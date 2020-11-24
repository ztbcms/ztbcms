<?php
/**
 * Author: jayinton
 */

namespace app\admin\model;

use think\facade\Db;
use think\Model;

/**
 *  管理后台用户
 *
 * @package app\admin\model
 */
class AdminUserModel extends Model
{
    protected $name = 'user';

    // 账号状态
    /**
     * 账号状态: 禁用
     */
    const STATUS_DISABLE = 0;
    /**
     * 账号状态: 正常
     */
    const STATUS_ENABLE = 1;

    /**
     * 获取用户信息
     * @deprecated 请使用AdminUserService
     * @param  string|int  $identifier  用户名或者用户ID
     * @param  null  $password
     *
     * @return boolean|array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getUserInfo($identifier, $password = null)
    {
        if (empty($identifier)) {
            return false;
        }
        $map = [];
        //判断是uid还是用户名
        if (is_int($identifier)) {
            $map []= ['id', '=', $identifier];
        } else {
            $map []= ['username', '=', $identifier];
        }
        $userInfo = $this->where($map)->findOrEmpty();
        if (empty($userInfo)) {
            return false;
        }
        //密码验证
        if (!empty($password) && $this->hashPassword($password, $userInfo['verify']) != $userInfo['password']) {
            return false;
        }
        return $userInfo->toArray();
    }

    /**
     * 对明文密码，进行加密，返回加密后的密文密码
     *
     * @param  string  $password  明文密码
     * @param  string  $verify  认证码
     *
     * @return string 密文密码
     */
    function hashPassword($password, $verify = "")
    {
        return md5($password.md5($verify));
    }

    /**
     * 产生一个指定长度的随机字符串,并返回给用户
     * @param  int  $len  产生字符串的长度
     * @return string 随机字符串
     */
    function genRandomString($len = 6)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9",
        );
        $charsLen = count($chars) - 1;
        // 将数组打乱
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    /**
     * 修改密码
     * @param $uid
     * @param $newPass
     * @param  null  $password
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function changePassword($uid, $newPass, $password = null)
    {
        //获取会员信息
        $userInfo = $this->getUserInfo((int) $uid, $password);
        if (empty($userInfo)) {
            $this->error = '旧密码不正确或者该用户不存在！';
            return false;
        }
        $verify = $this->genRandomString(6);
        $status = $this->where(array('id' => $userInfo['id']))->save(array(
            'password' => $this->hashPassword($newPass, $verify), 'verify' => $verify
        ));
        return $status !== false ? true : false;
    }
}