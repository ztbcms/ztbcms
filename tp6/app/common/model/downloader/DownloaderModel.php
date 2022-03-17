<?php
/**
 * Author: cycle_3
 */

namespace app\common\model\downloader;


use think\Model;
use think\model\concern\SoftDelete;

/**
 * 下载中心
 * Class DownloaderModel
 *
 * @package app\common\model\downloader
 */
class DownloaderModel extends Model
{
    use SoftDelete;

    protected $name = 'downloader';
    protected $pk = 'downloader_id';
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;


    //等待下载
    const STATE_WAIT = 10;
    //启动下载-下载中
    const STATE_PROCESSING = 20;
    //下载成功
    const STATE_SUCCESS = 30;
    //下载失败
    const STATE_FAIL = 40;


    /**
     * 下载状态
     * @param $v
     * @param $data
     * @return string
     */
    public function getDownloaderStateNameAttr($v, $data): string
    {
        $downloader_state = $data['downloader_state'] ?? '';
        if ($downloader_state == self::STATE_WAIT) {
            return '等待下载';
        }
        if ($downloader_state == self::STATE_PROCESSING) {
            return '下载中';
        }
        if ($downloader_state == self::STATE_SUCCESS) {
            return '下载成功';
        }
        if ($downloader_state == self::STATE_FAIL) {
            return '下载失败';
        }
        return '';
    }

    /**
     * 下载开始时间格式化
     * @param $v
     * @param $data
     * @return string
     */
    public function getProcessStartDateAttr($v, $data): string
    {
        if (empty($data['process_start_time'])) {
            return '';
        }
        return date('Y-m-d H:i:s', $data['process_start_time']);
    }

    /**
     * 下载结束时间格式化
     * @param $v
     * @param $data
     * @return string
     */
    public function getProcessEndDateAttr($v, $data): string
    {
        if (empty($data['process_end_time'])) {
            return '';
        }
        return date('Y-m-d H:i:s', $data['process_end_time']);
    }

}