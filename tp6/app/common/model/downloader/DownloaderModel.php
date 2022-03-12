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
    const WAIT_DOWNLOADER = 10;
    //启动下载
    const START_DOWNLOADER = 20;
    //下载成功
    const SUCCESS_DOWNLOADER = 30;
    //下载失败
    const FAIL_DOWNLOADER = 40;


    /**
     * 下载状态
     * @param $v
     * @param $data
     * @return string
     */
    public function getDownloaderStateNameAttr($v,$data): string
    {
        $downloader_state = $data['downloader_state'] ?? '';
        if($downloader_state == self::WAIT_DOWNLOADER) {
            return '等待下载';
        }
        if($downloader_state == self::START_DOWNLOADER) {
            return '下载中';
        }
        if($downloader_state == self::SUCCESS_DOWNLOADER) {
            return '下载成功';
        }
        if($downloader_state == self::FAIL_DOWNLOADER) {
            return '下载失败';
        }
        return '';
    }

}