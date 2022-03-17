<?php
/**
 * Author: cycle_3
 */

namespace app\common\controller\downloader;

use app\common\controller\AdminController;
use app\common\model\downloader\DownloaderModel;
use app\common\service\downloader\DownloaderService;
use think\Request;


class Log extends AdminController
{

    public function index(Request $request)
    {
        if ($request->isGet() && $request->get('_action') == 'list') {
            $DownloaderModel = new DownloaderModel();
            $where = [];
            $keywords = input('keywords', '', 'trim');
            if ($keywords) {
                $where[] = ['downloader_url|downloader_result|file_name', 'like', '%' . $keywords . '%'];
            }
            $downloader_state = intval(input('downloader_state', 0));
            if ($downloader_state !== 0) {
                $where[] = ['downloader_state', '=', $downloader_state];
            }
            $list = $DownloaderModel
                ->where($where)
                ->append(['downloader_state_name', 'process_start_date', 'process_end_date'])
                ->order('create_time desc')
                ->paginate(input('limit'));
            return json(self::createReturn(true, $list));
        }
        if ($request->isPost() && $request->post('_action') == 'implement') {
            //执行下载任务
            $downloader_id = input('downloader_id', '', 'trim');
            $res = DownloaderService::implementDownloaderTask($downloader_id);
            return json($res);
        }
        if ($request->isPost() && $request->post('_action') == 'retry') {
            //重试下载任务
            $downloader_id = input('downloader_id', '', 'trim');
            $res = DownloaderService::retryDownloaderTask($downloader_id);
            return json($res);
        }
        if ($request->isPost() && $request->post('_action') == 'delete') {
            //删除下载任务
            $downloader_id = input('downloader_id', '', 'trim');
            $DownloaderModel = new DownloaderModel();
            $DownloaderModel->where('downloader_id', '=', $downloader_id)
                ->findOrEmpty()->delete();
            return json(self::createReturn(true, '', '操作成功'));
        }
        return view();
    }

}