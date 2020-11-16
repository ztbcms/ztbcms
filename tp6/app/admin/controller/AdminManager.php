<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace app\admin\controller;

use app\admin\model\AdminUserModel;
use app\admin\model\RoleModel;
use app\admin\service\AdminManagerService;
use app\admin\service\AdminUserService;
use app\admin\service\RoleService;
use app\common\controller\AdminController;
use app\common\libs\helper\TreeHelper;
use think\facade\Request;

/**
 * 管理员
 */
class AdminManager extends AdminController
{
    protected $noNeedPermission = ['editMyBasicsInfo', 'changePassword', 'getManagerList'];

    /**
     * 用户基本信息
     */
    function myBasicsInfo()
    {
        return view(
            'myBasicsInfo', ['user' => $this->user]
        );
    }

    /**
     * 编辑我的基本资料
     */
    function editMyBasicsInfo()
    {
        $save = array(
            'nickname' => Request::param('nickname'),
            'email' => Request::param('email'),
            'remark' => Request::param('remark'),
            'update_time' => time()
        );
        $AdminUserModel = new AdminUserModel();
        $AdminUserModel->where('id', $this->user->id)->save($save);
        return json(self::createReturn(true, [], '操作成功'));
    }

    /**
     * 修改密码
     */
    function chanpass()
    {
        return view('chanpass', ['user' => $this->user]);
    }

    /**
     * 编辑用户密码
     */
    function changePassword()
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
            (new AdminUserService())->logout();
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
    function index()
    {
        $role_id = Request::param('role_id');
        return view('index', [
            'role_id' => $role_id
        ]);
    }

    /**
     * 新增
     * @return \think\response\Json|\think\response\View
     */
    function managerAdd()
    {
        if (Request::isPost()) {
            $data = Request::post();
            if (empty($data['password']) || $data['password'] !== $data['pwdconfirm']) {
                return json(self::createReturn(false, null, '密码不一致'));
            }
            $adminManagerService = new AdminManagerService();
            $res = $adminManagerService->addOrEditAdminManager($data);
            return json($res);
        }
        return view('managerAddOrEdit');
    }

    /**
     * 编辑
     * @return \think\response\Json|\think\response\View
     */
    function managerEdit()
    {
        if (Request::isPost()) {
            $data = Request::post();
            if (!empty($data['password'])) {
                if ($data['password'] !== $data['pwdconfirm']) {
                    return json(self::createReturn(false, null, '密码不一致'));
                }
            }
            $adminManagerService = new AdminManagerService();
            $res = $adminManagerService->addOrEditAdminManager($data);
            return json($res);
        }
        $id = Request::param('id');
        return view('managerAddOrEdit', [
            'id' => $id
        ]);
    }

    /**
     * 获取管理员列表(只能登录用户管理下级角色的成员)
     * 当前登录角色查看指定角色时，若当前登录角色等于或高于指定角色，可以查看指定角色的管理员列表；否则没有权限展示
     */
    function getManagerList()
    {
        $AdminUserModel = new AdminUserModel();
        $RoleModel = new RoleModel();
        $roleService = new RoleService();
        $where = [];
        $role_id = Request::param('role_id', 0);
        // 指定角色时，需要判断权限
        if (!empty($role_id)) {
            // 角色层级权限判断
            $result = $roleService->compareRoleLevel($this->user['role_id'], $role_id)['data'];
            if ($result <= 0) {
                return json(self::createReturn(true, null, '当前登录用户无权限操作'));
            }
        }
        if (empty($role_id)) {
            // 没有指定角色，则获取当前登录用户角色的旗下角色
            $role_list = $roleService->getRoleList()['data'];
            $son_role_list = TreeHelper::getSonNodeFromArray($role_list, $this->user['role_id'], ['parentKey' => 'parentid']);
            $son_role_id_list = array_map(function ($item)
            {
                return $item['id'];
            }, $son_role_list);
            // 超管可以管理全部管理员，包括其他管理角色
            if ($this->user['role_id'] == RoleModel::SUPER_ADMIN_ROLE_ID) {
                $son_role_id_list [] = $this->user['role_id'];
            }
            //如果没有找到下级role_ids 则默认为0
            $where[] = ['role_id', 'in', $son_role_id_list];
        } else {
            // 指定
            $where[] = ['role_id', '=', $role_id];
        }

        $list = $AdminUserModel->where($where)->order('id desc')->select();
        foreach ($list as $k => $User_value) {
            if ($User_value['last_login_time']) {
                $list[$k]['last_login_time'] = date("Y-m-d H:i:s", $User_value['last_login_time']);
            }
            $list[$k]['role_name'] = $RoleModel->where('id=' . $User_value['role_id'])->value('name');
        }
        return json(self::createReturn(true, $list));
    }

    /**
     * 获取管理员详情
     */
    function getDetail()
    {
        $AdminUserModel = new AdminUserModel();
        $id = Request::param('id');
        $where['id'] = $id;
        $res = $AdminUserModel->where($where)->withoutField('create_time,update_time')->find();
        $res['password'] = '';
        $res['status'] = (string)$res['status'];
        if ($res) {
            return json(self::createReturn(true, $res));
        } else {
            return json(self::createReturn(false, [], '该管理员不存在'));
        }
    }

    //管理员删除
    function managerDelete()
    {
        $id = Request::param('id', '', 'trim');
        if ($id == $this->user->id) {
            return json(self::createReturn(false, [], '不能删除自己'));
        }

        $adminManagerService = new AdminManagerService();
        $res = $adminManagerService->deleteAdminManager($id);
        return json($res);
    }
}