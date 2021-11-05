<?php
/**
 * Author: Jayin
 */

namespace app\admin\controller;


use app\admin\model\MenuModel;
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
    public $noNeedPermission = ['getRoleList', 'getAuthorizeList'];

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

        return view('roleAddOrEdit',[
            'is_superior' => true
        ]);
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
        $userInfo = AdminUserService::getInstance()->getInfo();
        $is_superior = $userInfo['role_id'] == RoleModel::SUPER_ADMIN_ROLE_ID;

        return view('roleAddOrEdit', [
            'id' => $id,
            'is_superior' => $is_superior
        ]);
    }

    /**
     * 获取角色列表
     *
     * 如果是超级管理员，显示所有角色。如果非超级管理员，只显示下级角色
     *
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getRoleList()
    {
        $status = input('status', null);
        $where = [];
        if ($status !== null) {
            $where [] = ['status', '=', $status];
        }
        $RoleModel = new RoleModel();
        $list = $RoleModel->where($where)->select()->toArray();
        $userInfo = AdminUserService::getInstance()->getInfo();
        //如果是超级管理员，显示所有角色。如果非超级管理员，只显示下级角色
        if ($userInfo['role_id'] == RoleModel::SUPER_ADMIN_ROLE_ID) {
            $list =TreeHelper::arrayToTreeList($list,  0, [
                'parentKey' => 'parentid'
            ]);
        } else {
            $list =TreeHelper::arrayToTreeList($list,  $userInfo['role_id'], [
                'parentKey' => 'parentid'
            ]);
        }
        return json(self::createReturn(true, $list));
    }

    /**
     * 添加或者编辑角色
     */
    private function roleAddEdit()
    {
        $id = Request::param('id', '', 'trim');
        $name = Request::param('name', '', 'trim');
        $remark = Request::param('remark', '', 'trim');
        $status = Request::param('status', '1', 'trim');
        $parentid = Request::param('parentid', '0', 'trim');

        if (RoleModel::SUPER_ADMIN_ROLE_ID == $id) {
            return json(self::createReturn(false, [], '超级管理员角色不能被修改'));
        }
        if (empty($name)) {
            return json(self::createReturn(false, null, '请填写角色名称'));
        }
        $userInfo = AdminUserService::getInstance()->getInfo();
        if (empty($parentid)) {
            // 非超管时，上级角色默认是当前登录角色
            if (RoleModel::SUPER_ADMIN_ROLE_ID !== $userInfo['role_id']) {
                $parentid = $userInfo['role_id'];
            }
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
        $userInfo = AdminUserService::getInstance()->getInfo();
        if ($id == $userInfo['role_id']) {
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
        $select_menu_ids = []; // 选中权限
        $select_menu_paretids = []; // 选中的菜单项
        $userInfo = AdminUserService::getInstance()->getInfo();
        //获取当前登录用户角色的所有权限菜单
        $loginUsermenuList = MenuService::getMenuByRole($userInfo['role_id'])['data'];
        // 菜单排序，父类菜单靠前
        $loginUsermenuList = TreeHelper::arrayToTreeList($loginUsermenuList, 0, [
            'parentKey' => 'parentid'
        ]);
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
            // 从父类菜单项开始检测权限，由上至下鉴权
            if($a['type'] == MenuModel::TYPE_MENU){
                //如果是菜单项
                $controller = $a['controller'].$a['id'];
                $action = $a['action'].$a['id'];
            } else {
                $controller = $a['controller'];
                $action = $a['action'];
            }
            if ($rbacService->enableRoleAccess($role_id, $a['app'], $controller, $action, true)['status']) {
                if($a['type'] == MenuModel::TYPE_MENU){
                    $select_menu_paretids [] = $a['id'];
                } else {
                    if(in_array($a['parentid'], $select_menu_paretids) || $a['parentid'] == 0){
                        $select_menu_ids [] = $a['id'];
                    }
                }
            }
        }

        $menuList = TreeHelper::arrayToTree($list, 0, [
            'parentKey'   => 'parentid',
            'childrenKey' => 'children',
        ]);

        $userInfo = AdminUserService::getInstance()->getInfo();
        $role_info = $roleModel->where('id', $role_id)->findOrEmpty();
        $res['list'] = $menuList;
        $res['role_id'] = $role_id;
        $res['my_role_id'] = $userInfo['role_id'];
        $res['name'] = $role_info['name'];
        $res['select_menu_id'] = $select_menu_ids;
        return json(self::createReturn(true, $res));
    }

}
