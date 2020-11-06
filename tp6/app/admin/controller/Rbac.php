<?php
/**
 * User: Cycle3
 * Date: 2020/9/23
 */

namespace app\admin\controller;

use app\admin\model\AccessModel;
use app\admin\model\MenuModel;
use app\admin\model\RoleModel;
use app\admin\service\AdminUserService;
use app\admin\service\RbacService;
use app\common\controller\AdminController;
use think\facade\Request;
use app\admin\validate\Role;
use think\exception\ValidateException;

/**
 * 角色权限
 *
 * @package app\admin\controller
 */
class Rbac extends AdminController
{
    /**
     * 角色列表
     */
    public function index()
    {
        return view();
    }

    /**
     * 新增角色
     * @return \think\response\View
     */
    public function roleAdd()
    {
        return view('roleAdd');
    }

    /**
     * 编辑角色
     * @return \think\response\View
     */
    public function roleEdit()
    {
        $id = Request::param('id', '', 'trim');
        $RoleModel = new RoleModel();
        $info = $RoleModel->where(['id' => $id])->find();
        return view('roleEdit', [
            'info' => $info
        ]);
    }

    /**
     * 获取角色列表 api
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getrolemanage()
    {
        $RoleModel = new RoleModel();
        //如果是超级管理员，显示所有角色。如果非超级管理员，只显示下级角色
        if (!$this->is_administrator) {
            $list = $RoleModel->where('parentid='.$this->user->role_id)->select();
            $pid = $this->user->role_id;
        } else {
            $list = $RoleModel->select();
            $pid = 0;
        }
        $list = $RoleModel->getTree($list,$pid);
        foreach ($list as $k => $v) {
            $list[$k]['name'] = str_repeat("ㄧㄧ", $v['level']).' '.$v['name'];
        }
        return json(self::createReturn(true, $list, '获取成功'));
    }

    /**
     * 添加或者编辑角色
     */
    public function roleAddEdit()
    {
        $id = Request::param('id', '', 'trim');
        $name = Request::param('name', '', 'trim');
        $remark = Request::param('remark', '', 'trim');
        $status = Request::param('status', '', 'trim');
        $parentid = Request::param('parentid', '0', 'trim');

        $data['name'] = $name;
        $data['remark'] = $remark;
        $data['status'] = $status;
        $data['parentid'] = $parentid;

        $userInfo = $this->user;
        $RoleModel = new RoleModel();

        try {
            validate(Role::class)->check($data);
            if ($id) {
                //编辑角色
                if (AdminUserService::administratorRoleId == $id) {
                    return json(self::createReturn(false, [], '超级管理员角色不能被修改'));
                }
                unset($data['parentid']);
                $RoleModel->where(array('id' => $id))->update($data);
            } else {
                //新增角色
                if (!$this->is_administrator) {
                    //如果不是超级管理员，所添加的角色只能是该角色的下级。
                    $data['parentid'] = $userInfo['role_id'];
                }
                $RoleModel->insertGetId($data);
            }
            return json(self::createReturn(true, [], '操作成功'));
        } catch (ValidateException $e) {
            return json(self::createReturn(false, [], $e->getError()));
        }
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
        $RoleModel = new RoleModel();
        if ($RoleModel->roleDelete($id)) {
            return json(self::createReturn(true, null, '删除成功'));
        } else {
            $error = $RoleModel->error;
            return json(self::createReturn(false, null, $error ? $error : '删除失败'));
        }
    }

    /**
     * 权限设置
     */
    public function authorize()
    {
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
        $menulist = $RoleModel->getMenuList($roleid, $this->is_administrator, $userInfo, 0);

        $res['list'] = $menulist;
        $res['roleid'] = $roleid;
        $res['name'] = $RoleModel->getRoleIdName($roleid);
        $res['select_menu_id'] = $RoleModel->getSelectMenuId($roleid, $this->is_administrator, $userInfo, true);
        return json(self::createReturn(true, $res));
    }

    /**
     * 添加或者编辑权限
     */
    public function addEditAuthorize()
    {
        $menuid = Request::param('menuid');
        $roleid = Request::param('roleid');

        if (!$roleid) return json(self::createReturn(false,'','需要授权的角色不存在！'));

        //被选中的菜单项
        $menuIdList = explode(',', $menuid);
        $rbacService = new RbacService();
        return $rbacService->authorizeRoleAccess($roleid, $menuIdList);

    }

}
