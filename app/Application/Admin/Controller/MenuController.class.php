<?php

// +----------------------------------------------------------------------
// |  菜单管理
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Common\Controller\AdminBase;

class MenuController extends AdminBase {

    //菜单首页
    public function index() {
        if (IS_POST) {
            $listorders = $_POST['listorders'];
            if (!empty($listorders)) {
                foreach ($listorders as $id => $v) {
                    D("Admin/Menu")->find($id);
                    D("Admin/Menu")->listorder = $v;
                    D("Admin/Menu")->save();
                }
                $this->success('修改成功！', U('index'));
                exit;
            }
        }
        $result = D("Admin/Menu")->order(array("listorder" => "ASC"))->select();
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        foreach ($result as $r) {
            $r['str_manage'] = '<a href="' . U("Menu/add", array("parentid" => $r['id'])) . '">添加子菜单</a> | <a href="' . U("Menu/edit", array("id" => $r['id'])) . '">修改</a> | <a class="J_ajax_del" href="' . U("Menu/delete", array("id" => $r['id'])) . '">删除</a> ';
            $r['status'] = $r['status'] ? "显示" : "不显示";
            $array[] = $r;
        }
        $tree->init($array);
        $str = "<tr>
	<td align='center'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input'></td>
	<td align='center'>\$id</td>
	<td >\$spacer\$name</td>
                    <td align='center'>\$status</td>
	<td align='center'>\$str_manage</td>
	</tr>";
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        $this->display();
    }

    //添加菜单
    public function add() {
        if (IS_POST) {
            if (D("Admin/Menu")->create()) {
                if (D("Admin/Menu")->add()) {
                    $this->success("添加成功！", U("Menu/index"));
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error(D("Admin/Menu")->getError());
            }
        } else {
            $tree = new \Tree();
            $parentid = I('get.parentid', 0, 'intval');
            $result = D("Admin/Menu")->select();
            foreach ($result as $r) {
                $r['selected'] = $r['id'] == $parentid ? 'selected' : '';
                $array[] = $r;
            }
            $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
            $tree->init($array);
            $select_categorys = $tree->get_tree(0, $str);
            $this->assign("select_categorys", $select_categorys);
            $this->display();
        }
    }

    //编辑
    public function edit() {
        if (IS_POST) {
            if (D("Admin/Menu")->create()) {
                if (D("Admin/Menu")->save() !== false) {
                    $this->success("更新成功！", U("Menu/index"));
                } else {
                    $this->error("更新失败！");
                }
            } else {
                $this->error(D("Admin/Menu")->getError());
            }
        } else {
            $tree = new \Tree();
            $id = I('id', 0, 'intval');
            $rs = D("Admin/Menu")->find($id);
            $result = D("Admin/Menu")->select();
            foreach ($result as $r) {
                $r['selected'] = $r['id'] == $rs['parentid'] ? 'selected' : '';
                $array[] = $r;
            }
            $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
            $tree->init($array);
            $select_categorys = $tree->get_tree(0, $str);
            $this->assign("data", $rs);
            $this->assign("select_categorys", $select_categorys);
            $this->display();
        }
    }

    //删除
    public function delete() {
        $id = I('get.id', 0, 'intval');
        $count = D("Admin/Menu")->where(array("parentid" => $id))->count();
        if ($count > 0) {
            $this->error("该菜单下还有子菜单，无法删除！");
        }
        if (D("Admin/Menu")->delete($id) !== false) {
            $this->success("删除菜单成功！");
        } else {
            $this->error("删除失败！");
        }
    }

}
