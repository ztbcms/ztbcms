<?php
/**
 * User: Cycle3
 * Date: 2020/9/23
 */

namespace app\admin\controller;


use app\admin\model\RoleModel;
use app\admin\service\AdminUserService;
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
    public function index()
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
    public function roleAdd()
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
    public function roleEdit()
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
    public function getRoleList()
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
    private function roleAddEdit()
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
    public function roleDelete()
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
    public function authorize()
    {
        if(Request::isPost()){
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
     * 获取权限列表
     */
    public function getAuthorizeList()
    {
        //角色ID
        $roleid = Request::param('id', 0, 'intval');
        $RoleModel = new RoleModel();

        //获取登录管理员的授权信息
        $userInfo = $this->user;
        $is_administrator = $this->user['role_id'] == RoleModel::SUPER_ADMIN_ROLE_ID;
        $menulist = $RoleModel->getMenuList($roleid, $is_administrator, $userInfo, 0);

        $res['list'] = $menulist;
        $res['roleid'] = $roleid;
        $res['name'] = $RoleModel->getRoleIdName($roleid);
        $res['select_menu_id'] = $RoleModel->getSelectMenuId($roleid, $is_administrator, $userInfo, true);
        return json(self::createReturn(true, $res));
    }


}
