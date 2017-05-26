<?php

// +----------------------------------------------------------------------
// |  后台菜单模型
// +----------------------------------------------------------------------

namespace Admin\Model;

use Common\Model\Model;

class MenuModel extends Model {

    //自动验证
    protected $_validate = array(
        //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
        array('name', 'require', '菜单名称不能为空！', 1, 'regex', 3),
        array('app', 'require', '模块不能为空！', 1, 'regex', 3),
        array('controller', 'require', '控制器不能为空！', 1, 'regex', 3),
        array('action', 'require', '方法不能为空！', 1, 'regex', 3),
        array('status', array(0, 1), '状态值的范围不正确！', 2, 'in'),
        array('type', array(0, 1), '状态值的范围不正确！', 2, 'in'),
    );

    /**
     * 获取菜单
     * @return array
     */
    public function getMenuList() {
//        $items['0changyong'] = array(
//            "id" => "",
//            "name" => "常用菜单",
//            "parent" => "changyong",
//            "url" => U("Public/changyong"),
//        );
//        foreach (D('Admin/AdminPanel')->getAllPanel(\Admin\Service\User::getInstance()->id) as $r) {
//            $items[$r['mid'] . '0changyong'] = array(
//                "icon" => "",
//                "id" => $r['mid'] . '0changyong',
//                "name" => $r['name'],
//                "parent" => "changyong",
//                "url" => U($r['url']),
//            );
//        }
//        $changyong = array(
//            "changyong" => array(
//                "icon" => "",
//                "id" => "changyong",
//                "name" => "常用",
//                "parent" => "",
//                "url" => "",
//                "items" => $items
//            )
//        );
        $data = $this->getTree(0);
//        return array_merge($changyong, $data ? $data : array());
        return empty($data) ? array() : $data;
    }

    /**
     * 按父ID查找菜单子项
     * @param integer $parentid   父菜单ID  
     * @param boolean $with_self  是否包括他自己
     * @return array
     *
     */
    public function adminMenu($parentid, $with_self = false) {
        //父节点ID
        $parentid = (int) $parentid;
        $result = $this->where(array('parentid' => $parentid, 'status' => 1))->order('listorder ASC,id ASC')->select();
        if (empty($result)) {
            $result = array();
        }
        if ($with_self) {
            $parentInfo = $this->where(array('id' => $parentid))->find();
            $result2[] = $parentInfo ? $parentInfo : array();
            $result = array_merge($result2, $result);
        }
        //是否超级管理员
        if (\Admin\Service\User::getInstance()->isAdministrator()) {
            //如果角色为 1 直接通过
            return $result;
        }
        $array = array();
        //子角色列表
        $child = explode(',', D("Admin/Role")->getArrchildid(\Admin\Service\User::getInstance()->role_id));
        foreach ($result as $v) {
            //方法
            $action = $v['action'];
            //条件
            $where = array('app' => $v['app'], 'controller' => $v['controller'], 'action' => $action, 'role_id' => array('IN', $child));
            //如果是菜单项
            if ($v['type'] == 0) {
                $where['controller'] .= $v['id'];
                $where['action'] .= $v['id'];
            }
            //public开头的通过
            if (preg_match('/^public_/', $action)) {
                $array[] = $v;
            } else {
                if (preg_match('/^ajax_([a-z]+)_/', $action, $_match)) {
                    $action = $_match[1];
                }
                //是否有权限
                if (D('Admin/Access')->isCompetence($where)) {
                    $array[] = $v;
                }
            }
        }
        return $array;
    }

    /**
     * 取得树形结构的菜单
     * @param string $myid
     * @param string $parent
     * @param integer $Level
     * @return array
     */
    public function getTree($myid, $parent = "", $Level = 1) {
        $data = $this->adminMenu($myid);
        $Level++;
        $ret = array();
        if (is_array($data)) {
            foreach ($data as $a) {
                $id = $a['id'];
                $name = $a['app'];
                $controller = $a['controller'];
                $action = $a['action'];
                //附带参数
                $fu = "";
                if ($a['parameter']) {
                    $fu = "?" . $a['parameter'];
                }
                $array = array(
                    "icon" => "",
                    "id" => $id . $name,
                    "name" => $a['name'],
                    "parent" => $parent,
                    "url" => U("{$name}/{$controller}/{$action}{$fu}", array("menuid" => $id)),
                );
                $ret[$id . $name] = $array;
                $child = $this->getTree($a['id'], $id, $Level);
                //由于后台管理界面只支持三层，超出的不层级的不显示
                if ($child && $Level <= 3) {
                    $ret[$id . $name]['items'] = $child;
                }
            }
        }
        return $ret;
    }

