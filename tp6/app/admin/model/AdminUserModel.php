<?php
/**
 * Author: jayinton
 */

namespace app\admin\model;

use app\admin\validate\User;
use think\exception\ValidateException;
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
     *
     * @param  string|int  $identifier  用户名或者用户ID
     *
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
     * 更新登录状态信息
     *
     * @param  string  $userId
     *
     * @return boolean|array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function loginStatus($userId)
    {
        $this->find((int) $userId);
        $res = Db::name('user')->where('id', $userId)->update([
            'last_login_time' => time(),
            'last_login_ip'   => request()->ip(),
        ]);

        return !!$res;
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

    /**
     * 编辑管理员
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function amendManager($data){
        if (empty($data) || !is_array($data) || !isset($data['id'])) {
            $this->error = '没有需要修改的数据！';
            return false;
        }

        $info = $this->where(array('id' => $data['id']))->find();
        if (empty($info)) {
            $this->error = '该管理员不存在！';
            return false;
        }

        //密码为空，表示不修改密码
        if (isset($data['password']) && empty($data['password'])) {
            unset($data['password']);
        } else {
            $verify =  $this->genRandomString(6);
            $data['verify'] = $verify;
            $data['password'] = $this->hashPassword($data['password'], $verify);
            unset($data['pwdconfirm']);
        }

        try {
            validate(User::class)
                ->scene('edit')
                ->check($data);

            $this->where(['id'=> $data['id'] ])->update($data);
            return true;
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $this->error = $e->getError();
            return false;
        }
    }

    /**
     * 创建管理员
     * @param  array  $data
     * @return boolean
     */
    public function createManager($data)
    {
        if (empty($data)) {
            $this->error = '没有数据！';
            return false;
        }

        $isCount = $this->where(['username' => $data['username']])->count();
        if ($isCount) {
            $this->error = '对不起，该账户已经存在！';
            return false;
        }

        if ($data['password'] != $data['pwdconfirm']) {
            $this->error = '对不起，您两次输入的密码不一致！';
            return false;
        }

        if ($data['password']) {
            $verify = $this->genRandomString(6);
            $data['verify'] = $verify;
            $data['password'] = $this->hashPassword($data['password'], $verify);
        }

        try {
            validate(User::class)->check($data);
            $data['info'] = '';
            $this->create($data);
            return true;
        } catch (ValidateException $e) {
            // 验证失败 输出错误信息
            $this->error = $e->getError();
            return false;
        }
    }

    /**
     * 删除管理员
     * @param $userId
     * @return bool
     */
    public function deleteUser($userId){
        $userId = (int) $userId;
        if (empty($userId)) {
            $this->error = '请指定需要删除的用户ID！';
            return false;
        }
        if ($userId == 1) {
            $this->error = '该管理员不能被删除！';
            return false;
        }
        if (false !== $this->where(array('id' => $userId))->delete()) {
            return true;
        } else {
            $this->error = '删除失败！';
            return false;
        }
    }
}