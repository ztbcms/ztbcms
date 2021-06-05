<?php
/**
 * Author: jayinton
 */

namespace app\common\service\email;

use app\admin\service\AdminConfigService;
use app\common\model\email\EmailSendLogModel;
use app\common\service\BaseService;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 邮件服务
 *
 * @package app\common\service\email
 */
class EmailService extends BaseService
{
    /**
     * 发送邮件
     *
     * @param $email
     * @param $subject
     * @param $message
     *
     * @return array
     */
    static function send($email, $subject, $message)
    {
        $config = AdminConfigService::getInstance()->getConfig()['data'];
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = $config['mail_server'];
        $mail->SMTPAuth = $config['mail_auth'] == 1;
        $mail->Username = $config['mail_user']; // SMTP username
        $mail->Password = $config['mail_password'];
        $mail->Port = $config['mail_port'];
        $mail->setFrom($config['mail_from'], $config['mail_fname']);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        $logModel = new EmailSendLogModel();
        $log = [
            'to_email'   => $email,
            'from_email' => $config['mail_from'],
            'subject'    => $subject,
            'content'    => $message,
            'status'     => 1,
            'error_msg'  => '',
            'send_time'  => time()
        ];
        try {
            $result = $mail->send();
            if ($result) {
                $logModel->insert($log);
                return self::createReturn(true, null, '发送成功');
            } else {
                $log['status'] = 0;
                $log['error_msg'] = $mail->ErrorInfo;
                $logModel->insert($log);
                return self::createReturn(false, null, '发送失败: '.$mail->ErrorInfo);
            }
        } catch (\Exception $e) {
            $log['status'] = 0;
            $log['error_msg'] = $e->getMessage();
            $logModel->insert($log);
            return self::createReturn(false, null, '发送失败: '.$e->getMessage());
        }
    }
}