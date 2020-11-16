<?php
/**
 * User: Cycle3
 * Date: 2020/9/23
 */

namespace app\admin\controller;


use app\admin\model\RoleModel;
use app\admin\service\AdminUserService;
use app\admin\service\MenuService;
use app\admin\service\RbacService;
use app\admin\service\RoleService;
use app\common\controller\AdminController;
use app\common\libs\helper\TreeHelper;
use think\facade\Request;

/**
 * 角色权限
 *
 * @package app\admin\controller
 */
class Role extends AdminController
{
    protected $noNeedPermission = ['getRoleList', 'getAuthorizeList'];

    /**
     * 角色列表
     */
    function index()
    {
        $action = input('_action', '', 'trim');
        if (Request::isGet() && $action == 'getList') {

            return $this->getRoleList();
        }
        return view('index');
    }

    /**
     * 新增角色
     *
     */
    function roleAdd()
    {
        if (Request::isPost()) {
            return $this->roleAddEdit();
        }
        return view('roleAddOrEdit');
    }

    /**
     * 编辑角色
     *
     */
    function roleEdit()
    {
        if (Request::isPost()) {
            return $this->roleAddEdit();
        }

        $id = Request::param('id', '', 'trim');
        $action = input('_action', '', 'trim');
        if (Request::isGet() && $action == 'getDetail') {

            $RoleModel = new RoleModel();
            $info = $RoleModel->where(['id' => $id])->find();
            return self::makeJsonReturn(true, $info);
        }
        return view('roleAddOrEdit', [
            'id' => $id
        ]);
    }

    /**
     * 获取角色列表
     *
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getRoleList()
    {
        $RoleModel = new RoleModel();
        $pid = 0;
        $list = $RoleModel->select()->toArray();
        //如果是超级管理员，显示所有角色。如果非超级管理员，只显示下级角色
        if ($this->user['role_id'] !== RoleModel::SUPER_ADMIN_ROLE_ID) {
            $list = TreeHelper::getSonNodeFromArray($list, $this->user['role_id'], [
                'parentKey' => 'parentid'
            ]);
            $pid = $this->user['role_id'];
        }
        $list = $RoleModel->getTree($list, $pid);
        foreach ($list as $k => $v) {
            $list[$k]['name'] = str_repeat('—', $v['level']).' '.$v['name'];
        }
        return json(self::createReturn(true, $list));
    }

    /**
     * 添加或者编辑角色
     */
    function roleAddEdit()
    {
        $id = Request::param('id', '', 'trim');
        $name = Request::param('name', '', 'trim');
        $remark = Request::param('remark', '', 'trim');
        $status = Request::param('status', '1', 'trim');
        $parentid = Request::param('parentid', '0', 'trim');

        if (AdminUserService::administratorRoleId == $id) {
            return json(self::createReturn(false, [], '超级管理员角色不能被修改'));
        }
        if (empty($name)) {
            return json(self::createReturn(false, null, '请填写角色名称'));
        }

        $data['id'] = $id;
        $data['name'] = $name;
        $data['remark'] = $remark;
        $data['status'] = $status;
        $data['parentid'] = $parentid;

        $roleService = new RoleService();
        $res = $roleService->addOrEditRole($data);

        return json($res);
    }

    /**
     * 删除角色
     */
    function roleDelete()
    {
        $id = Request::param('id', '', 'trim');
        if ($id == $this->user->role_id) {
            return json(self::createReturn(false, null, '您不能删除自己使用的角色'));
        }
        $roleService = new RoleService();
        $res = $roleService->deleteRole($id);
        return json($res);
    }

    /**
     * 权限设置
     */
    function authorize()
    {
        if (Request::isPost()) {
            //添加或者编辑权限
            $menuid = Request::param('menuid');
            $roleid = Request::param('roleid');

            if (!$roleid) {
                return json(self::createReturn(false, '', '需要授权的角色不存在！'));
            }

            //被选中的菜单项
            $menuIdList = explode(',', $menuid);
            $rbacService = new RbacService();
            return $rbacService->authorizeRoleAccess($roleid, $menuIdList);
        }

        $id = Request::param('id', 0, 'intval');
        return view('authorize', [
            'id' => $id
        ]);
    }

    /**
     * 获取角色权限列表
     */
    function getAuthorizeList()
    {
        //角色ID
        $role_id = Request::param('id', 0, 'intval');
        $roleModel = new RoleModel();
        //获取登录管理员的授权信息
        $select_menu_ids = [];
        //获取当前登录用户角色的所有权限菜单
        $loginUsermenuList = MenuService::getMenuByRole($this->user['role_id'])['data'];
        $rbacService = new RbacService();
        $list = [];
        // 数据格式化
        foreach ($loginUsermenuList as $a) {
            $array = [
                'label'    => $a['name'],
                'id'       => $a['id'],
                'parentid' => $a['parentid'],
                'type'     => $a['type'],
                'status'   => $a['status'],
            ];

            $list [] = $array;
            if ($rbacService->enableRoleAccess($role_id, $a['app'], $a['controller'], $a['action'])['status']) {
                $select_menu_ids [] = $a['id'];
            }
        }

        $menuList = TreeHelper::arrayToTree($list, 0, [
            'parentKey'   => 'parentid',
            'childrenKey' => 'children',
        ]);

        $role_info = $roleModel->where('id', $role_id)->findOrEmpty();
        $res['list'] = $menuList;
        $res['role_id'] = $role_id;
        $res['my_role_id'] = $this->user['role_id'];
        $res['name'] = $role_info['name'];
        $res['select_menu_id'] = $select_menu_ids;
        return json(self::createReturn(true, $res));
    }

}
