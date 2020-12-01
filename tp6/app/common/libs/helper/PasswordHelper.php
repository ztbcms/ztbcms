<?php
/**
 * Author: jayinton
 */

namespace app\common\libs\helper;


/**
 * 密码服务
 *
 * @package app\common\libs\helper
 */
class PasswordHelper
{
    /**
     * 对明文密码，进行加密，返回加密后的密文密码
     *
     * @param  string  $password  明文密码
     * @param  string  $verify  认证码
     *
     * @return string 密文密码
     */
    static function hashPassword(string $password, $verify = "")
    {
        return md5($password.md5($verify));
    }

}