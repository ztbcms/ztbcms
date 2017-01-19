<?php

// +----------------------------------------------------------------------
// |  后台用户权限模型
// +----------------------------------------------------------------------

namespace Admin\Model;

use Common\Model\Model;

class AccessModel extends Model {

    /**
     * 根据角色ID返回全部权限
     * @param string $roleid 角色ID
     * @return array|boolean
     */
    public function getAccessList($roleid) {
        if (empty($roleid)) {
            return false;
        }
        //子角色列表
        $child = explode(',', D("Admin/Role")->getArrchildid($roleid));
        if (empty($child)) {
            return false;
        }
        //查询出该角色拥有的全部权限列表
        $data = $this->where(array('role_id' => array('IN', $child)))->select();
        if (empty($data)) {
            return false;
        }
        $accessList = array();
        foreach ($data as $info) {
            unset($info['status']);
            $accessList[] = $info;
        }
        return $accessList;
    }

    /**
     * 检查用户是否有对应权限
     * @param string $map 方法[模块/控制器/方法]，为空自动获取
     * @return boolean
     */
    public function isCompetence($map = '') {
        //超级管理员
        if (\Admin\Service\User::getInstance()->isAdministrator()) {
            return true;
        }
        if (!is_array($map)) {
            //子角色列表
            $child = explode(',', D("Admin/Role")->getArrchildid(\Admin\Service\User::getInstance()->role_id));
            if (!empty($map)) {
                $map = trim($map, '/');
                $map = explode('/', $map);
                if (empty($map)) {
                    return false;
                }
            } else {
                $map = array(MODULE_NAME, CONTROLLER_NAME, ACTION_NAME,);
            }
            if (count($map) >= 3) {
                list($app, $controller, $action) = $map;
            } elseif (count($map) == 1) {
                $app = MODULE_NAME;
                $controller = CONTROLLER_NAME;
                $action = $map[0];
            } elseif (count($map) == 2) {
                $app = MODULE_NAME;
                list($controller, $action) = $map;
            }
            $map = array('role_id' => array('IN', $child), 'app' => $app, 'controller' => $controller, 'action' => $action);
        }
        $count = $this->where($map)->count();
        return $count ? true : false;
    }

    /**
     * 返回用户权限列表，用于授权
     * @param string $roleid 角色
     * @param string $userId 用户ID
     * @return boolean|array
     */
    public function getUserAccessList($roleid, $userId = 0) {
        if (empty($roleid)) {
            return false;
        }
        $result = cache('Menu');
        $data = $this->where(array("role_id" => $roleid))->field("role_id,app,controller,action")->select();
        $json = array();
        foreach ($result as $rs) {
            $data = array(
                'id' => $rs['id'],
                'checked' => $rs['id'],
                'parentid' => $rs['parentid'],
                'name' => $rs['name'] . ($rs['type'] == 0 ? "(菜单项)" : ""),
                'checked' => D('Admin/Role')->isCompetence($rs, $roleid, $data) ? true : false,
            );
            $json[] = $data;
        }
        return array();
    }

    /**
     * 角色授权
     * @param array $addauthorize 授权数据
     * @param string $roleid 角色id
     * @return boolean
     */
    public function batchAuthorize($addauthorize, $roleid = 0) {
        if (empty($addauthorize)) {
            $this->error = '没有需要授权的权限！';
            return false;
        }
        if (empty($roleid)) {
            if (empty($addauthorize[0]['role_id'])) {
                $this->error = '角色ID不能为空！';
                return false;
            }
            $roleid = $addauthorize[0]['role_id'];
        }
        C('TOKEN_ON', false);
        foreach ($addauthorize as $k => $rs) {
            $addauthorize[$k] = $this->create($rs, 1);
        }
        //删除旧的权限
        $this->where(array("role_id" => $roleid))->delete();
        return $this->addAll($addauthorize) !== false ? true : false;
    }

}
