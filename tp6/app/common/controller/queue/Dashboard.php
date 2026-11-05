<?php
/**
 * Author: jayinton
 */

namespace app\common\controller\queue;

use app\common\controller\AdminController;
use app\common\libs\helper\PaginationHelper;
use app\common\service\queue\QueueFailedJobService;
use app\common\service\queue\QueueMonitorStatService;
use think\facade\View;
use think\Request;

/**
 * 队列监控（仅支持 database 驱动）
 */
class Dashboard extends AdminController
{
    /**
     * 概况：实时状态、统计、活跃 worker
     *
     * @param Request $request 请求
     * @return \think\response\Json|string
     */
    public function index(Request $request)
    {
        $action = $request->get('_action', '');
        $service = new QueueMonitorStatService();
        if ($request->isGet() && $action === 'getRealtime') {
            return json($service->getRealtime());
        }
        if ($request->isGet() && $action === 'getStats') {
            return json($service->getStats((string)$request->get('range', '1h')));
        }
        if ($request->isGet() && $action === 'getWorkers') {
            return json($service->getWorkers());
        }
        $ranges = [];
        foreach (QueueMonitorStatService::RANGES as $value => $item) {
            $ranges[] = ['value' => $value, 'label' => $item['label']];
        }

        return View::fetch('index', ['ranges' => $ranges]);
    }

    /**
     * 执行记录列表
     *
     * @param Request $request 请求
     * @return \think\response\Json|string
     */
    public function monitorLog(Request $request)
    {
        $service = new QueueMonitorStatService();
        if ($request->isGet() && $request->get('_action') === 'getList') {
            $page = PaginationHelper::normalizePage($request->get('page', 1));
            $limit = PaginationHelper::normalizeLimit($request->get('limit', 20), 20);
            $filters = [
                'queue'        => (string)$request->get('queue', ''),
                'job_name'     => (string)$request->get('job_name', ''),
                'status'       => (string)$request->get('status', ''),
                'start_date'   => (string)$request->get('start_date', ''),
                'end_date'     => (string)$request->get('end_date', ''),
                'min_duration' => (string)$request->get('min_duration', ''),
                'hostname'     => (string)$request->get('hostname', ''),
                'pid'          => (string)$request->get('pid', ''),
                'keyword'      => (string)$request->get('keyword', ''),
            ];

            return json($service->getMonitorList($filters, $page, $limit));
        }
        if ($request->isGet() && $request->get('_action') === 'getOptions') {
            return json(self::createReturn(true, $service->getFilterOptions()));
        }

        return View::fetch('monitorLog');
    }

    /**
     * 执行记录详情（iframe 子页面，?id=）
     *
     * @param Request $request 请求
     * @return \think\response\Json|string
     */
    public function monitorDetail(Request $request)
    {
        if ($request->isGet() && $request->get('_action') === 'getDetail') {
            return json((new QueueMonitorStatService())->getMonitorDetail(intval($request->get('id', 0))));
        }

        return View::fetch('monitorDetail', ['id' => intval($request->get('id', 0))]);
    }

    /**
     * 失败任务列表
     *
     * @param Request $request 请求
     * @return \think\response\Json|string
     */
    public function failedJobs(Request $request)
    {
        if ($request->isGet() && $request->get('_action') === 'getList') {
            $page = PaginationHelper::normalizePage($request->get('page', 1));
            $limit = PaginationHelper::normalizeLimit($request->get('limit', 20), 20);
            $filters = [
                'queue'      => (string)$request->get('queue', ''),
                'keyword'    => (string)$request->get('keyword', ''),
                'start_date' => (string)$request->get('start_date', ''),
                'end_date'   => (string)$request->get('end_date', ''),
            ];

            return json((new QueueFailedJobService())->getList($filters, $page, $limit));
        }

        return View::fetch('failedJobs');
    }

    /**
     * 失败任务详情（iframe 子页面，?id=）
     *
     * @param Request $request 请求
     * @return \think\response\Json|string
     */
    public function failedJobDetail(Request $request)
    {
        if ($request->isGet() && $request->get('_action') === 'getDetail') {
            return json((new QueueFailedJobService())->getDetail(intval($request->get('id', 0))));
        }

        return View::fetch('failedJobDetail', ['id' => intval($request->get('id', 0))]);
    }

    /**
     * 重试失败任务
     *
     * @param Request $request 请求
     * @return \think\response\Json
     */
    public function retryFailedJob(Request $request)
    {
        if (!$request->isPost()) {
            return json(self::createReturn(false, null, '请求方式错误'));
        }

        return json((new QueueFailedJobService())->retry((array)$request->post('ids/a', [])));
    }

    /**
     * 删除失败任务
     *
     * @param Request $request 请求
     * @return \think\response\Json
     */
    public function deleteFailedJob(Request $request)
    {
        if (!$request->isPost()) {
            return json(self::createReturn(false, null, '请求方式错误'));
        }

        return json((new QueueFailedJobService())->delete((array)$request->post('ids/a', [])));
    }
}