    /**
     * 获取菜单导航
     * @return array
     */
    public function getMenu() {
        $menuid = I('get.menuid', 0, 'intval');
        $menuid = $menuid ? $menuid : cookie("menuid", "", array("prefix" => ""));
        $info = $this->where(array("id" => $menuid))->getField("id,action,app,controller,parentid,parameter,type,name");
        $find = $this->where(array("parentid" => $menuid, "status" => 1))->getField("id,action,app,controller,parentid,parameter,type,name");
        if ($find) {
            array_unshift($find, $info[$menuid]);
        } else {
            $find = $info;
        }
        if($find){
            foreach ($find as $k => $v) {
                $find[$k]['parameter'] = "menuid={$menuid}&{$find[$k]['parameter']}";
            }
        }
        return $find;
    }

    // 写入数据前的回调方法 包括新增和更新
    protected function _before_write(&$data) {
        if ($data['app']) {
            $data['app'] = ucwords($data['app']);
        }
        if ($data['controller']) {
            $data['controller'] = ucwords($data['controller']);
        }
//        if ($data['action']) {
//            $data['action'] = strtolower($data['action']);
//        }
        //清除缓存
        cache('Menu', NULL);
    }

    /**
     * 模块安装时进行菜单注册
     * @param array $data 菜单数据
     * @param array $config 模块配置
     * @param int $parentid 父菜单ID
     * @return boolean
     */
    public function installModuleMenu(array $data, array $config, $parentid = 0) {
        if (empty($data) || !is_array($data)) {
            $this->error = '没有数据！';
            return false;
        }
        if (empty($config)) {
            $this->error = '模块配置信息为空！';
            return false;
        }
        //默认安装时父级ID
        $defaultMenuParentid = $this->where(array('app' => 'Admin', 'controller' => 'Module', 'action' => 'local'))->getField('id')? : 42;
        //安装模块名称
        $moduleNama = $config['module'];
        foreach ($data as $rs) {
            if (empty($rs['route'])) {
                $this->error = '菜单信息配置有误，route 不能为空！';
                return false;
            }
            $route = $this->menuRoute($rs['route']);
            $pid = $parentid ? : ((is_null($rs['parentid']) || !isset($rs['parentid'])) ? (int) $defaultMenuParentid : $rs['parentid']);
            $newData = array_merge(array(
                'name' => $rs['name'],
                'parentid' => $pid,
                'type' => isset($rs['type']) ? $rs['type'] : 1,
                'status' => isset($rs['status']) ? $rs['status'] : 0,
                'remark' => $rs['remark']? : '',
                'listorder' => $rs['listorder']? : 0,
                    ), $route);
            if (!$this->create($newData)) {
                $this->error = '菜单信息配置有误，' . $this->error;
                return false;
            }
            $newId = $this->add();
            //是否有子菜单
            if (!empty($rs['child'])) {
                if ($this->installModuleMenu($rs['child'], $config, $newId) !== true) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 把模块安装时，Menu.php中配置的route进行转换
     * @param string $route route内容
     * @param string $moduleNama 安装模块名称
     * @return array
     */
    private function menuRoute($route, $moduleNama) {
        $route = explode('/', $route, 3);
        if (count($route) < 3) {
            array_unshift($route, $moduleNama);
        }
        $data = array(
            'app' => $route[0],
            'controller' => $route[1],
            'action' => $route[2],
        );
        return $data;
    }

    /**
     * 更新缓存
     * @return array|boolean
     */
    public function menu_cache() {
        $data = $this->select();
        if (empty($data)) {
            return false;
        }
        $cache = array();
        foreach ($data as $rs) {
            $cache[$rs['id']] = $rs;
        }
        cache('Menu', $cache);
        return $cache;
    }

}
