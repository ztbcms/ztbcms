<?php
/**
 * User: jayinton
 * Date: 2020/9/22
 */

namespace app\admin\model;

use think\facade\Db;
use think\Model;

class AdminUserModel extends Model
{
    protected $name = 'user';

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
     * @param  string $userId
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
            'last_login_ip' => request()->ip(),
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
     * 修改密码
     * TODO update
     *
     * @param  int  $uid  用户ID
     * @param  string  $newPass  新密码
     * @param  string  $password  旧密码
     *
     * @return boolean
     */
    function changePassword($uid, $newPass, $password = null)
    {
        //获取会员信息
        $userInfo = $this->getUserInfo((int) $uid, $password);
        if (empty($userInfo)) {
            $this->error = '旧密码不正确或者该用户不存在！';
            return false;
        }
        $verify = genRandomString(6);
        $status = $this->where(array('id' => $userInfo['id']))->save(array('password' => $this->hashPassword($newPass, $verify), 'verify' => $verify));
        return $status !== false ? true : false;
    }

    /**
     * 修改管理员信息
     * TODO update
     *
     * @param  array  $data
     *
     * @return boolean
     */
    function amendManager($data)
    {
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
        }
        if ($this->create($data)) {
            if ($this->data['password']) {
                $verify = genRandomString(6);
                $this->verify = $verify;
                $this->password = $this->hashPassword($this->password, $verify);
            }
            $status = $this->save();
            return $status !== false ? true : false;
        }
        return false;
    }

    /**
     * 创建管理员
     * TODO update
     *
     * @param  array  $data
     *
     * @return boolean
     */
    function createManager($data)
    {
        if (empty($data)) {
            $this->error = '没有数据！';
            return false;
        }
        if ($this->create($data)) {
            $id = $this->add();
            if ($id) {
                return $id;
            }
            $this->error = '入库失败！';
            return false;
        } else {
            return false;
        }
    }

    /**
     * 删除管理员
     * TODO update
     *
     * @param  string  $userId
     *
     * @return boolean
     */
    function deleteUser($userId)
    {
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

    /**
     * 插入成功后的回调方法
     * TODO update
     *
     * @param  array  $data  数据
     * @param  string  $options  表达式
     */
    protected function _after_insert($data, $options)
    {
        //添加信息后，更新密码字段
        $this->where(array('id' => $data['id']))->save(array(
            'password' => $this->hashPassword($data['password'], $data['verify']),
        ));
    }
}