<?php

// +----------------------------------------------------------------------
// |  系统权限配置，用户角色管理
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Admin\Service\User;
use Common\Controller\AdminBase;

class RbacController extends AdminBase {

    //角色管理首页
    public function rolemanage() {
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $roleList = D("Admin/Role")->getTreeArray();
        $userInfo=User::getInstance()->getInfo();
        foreach ($roleList as $k => $rs) {
            $operating = '';
            if ($rs['id'] == 1) {
                $operating = '<font color="#cccccc">权限设置</font> | <a href="' . U('Management/manager', array('role_id' => $rs['id'])) . '">成员管理</a> | <font color="#cccccc">修改</font> | <font color="#cccccc">删除</font>';
            } else {
                $operating = '<a href="' . U("Rbac/authorize", array("id" => $rs["id"])) . '">权限设置</a> | <a href="' . U("Rbac/setting_cat_priv", array("roleid" => $rs["id"])) . '">栏目权限</a> | <a href="' . U('Management/manager', array('role_id' => $rs['id'])) . '">成员管理</a> | <a href="' . U('Rbac/roleedit', array('id' => $rs['id'])) . '">修改</a> | <a class="J_ajax_del" href="' . U('Rbac/roledelete', array('id' => $rs['id'])) . '">删除</a>';
            }
            if ($rs['status'] == 1) {
                $status = "<font color='red'>√</font>";
            } else {
                $status = "<font color='red'>×</font>";
            }

            $roleList[$k]['operating'] = $operating;
            $roleList[$k]['status'] = $status;
        }
        $str = "<tr>
          <td>\$id</td>
          <td>\$spacer\$name</td>
          <td>\$remark</td>
          <td align='center'>\$status</td>
          <td align='center'>\$operating</td>
        </tr>";
        //如果是超级管理员，显示所有角色。如果非超级管理员，只显示下级角色
        $myid=User::getInstance()->isAdministrator() ? 0 : $userInfo['role_id'];
        $tree->init($roleList);
        $this->assign("role", $tree->get_tree($myid, $str));
        $this->assign("data", D("Admin/Role")->order(array("listorder" => "asc", "id" => "desc"))->select())
                ->display();
    }

