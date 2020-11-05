<?php
/**
 * User: Cycle3
 * Date: 2020/9/23
 * Time: 16:40
 */

namespace app\admin\controller;

use app\admin\model\AdminUserModel;
use app\admin\model\RoleModel;
use app\admin\service\AdminUserService;
use app\common\controller\AdminController;
use think\facade\Request;

/**
 * 管理员
 *
 * @package app\admin\controller
 */
class Management extends AdminController
{
    protected $noNeedPermission = ['editMyBasicsInfo', 'changePassword'];

    /**
     * 用户基本信息
     */
    public function myBasicsInfo()
    {
        return view(
            'myBasicsInfo', ['user' => $this->user]
        );
    }

    /**
     * 编辑我的基本资料
     */
    public function editMyBasicsInfo()
    {
        $save = array(
            'nickname'    => Request::param('nickname'),
            'email'       => Request::param('email'),
            'remark'      => Request::param('remark'),
            'update_time' => time()
        );
        $AdminUserModel = new AdminUserModel();
        $AdminUserModel->where(['id' => $this->user->id])->save($save);
        return json(self::createReturn(true, [], '操作成功'));
    }

    /**
     * 修改密码
     */
    public function chanpass()
    {
        return view('chanpass', ['user' => $this->user]);
    }

    /**
     * 编辑用户密码
     */
    public function changePassword()
    {
        $oldPass = Request::param('password', '', 'trim');
        $newPass = Request::param('new_password', '', 'trim');
        $new_pwdconfirm = Request::param('new_pwdconfirm', '', 'trim');

        if (empty($oldPass)) {
            return json(self::createReturn(false, null, '请输入旧密码'));
        }

        if ($newPass != $new_pwdconfirm) {
            return json(self::createReturn(false, null, '两次密码不相同'));
        }

        $AdminUserModel = new AdminUserModel();
        if ($AdminUserModel->changePassword($this->user->id, $newPass, $oldPass)) {
            //退出登录
            (new AdminUserService)->logout();
            return json(self::createReturn(true, [
                'rediret_url' => api_url("/admin/Login/index") //跳转链接
            ], '密码已经更新，请重新登录'));
        } else {
            return json(self::createReturn(false, null, '密码更新失败'));
        }
    }

    /**
     * 管理员列表
     */
    public function index()
    {
        $role_id = Request::param('role_id');
        return view('index', [
            'role_id' => $role_id
        ]);
    }

    /**
     * 管理员管理
     */
    public function details()
    {
        $id = Request::param('id');
        return view('details', [
            'id' => $id
        ]);
    }

    /**
     * 获取管理员列表
     */
    public function getManagementList()
    {
        $AdminUserModel = new AdminUserModel();
        $RoleModel = new RoleModel();
        $where = [];
        $role_id = Request::param('role_id');
        if (!$this->is_administrator) {
            //如果非超级管理员，只能管理下级角色的成员
            $roleWhere['parentid'] = $this->user->role_id;
            if ($role_id) {
                $roleWhere['id'] = $role_id;
            }
            $role_ids = $RoleModel->where($roleWhere)->column('id');
            //如果没有找到下级role_ids 则默认为0
            $where['role_id'] = ['in', implode(',', $role_ids)];
        } else {
            if ($role_id) {
                $where['role_id'] = $role_id;
            }
        }

        $list = $AdminUserModel->where($where)->select();
        foreach ($list as $k => $User_value) {
            if ($User_value['last_login_time']) {
                $list[$k]['last_login_time'] = date("Y-m-d H:i:s", $User_value['last_login_time']);
            }
            $list[$k]['role_name'] = $RoleModel->where('id='.$User_value['role_id'])->value('name');
        }
        return json(self::createReturn(true, $list));
    }

    /**
     * 获取管理员详情
     */
    public function getDetails()
    {
        $AdminUserModel = new AdminUserModel();
        $id = Request::param('id');
        $where['id'] = $id;
        $res = $AdminUserModel->where($where)->withoutField('create_time,update_time')->find();
        $res['password'] = '';
        $res['status'] = (string) $res['status'];
        if ($res) {
            return json(self::createReturn(true, $res));
        } else {
            return json(self::createReturn(false, [], '该管理员不存在'));
        }
    }

    /**
     * 添加或者编辑管理员
     */
    public function addEditManagement()
    {
        $id = Request::param('id');

        $AdminUserModel = new AdminUserModel();
        // 编辑管理员
        if (!empty($id)) {
            //判断是否修改本人，在此方法，不能修改本人相关信息
            if ($this->user->id == $id) {
                return json(self::createReturn(false, null, '修改当前登录用户信息请进入[我的面板]中进行修改'));
            }
            if (1 == $id) {
                return json(self::createReturn(false, null, '该帐号不允许修改'));
            }
            if (false !== $AdminUserModel->amendManager($_POST)) {
                return json(self::createReturn(true, [], '更新成功'));
            } else {
                $error = $AdminUserModel->error;
                return json(self::createReturn(false, [], $error ? $error : '添加失败！'));
            }
        }

        // 创建管理员
        if ($AdminUserModel->createManager($_POST)) {
            return json(self::createReturn(true, [], '添加管理员成功'));
        } else {
            $error = $AdminUserModel->error;
            return json(self::createReturn(false, [], $error ? $error : '添加失败！'));
        }
    }

    //管理员删除
    public function delete()
    {
        $id = Request::param('id', '', 'trim');
        if ((int) $id == $this->user->id) {
            return json(self::createReturn(false, [], '你不能删除你自己！'));
        }

        $AdminUserModel = new AdminUserModel();
        //执行删除
        if ($AdminUserModel->deleteUser($id)) {
            return json(self::createReturn(true, [], '删除成功！'));
        } else {
            $error = $AdminUserModel->error;
            return json(self::createReturn(false, [], $error ?: '删除失败！'));
        }
    }

}