<?php
/**
 * Author: cycle_3
 */

namespace app\common\controller\downloader;

use app\common\controller\AdminController;
use app\common\model\downloader\DownloaderModel;
use app\common\service\downloader\DownloaderService;
use think\Request;

class Panel extends AdminController
{

    /**
     * 下载中心页
     * @param Request $request
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DbException
     */
    public function index(Request $request)
    {
        if ($request->isGet() && $request->get('_action') == 'list') {
            $DownloaderModel = new DownloaderModel();
            $where = [];

            $downloader_ids = input('downloader_ids');
            if ($downloader_ids) {
                $where[] = ['downloader_id', 'in', $downloader_ids];
            } else {
                $where[] = ['downloader_id', '=', -1];
            }

            $list = $DownloaderModel
                ->where($where)
                ->append(['downloader_state_name'])
                ->order('create_time desc')
                ->paginate(input('limit'));
            return json(self::createReturn(true, $list));
        }
        if ($request->isPost() && $request->post('_action') == 'submit') {
            //创建下载任务
            $url = input('url', '', 'trim');
            $res = DownloaderService::createDownloaderTask($url);
            return json($res);
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