<?php

// +----------------------------------------------------------------------
// |  后台用户角色表
// +----------------------------------------------------------------------

namespace Admin\Model;

use Admin\Service\User;
use Common\Model\Model;

class RoleModel extends Model {

    //array(验证字段,验证规则,错误提示,[验证条件,附加规则,验证时间])
    protected $_validate = array(
        array('name', 'require', '角色名称不能为空！'),
        array('name', '', '该名称已经存在！', 0, 'unique', 3),
        array('status', 'require', '缺少状态！'),
        array('status', array(0, 1), '状态错误，状态只能是1或者0！', 2, 'in'),
    );
    //array(填充字段,填充内容,[填充条件,附加规则])
    protected $_auto = array(
        array('create_time', 'time', 1, 'function'),
        array('update_time', 'time', 3, 'function'),
        array('listorder', '0'),
    );

    /**
     * 通过递归的方式获取该角色下的全部子角色
     * @param string $id
     * @return string
     */
    public function getArrchildid($id) {
        if (empty($this->roleList)) {
            $this->roleList = $this->getTreeArray();
        }
        $arrchildid = $id;
        if (is_array($this->roleList)) {
            foreach ($this->roleList as $k => $cat) {
                if ($cat['parentid'] && $k != $id && $cat['parentid'] == $id) {
                    $arrchildid .= ',' . $this->getArrchildid($k);
                }
            }
        }
        return $arrchildid;
    }

    /**
     * 通过递归的方式获取父角色ID列表
     * @param string $id 角色ID
     * @param string $arrparentid
     * @param int $n
     * @return boolean
     */
    public function getArrparentid($id, $arrparentid = '', $n = 1) {
        if (empty($this->roleList)) {
            $this->roleList = $this->getTreeArray();
        }
        if ($n > 10 || !is_array($this->roleList) || !isset($this->roleList[$id])) {
            return false;
        }
        //获取当前栏目的上级栏目ID
        $parentid = $this->roleList[$id]['parentid'];
        //所有父ID
        $arrparentid = $arrparentid ? $parentid . ',' . $arrparentid : $parentid;
        if ($parentid) {
            $arrparentid = $this->getArrparentid($parentid, $arrparentid, ++$n);
        } else {
            $this->roleList[$id]['arrparentid'] = $arrparentid;
        }
        return $arrparentid;
    }

    /**
     * 删除角色
     * @param int $roleid 角色ID
     * @return boolean
     */
    public function roleDelete($roleid) {
        if (empty($roleid) || $roleid == 1) {
            $this->error = '超级管理员角色不能被删除！';
            return false;
        }
        //角色信息
        $info = $this->where(array('id' => $roleid))->find();
        if (empty($info)) {
            $this->error = '该角色不存在！';
            return false;
        }
        //子角色列表
        $child = explode(',', $this->getArrchildid($roleid));
        if (count($child) > 1) {
            $this->error = '该角色下有子角色，请删除子角色才可以删除！';
            return false;
        }
        $status = $this->where(array('id' => $roleid))->delete();
        if ($status !== false) {
            //删除access中的授权信息
            return D('Admin/Access')->where(array('role_id' => $roleid))->delete() !== false ? true : false;
        }
        return false;
    }

    /**
     * 根据角色Id获取角色名
     * @param int $roleId 角色id
     * @return string 返回角色名
     */
    public function getRoleIdName($roleId) {
        return $this->where(array('id' => $roleId))->getField('name');
    }

    /**
     * 检查指定菜单是否有权限
     * @param array $data menu表中数组，单条
     * @param string $roleid 需要检查的角色ID
     * @param array $priv_data 已授权权限表数据
     * @return boolean
     */
    public function isCompetence($data, $roleid, $priv_data = array()) {
        $priv_arr = array('app', 'controller', 'action');
        if ($data['app'] == '') {
            return false;
        }
        if (empty($priv_data)) {
            //查询已授权权限
            $priv_data = $this->getAccessList($roleid);
        }
        if (empty($priv_data)) {
            return false;
        }
        //菜单id
        $menuid = $data['id'];
        //菜单类型
        $type = $data['type'];
        //去除不要的数据
        foreach ($data as $key => $value) {
            if (!in_array($key, $priv_arr)) {
                unset($data[$key]);
            }
        }
        $competence = array(
            'role_id' => $roleid,
            'app' => $data['app'],
        );
        //如果是菜单项加上菜单Id用以区分，保持唯一
        if ($type == 0) {
            $competence["controller"] = $data['controller'] . $menuid;
            $competence["action"] = $data['action'] . $menuid;
        } else {
            $competence["controller"] = $data['controller'];
            $competence["action"] = $data['action'];
        }
        //检查是否在已授权列表中
        $implode = implode('', $competence);
        $info = in_array(implode('', $competence), $this->privArrStr($priv_data));
        if ($info) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 按规则排序组合
     * @param array $priv_data
     * @return array
     */
    private function privArrStr($priv_data) {
        $privArrStr = array();
        if (empty($priv_data)) {
            return $privArrStr;
        }
        foreach ($priv_data as $rs) {
            $competence = array(
                'role_id' => $rs['role_id'],
                'app' => $rs['app'],
                'controller' => $rs['controller'],
                'action' => $rs['action'],
            );
            $privArrStr[] = implode('', $competence);
        }
        return $privArrStr;
    }

    /**
     * 返回Tree使用的数组
     * @return array
     */
    public function getTreeArray() {
        $roleList = array();
        $roleData = $this->order(array("listorder" => "asc", "id" => "desc"))->select();
        foreach ($roleData as $rs) {
            $roleList[$rs['id']] = $rs;
        }
        return $roleList;
    }

    /**
     * 返回select选择列表
     * @param int $parentid 父节点ID
     * @param string $selectStr 是否要 <select></select>
     * @return string
     */
    public function selectHtmlOption($parentid = 0, $selectStr = '') {
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $str = "'<option value='\$id' \$selected>\$spacer\$name</option>";
        $tree->init($this->getTreeArray());
        if ($selectStr) {
            //$parentid 不是超级管理员，禁止选择其他角色
            $html = '<select '.($parentid <= User::administratorRoleId ? '' : 'disabled' ).' ' . $selectStr . '>';
            $html.=$tree->get_tree(0, $str, $parentid);
            $html.='</select>';
            return $html;
        }
        return $tree->get_tree(0, $str, $parentid);
    }

    /**
     * 返回select选择列表
     * @param int $parentid 父节点ID
     * @param int $selectid 所选ID
     * @param string $selectStr 是否要 <select></select>
     * @return string
     */
    public function selectChildHtmlOption($parentid = 0,$selectid = 0, $selectStr = '') {
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        $str = "'<option  value='\$id' \$selected>\$spacer\$name</option>";
        $tree->init($this->getTreeArray());
        if ($selectStr) {
            $html = '<select ' . $selectStr . '>';
            $html.=$tree->get_tree($parentid, $str, $selectid);
            $html.='</select>';
            return $html;
        }
        return $tree->get_tree($parentid, $str, $selectid);
    }

    /**
     * 根据角色ID返回全部权限
     * @param string $roleid 角色ID
     * @return array  
     */
    public function getAccessList($roleid) {
        $priv_data = array();
        $data = D("Admin/Access")->getAccessList($roleid);
        if (empty($data)) {
            return $priv_data;
        }
        foreach ($data as $k => $rs) {
            $priv_data[$k] = array(
                'role_id' => $rs['role_id'],
                'app' => $rs['app'],
                'controller' => $rs['controller'],
                'action' => $rs['action'],
            );
        }
        return $priv_data;
    }

}
