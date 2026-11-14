<?php
/**
 * 计划任务公共调度服务
 */

namespace app\common\service\cron;

use think\facade\Log;
use app\common\model\cron\CronConfigModel;
use app\common\model\cron\CronModel;
use app\common\model\cron\CronSchedulingLogModel;
use app\common\service\BaseService;

/**
 * 计划任务调度服务
 */
class CronService extends BaseService
{
    const STATUS_DISABLED = 'disabled';
    const STATUS_INVALID_SECRET = 'invalid_secret';
    const STATUS_LOCKED = 'locked';
    const STATUS_FINISHED = 'finished';

    /**
     * 执行一轮计划任务调度
     *
     * @param array $options 调度参数：
     *                       - check_secret (bool): 是否校验调度私钥，默认 false
     *                       - secret_key (string): 请求传入的调度私钥，默认 ''
     *                       - unlimited_time (bool): 是否解除脚本超时并忽略客户端断开，默认 false
     *                       - on_progress (callable|null): 调度进度回调 function(string $message): void
     * @return array 统一返回结构，data 中包含 reason、used_time、elapsed_time、total_amount、error_amount
     */
    public static function runScheduler(array $options = []): array
    {
        $cronConfig = CronConfigModel::column('value', 'key');
        // 判断计划任务是否关闭
        if (($cronConfig[CronConfigModel::KEY_ENABLE_CRON] ?? '') != CronConfigModel::ENABLE_YES) {
            return self::createReturn(false, [
                'reason' => self::STATUS_DISABLED,
                'used_time' => 0,
                'elapsed_time' => 0,
                'total_amount' => 0,
                'error_amount' => 0,
            ], '计划任务功能已关闭');
        }

        // 可选密钥检测（Web 入口启用）
        if (!empty($options['check_secret'])) {
            $expectedSecret = (string) ($cronConfig[CronConfigModel::KEY_ENABLE_SECRET_KEY] ?? '');
            $providedSecret = (string) ($options['secret_key'] ?? '');
            if (!hash_equals($expectedSecret, $providedSecret)) {
                return self::createReturn(false, [
                    'reason' => self::STATUS_INVALID_SECRET,
                    'used_time' => 0,
                    'elapsed_time' => 0,
                    'total_amount' => 0,
                    'error_amount' => 0,
                ], 'Secret key invalidated');
            }
        }

        // 使用 flock 非阻塞排他锁，避免进程异常退出后残留锁文件导致永久停摆
        $lockfile = runtime_path() . 'cron.lock';
        $lockDir = dirname($lockfile);
        if (!is_dir($lockDir)) {
            mkdir($lockDir, 0777, true);
        }
        $lockHandle = fopen($lockfile, 'c+');
        if ($lockHandle === false || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if (is_resource($lockHandle)) {
                fclose($lockHandle);
            }
            return self::createReturn(false, [
                'reason' => self::STATUS_LOCKED,
                'used_time' => 0,
                'elapsed_time' => 0,
                'total_amount' => 0,
                'error_amount' => 0,
            ], 'Cron is Locked');
        }

        $progressCallback = isset($options['on_progress']) && is_callable($options['on_progress'])
            ? $options['on_progress']
            : null;

        try {
            if (!empty($options['unlimited_time'])) {
                set_time_limit(0);
                ignore_user_abort(true);
            }

            $startTime = time();
            $cronCount = 0;
            $errorCount = 0;
            $processedCronIds = [];

            while ($cron = self::getNextDueCron($processedCronIds)) {
                $processedCronIds[] = $cron->cron_id;
                $cronCount++;

                list($day, $hour, $minute) = array_pad(explode('-', (string) $cron->loop_daytime), 3, 0);
                $nexttime = CronModel::getNextTime($cron->loop_type, $day, $hour, $minute);

                try {
                    // 先推进下一次执行时间，防止单个任务执行耗时较长或失败时被重复调度
                    CronModel::where('cron_id', $cron->cron_id)->update([
                        'modified_time' => time(),
                        'next_time' => $nexttime,
                    ]);
                } catch (\Throwable $e) {
                    $errorCount++;
                    $message = 'Update failed for cron_id=' . $cron->cron_id . ': ' . $e->getMessage();
                    Log::error($message);
                    if ($progressCallback !== null) {
                        $progressCallback($message);
                    }
                    continue;
                }

                if ($progressCallback !== null) {
                    $progressCallback('Processing:' . $cron->cron_file);
                }
                $res = $cron->runAction();
                if ($progressCallback !== null) {
                    $progressCallback('Processed :' . $cron->cron_file);
                }
                if (empty($res['status'])) {
                    $errorCount++;
                }
            }

            $endTime = time();
            $elapsedTime = max(0, $endTime - $startTime);
            $logUseTime = $elapsedTime <= 0 ? 1 : $elapsedTime;

            $schedulingLogModel = new CronSchedulingLogModel();
            $schedulingLogModel->start_time = $startTime;
            $schedulingLogModel->end_time = $endTime;
            $schedulingLogModel->use_time = $logUseTime;
            $schedulingLogModel->error_count = $errorCount;
            $schedulingLogModel->cron_count = $cronCount;
            $schedulingLogModel->save();

            return self::createReturn(true, [
                'reason' => self::STATUS_FINISHED,
                'used_time' => $logUseTime,
                'elapsed_time' => $elapsedTime,
                'total_amount' => $cronCount,
                'error_amount' => $errorCount,
            ], 'Cron status: finish');
        } finally {
            // 释放文件锁并关闭句柄（不删除锁文件以免造成 inode 竞态）
            if (is_resource($lockHandle)) {
                flock($lockHandle, LOCK_UN);
                fclose($lockHandle);
            }
        }
    }

    /**
     * 获取下一条待执行的已开启计划任务
     *
     * @param array $excludeCronIds 本轮已处理过的任务 ID 列表，防止遗漏小 ID 任务或更新失败时死循环
     * @return CronModel|null
     */
    private static function getNextDueCron(array $excludeCronIds = []): ?CronModel
    {
        $query = CronModel::where([
            ['isopen', '=', CronModel::OPEN_STATUS_ON],
            ['next_time', '<', time()],
        ]);
        if (!empty($excludeCronIds)) {
            $query->where('cron_id', 'not in', $excludeCronIds);
        }
        $cron = $query->order(['next_time' => 'ASC', 'cron_id' => 'ASC'])->find();
        return $cron instanceof CronModel ? $cron : null;
    }
}
