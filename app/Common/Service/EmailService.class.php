<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Common\Service;

use System\Service\BaseService;

class EmailService extends BaseService {

    /**
     * 发送邮件
     * @param $email
     * @param $subject
     * @param $message
     * @return array
     * @throws \phpmailerException
     */
    static function send($email, $subject, $message) {
        $config = cache('Config');

        $mail = new \PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP(); // Set mailer to use SMTP
        $mail->Host = $config['mail_server'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['mail_user']; // SMTP username
        $mail->Password = $config['mail_password'];
        $mail->Port = $config['mail_port'];
        $mail->setFrom($config['mail_from'], $config['mail_fname']);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $message;

        try {
            $result = $mail->send();
            if ($result) {
                return self::createReturn(true, null, '发送成功');
            } else {
                return self::createReturn(false, null, '发送失败: ' . $mail->ErrorInfo);
            }
        } catch (\phpmailerException $e) {
            return self::createReturn(false, null, '发送失败: ' . $e->getMessage());
        }
    }
}