<?php
/**
 * Author: jayinton
 */

namespace app\common\controller\email;


use app\admin\service\AdminConfigService;
use app\common\controller\AdminController;
use app\common\model\email\EmailSendLogModel;
use app\common\service\email\EmailService;
use think\Request;

class Email extends AdminController
{
    /**
     * 邮件配置
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function config(Request $request)
    {
        $adminConfigService = new AdminConfigService();
        if ($request->isPost()) {
            $data = [
                'mail_server'   => $request->post("mail_server"),
                'mail_port'     => $request->post("mail_port"),
                'mail_from'     => $request->post("mail_from"),
                'mail_fname'    => $request->post("mail_fname"),
                'mail_auth'     => $request->post("mail_auth"),
                'mail_user'     => $request->post("mail_user"),
                'mail_password' => $request->post("mail_password"),
            ];
            $res = $adminConfigService->updateConfig($data);
            return json($res);
        }

        if ($request->get('_action') === 'getDetail') {
            // 获取详情
            $_config = $adminConfigService->getConfig(null, false)['data'];
            $fields = [
                'mail_server', 'mail_port', 'mail_from', 'mail_fname', 'mail_auth', 'mail_user', 'mail_password'
            ];
            $config = [];
            foreach ($fields as $i => $key) {
                $config[$key] = $_config[$key];
            }
            return self::makeJsonReturn(true, $config);
        }

        return view('config');
    }

    /**
     * 发送日志
     *
     * @return \think\response\Json|\think\response\View
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function sendLog()
    {
        $action = input('_action');
        if (\think\facade\Request::isGet() && $action == 'getList') {
            $where = [];
            $to_email = input('to_email', '', 'trim');
            if (!empty($to_email)) {
                $where[] = ['to_email', 'like', '%'.$to_email.'%'];
            }
            $from_email = input('from_email', '', 'trim');
            if (!empty($from_email)) {
                $where[] = ['from_email', 'like', '%'.$from_email.'%'];
            }
            $start_time = input('start_time', '', 'trim');
            $end_time = input('end_time', '', 'trim');
            if (!empty($start_time) && !empty($end_time)) {
                $send_time = [$start_time, $end_time];
                $where [] = ['send_time', 'between', $send_time];
            }

            $status = input('status', '', 'trim');
            if ($status != '') {
                $where[] = ['status', '=', $status];
            }

            $page = input('page', 1, 'trim');
            $limit = input('limit', 10, 'trim');

            $logModel = new EmailSendLogModel();
            $db = $logModel->where($where)->order(['id' => 'desc'])->page($page)->limit($limit);
            if (!empty($logintime)) {
                $db->whereTime('logintime', 'between', $logintime);
            }

            $items = $db->select();

            $total_items = $logModel->where($where)->count();
            $total_pages = ceil($total_items / $limit);

            $data = [
                'items'       => $items,
                'page'        => intval($page),
                'limit'       => intval($limit),
                'total_items' => intval($total_items),
                'total_pages' => intval($total_pages),
            ];
            return self::makeJsonReturn(true, $data);
        }
        return view('sendLog');
    }

    /**
     * 发送邮件
     *
     * @param  Request  $request
     *
     * @return \think\response\Json|\think\response\View
     */
    function sendEmail(Request $request)
    {
        if ($request->isPost() && $request->post('_action') === 'doSendEmail') {
            $to_email = input('to_email', '', 'trim');
            $subject = input('subject', '', 'trim');
            $content = input('content', '', 'trim');
            return json(EmailService::send($to_email, $subject, $content));
        }
        return view('sendEmail');
    }

}