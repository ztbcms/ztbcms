<?php

namespace app\common\controller\cron;

use app\BaseController;
use app\common\service\cron\CronService;

/**
 * 计划任务调度控制器
 */
class Index extends BaseController
{
    /**
     * 计划任务调度入口
     *
     * @param string $cron_secret_key
     *
     * @return \think\response\Json
     */
    public function index($cron_secret_key = '')
    {
        $res = CronService::runScheduler([
            'check_secret' => true,
            'secret_key' => (string) $cron_secret_key,
            'unlimited_time' => true,
        ]);

        $data = $res['data'] ?? [];
        $reason = $data['reason'] ?? '';
        if ($reason === CronService::STATUS_DISABLED) {
            return json(['used_time' => 0, 'msg' => 'Cron status: stop']);
        }
        if ($reason === CronService::STATUS_INVALID_SECRET) {
            return json(['used_time' => 0, 'msg' => 'Secret key invalidated']);
        }
        if ($reason === CronService::STATUS_LOCKED) {
            return json(['used_time' => 0, 'msg' => 'Cron is Locked']);
        }

        return json([
            'used_time' => $data['elapsed_time'] ?? 0,
            'msg' => 'Cron status: finish',
        ]);
    }
}
