<?php

// +----------------------------------------------------------------------
// |  后台用户模型
// +----------------------------------------------------------------------

namespace Admin\Model;

use Common\Model\Model;

class UserModel extends Model {

    //array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
    protected $_validate = array(
        array('username', 'require', '用户名不能为空！'),
        array('nickname', 'require', '真实姓名不能为空！'),
        array('role_id', 'require', '帐号所属角色不能为空！', 0, 'regex', 1),
        array('password', 'require', '密码不能为空！', 0, 'regex', 1),
        array('pwdconfirm', 'password', '两次输入的密码不一样！', 0, 'confirm'),
        array('email', 'email', '邮箱地址有误！'),
        array('username', '', '帐号名称已经存在！', 0, 'unique', 1),
        array('status', array(0, 1), '状态错误，状态只能是1或者0！', 2, 'in'),
    );
    //array(填充字段,填充内容,[填充条件,附加规则])
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 3, 'function'),
        array('verify', 'genRandomString', 1, 'function', 6), //新增时自动生成验证码
    );

    /**
     * 获取用户信息
     * @param type $identifier 用户名或者用户ID
     * @return boolean|array
     */
    public function getUserInfo($identifier, $password = NULL) {
        if (empty($identifier)) {
            return false;
        }
        $map = array();
        //判断是uid还是用户名
        if (is_int($identifier)) {
            $map['id'] = $identifier;
        } else {
            $map['username'] = $identifier;
        }
        $userInfo = $this->where($map)->find();
        if (empty($userInfo)) {
            return false;
        }
        //密码验证
        if (!empty($password) && $this->hashPassword($password, $userInfo['verify']) != $userInfo['password']) {
            return false;
        }
        return $userInfo;
    }

    /**
     * 更新登录状态信息
     * @param type $userId
     * @return type
     */
    public function loginStatus($userId) {
        $this->find((int) $userId);
        $this->last_login_time = time();
        $this->last_login_ip = get_client_ip();
        return $this->save();
    }

    /**
     * 对明文密码，进行加密，返回加密后的密文密码
     * @param string $password 明文密码
     * @param string $verify 认证码
     * @return string 密文密码
     */
    public function hashPassword($password, $verify = "") {
        return md5($password . md5($verify));
    }

    /**
     * 修改密码
     * @param int $uid 用户ID
     * @param string $newPass 新密码
     * @param string $password 旧密码
     * @return boolean
     */
    public function changePassword($uid, $newPass, $password = NULL) {
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
     * @param type $data
     */
    public function amendManager($data) {
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
     * @param type $data
     * @return boolean
     */
    public function createManager($data) {
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
     * @param type $userId
     * @return boolean
     */
    public function deleteUser($userId) {
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
     * @param type $data 数据
     * @param type $options 表达式
     */
    protected function _after_insert($data, $options) {
        //添加信息后，更新密码字段
        $this->where(array('id' => $data['id']))->save(array(
            'password' => $this->hashPassword($data['password'], $data['verify']),
        ));
    }

}