    //添加角色
    public function roleadd() {
        $userInfo = User::getInstance()->getInfo();
        if (IS_POST) {
            $data = I('post.');
            if(!User::getInstance()->isAdministrator()){
                //如果不是超级管理员，所添加的角色只能是该角色的下级。
                $data['parentid']=$userInfo['role_id'];
            }
            if (D("Admin/Role")->create($data)) {
                if (D("Admin/Role")->add()) {
                    $this->success("添加角色成功！", U("Rbac/rolemanage"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $error = D("Admin/Role")->getError();
                $this->error($error ? $error : '添加失败！');
            }
        } else {
            //向前端渲染登录信息
            $this->assign('userInfo',$userInfo);
            $this->display();
        }
    }

    //删除角色
    public function roledelete() {
        $id = I('get.id', 0, 'intval');
        if (D("Admin/Role")->roleDelete($id)) {
            $this->success("删除成功！", U('Rbac/rolemanage'));
        } else {
            $error = D("Admin/Role")->getError();
            $this->error($error ? $error : '删除失败！');
        }
    }

    //编辑角色
    public function roleedit() {
        $id = I('request.id', 0, 'intval');
        if (empty($id)) {
            $this->error('请选择需要编辑的角色！');
        }
        if (1 == $id) {
            $this->error("超级管理员角色不能被修改！");
        }
        if (IS_POST) {
            if (D("Admin/Role")->create()) {
                if (D("Admin/Role")->where(array('id' => $id))->save()) {
                    $this->success("修改成功！", U('Rbac/rolemanage'));
                } else {
                    $this->error("修改失败！");
                }
            } else {
                $error = D("Admin/Role")->getError();
                $this->error($error ? $error : '修改失败！');
            }
        } else {
            $data = D("Admin/Role")->where(array("id" => $id))->find();
            if (empty($data)) {
                $this->error("该角色不存在！", U('rolemanage'));
            }
            $this->assign("data", $data)
                    ->display();
        }
    }

    //角色授权
    public function authorize() {
        if (IS_POST) {
            $roleid = I('post.roleid', 0, 'intval');
            if (!$roleid) {
                $this->error("需要授权的角色不存在！");
            }
            //被选中的菜单项
            $menuidAll = explode(',', I('post.menuid', ''));
            if (is_array($menuidAll) && count($menuidAll) > 0) {
                //取得菜单数据
                $menu_info = cache('Menu');
                $addauthorize = array();
                //检测数据合法性
                foreach ($menuidAll as $menuid) {
                    if (empty($menu_info[$menuid])) {
                        continue;
                    }
                    $info = array(
                        'app' => $menu_info[$menuid]['app'],
                        'controller' => $menu_info[$menuid]['controller'],
                        'action' => $menu_info[$menuid]['action'],
                        'type' => $menu_info[$menuid]['type'],
                    );
                    //菜单项
                    if ($info['type'] == 0) {
                        $info['app'] = $info['app'];
                        $info['controller'] = $info['controller'] . $menuid;
                        $info['action'] = $info['action'] . $menuid;
                    }
                    $info['role_id'] = $roleid;
                    $info['status'] = $info['type'] ? 1 : 0;
                    $addauthorize[] = $info;
                }
                if (D('Admin/Access')->batchAuthorize($addauthorize, $roleid)) {
                    $this->success("授权成功！", U("Rbac/rolemanage"));
                } else {
                    $error = D("Admin/Access")->getError();
                    $this->error($error ? $error : '授权失败！');
                }
            } else {
                $this->error("没有接收到数据，执行清除授权成功！");
            }
        } else {
            //角色ID
            $roleid = I('get.id', 0, 'intval');
            if (empty($roleid)) {
                $this->error("参数错误！");
            }
            //菜单缓存
            $result = cache("Menu");
            //获取已权限表数据
            $priv_data = D("Admin/Role")->getAccessList($roleid);
            $json = array();
            foreach ($result as $rs) {
                $data = array(
                    'id' => $rs['id'],
                    'parentid' => $rs['parentid'],
                    'name' => $rs['name'] . ($rs['type'] == 0 ? "(菜单项)" : ""),
                    'checked' => D("Admin/Role")->isCompetence($rs, $roleid, $priv_data) ? true : false,
                );
                $json[] = $data;
            }
            $this->assign('json', json_encode($json))
                    ->assign("roleid", $roleid)
                    ->assign('name', D("Admin/Role")->getRoleIdName($roleid))
                    ->display();
        }
    }

    //栏目授权
    public function setting_cat_priv() {
        if (IS_POST) {
            $roleid = I('post.roleid', 0, 'intval');
            $priv = array();
            foreach ($_POST['priv'] as $k => $v) {
                foreach ($v as $e => $q) {
                    $priv[] = array("roleid" => $roleid, "catid" => $k, "action" => $q, "is_admin" => 1);
                }
            }
            C('TOKEN_ON', false);
            //循环验证每天数据是否都合法
            foreach ($priv as $r) {
                $data = M("CategoryPriv")->create($r);
                if (!$data) {
                    $this->error(M("CategoryPriv")->getError());
                } else {
                    $addpriv[] = $data;
                }
            }
            C('TOKEN_ON', true);
            //设置权限前，先删除原来旧的权限
            M("CategoryPriv")->where(array("roleid" => $roleid))->delete();
            //添加新的权限数据，使用D方法有操作记录产生
            M("CategoryPriv")->addAll($addpriv);
            $this->success("权限赋予成功！");
        } else {
            $roleid = I('get.roleid', 0, 'intval');
            if(empty($roleid)){
                $this->error('请指定需要授权的角色！');
            }
            $categorys = cache("Category");
            $tree = new \Tree();
            $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
            $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
            $category_priv = M("CategoryPriv")->where(array("roleid" => $roleid))->select();
            $priv = array();
            foreach ($category_priv as $k => $v) {
                $priv[$v['catid']][$v['action']] = true;
            }

            foreach ($categorys as $k => $v) {
                $v = getCategory($v['catid']);
                if ($v['type'] == 1 || $v['child']) {
                    $v['disabled'] = 'disabled';
                    $v['init_check'] = '';
                    $v['add_check'] = '';
                    $v['delete_check'] = '';
                    $v['listorder_check'] = '';
                    $v['push_check'] = '';
                    $v['move_check'] = '';
                } else {
                    $v['disabled'] = '';
                    $v['add_check'] = isset($priv[$v['catid']]['add']) ? 'checked' : '';
                    $v['delete_check'] = isset($priv[$v['catid']]['delete']) ? 'checked' : '';
                    $v['listorder_check'] = isset($priv[$v['catid']]['listorder']) ? 'checked' : '';
                    $v['push_check'] = isset($priv[$v['catid']]['push']) ? 'checked' : '';
                    $v['move_check'] = isset($priv[$v['catid']]['remove']) ? 'checked' : '';
                    $v['edit_check'] = isset($priv[$v['catid']]['edit']) ? 'checked' : '';
                }
                $v['init_check'] = isset($priv[$v['catid']]['init']) ? 'checked' : '';
                $categorys[$k] = $v;
            }
            $str = "<tr>
	<td align='center'><input type='checkbox'  value='1' onclick='select_all(\$catid, this)' ></td>
	<td>\$spacer\$catname</td>
	<td align='center'><input type='checkbox' name='priv[\$catid][]' \$init_check  value='init' ></td>
	<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$add_check value='add' ></td>
	<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$edit_check value='edit' ></td>
	<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$delete_check  value='delete' ></td>
	<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$listorder_check value='listorder' ></td>
	<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$push_check value='push' ></td>
	<td align='center'><input type='checkbox' name='priv[\$catid][]' \$disabled \$move_check value='remove' ></td>
            </tr>";
            $tree->init($categorys);
            $categorydata = $tree->get_tree(0, $str);
            $this->assign("categorys", $categorydata);
            $this->assign("roleid", $roleid);
            $this->display("categoryrbac");
        }
    }

}
