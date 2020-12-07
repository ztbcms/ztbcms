<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\model;

use app\common\libs\helper\TreeHelper;
use think\Model;

/**
 * 角色
 * Class RoleModel
 *
 * @package app\admin\model
 */
class RoleModel extends Model
{
    protected $name = 'role';

    /**
     * 超级管理员ID
     */
    const SUPER_ADMIN_ROLE_ID = 1;

    // 启用状态
    /**
     * 启用状态：启用
     */
    const STATUS_YES = 1;
    /**
     * 启用状态：禁用
     */
    const STATUS_NO = 0;

    /**
     * 根据角色ID返回全部权限
     * @deprecated
     * @param  string  $role_id  角色ID
     *
     * @return array
     */
    function getAccessList($role_id)
    {
        $accessList = [];
        if ($role_id == RoleModel::SUPER_ADMIN_ROLE_ID) {
            //超级管理员返回全部
            return [
                [
                    'role_id'    => $role_id,
                    'app'        => '%',
                    'controller' => '%',
                    'action'     => '%',
                ]
            ];
        }
        $AccessModel = new AccessModel();
        $data = $AccessModel->getAccessList($role_id);
        if (empty($data)) {
            return $accessList;
        }
        foreach ($data as $k => $rs) {
            $accessList[$k] = [
                'role_id'    => $rs['role_id'],
                'app'        => $rs['app'],
                'controller' => $rs['controller'],
                'action'     => $rs['action'],
            ];
        }
        return $accessList;
    }

    /**
     * 获取该角色下的全部子角色
     *
     * @deprecated
     * @param  string  $role_id
     *
     * @return string
     */
    function getArrchildid($role_id)
    {
        $roleData = $this->order(array("listorder" => "asc", "id" => "desc"))->select()->toArray();
        // 子角色
        $sonRoleList = TreeHelper::getSonNodeFromArray($roleData, $role_id, [
            'parentKey' => 'parentid'
        ]);
        $arrchildid = $role_id;
        foreach ($sonRoleList as $role) {
            $arrchildid .= ','.$role['id'];
        }
        return $arrchildid;
    }

    /**
     * 获取该角色下的全部子角色ID列表
     *
     * @param  string  $role_id
     * @param  bool  $include_self 是否包括当前角色
     *
     * @return array
     */
    function getChildrenRoleIdList($role_id, $include_self = false)
    {
        $roleData = $this->order(array("listorder" => "asc", "id" => "desc"))->select()->toArray();
        // 子角色
        $sonRoleList = TreeHelper::getSonNodeFromArray($roleData, $role_id, [
            'parentKey' => 'parentid'
        ]);
        $list = $include_self ? [$role_id]:[];
        foreach ($sonRoleList as $role) {
            $list [] = $role['id'];
        }
        return $list;
    }

    /**
     * 递归实现无限极分类
     * @param $array
     * @param  int  $pid  父ID
     * @param  int  $level  分类级别
     * @return array 分好类的数组 直接遍历即可 $level可以用来遍历缩进
     */
    public static function getTree($array, $pid = 0, $level = 0)
    {
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        static $list = [];

        foreach ($array as $key => $value) {
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['parentid'] == $pid) {
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                self::getTree($array, $value['id'], $level + 1);
            }
        }
        return $list;
    }

    /**
     * 删除角色
     * @deprecated
     * @param $roleid
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function roleDelete($roleid)
    {
        if (empty($roleid)) {
            $this->error = '该角色不能被删除！';
            return false;
        }

        if ($roleid == 1) {
            $this->error = '超级管理员角色不能被删除！';
            return false;
        }

        //角色信息
        $info = $this->where('id', $roleid)->find();
        if (empty($info) || !isset($info)) {
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
            $AccessModel = new AccessModel();
            return $AccessModel->where(array('role_id' => $roleid))->delete() !== false ? true : false;
        }
        return false;
    }

    /**
     * 检查指定菜单是否有权限
     * @param  array  $data  menu表中数组，单条
     * @param  string  $roleid  需要检查的角色ID
     * @param  array  $priv_data  已授权权限表数据
     * @return boolean
     */
    public function isCompetence($data = [], $roleid = '', $priv_data = [])
    {
        $priv_arr = ['app', 'controller', 'action'];
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
            'app'     => $data['app'],
        );

        //如果是菜单项加上菜单Id用以区分，保持唯一
        if ($type == 0) {
            $competence["controller"] = $data['controller'].$menuid;
            $competence["action"] = $data['action'].$menuid;
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
     * @param  array  $priv_data
     * @return array
     */
    private function privArrStr($priv_data)
    {
        $privArrStr = [];
        if (empty($priv_data)) {
            return $privArrStr;
        }
        foreach ($priv_data as $rs) {
            $competence = array(
                'role_id'    => $rs['role_id'],
                'app'        => $rs['app'],
                'controller' => $rs['controller'],
                'action'     => $rs['action'],
            );
            $privArrStr[] = implode('', $competence);
        }
        return $privArrStr;
    }

    /**
     * 根据角色Id获取角色名
     * @deprecated
     * @param  int  $roleId  角色id
     * @return string 返回角色名
     */
    public function getRoleIdName($roleId)
    {
        return $this->where(array('id' => $roleId))->value('name');
    }

    /**
     * 获取菜单列表
     * @param  array  $menuList
     * @param  int  $roleid
     * @param  bool  $isadmin
     * @param  array  $userInfo
     * @return array
     */
    public function getMenuAccessList(
        $menuList = [],
        $roleid = 0,
        $isadmin = false,
        $userInfo = [],
        $is_check = true
    ) {

        //获取已获得的权限表数据
        $priv_data = $this->getAccessList($roleid);

        //获取登录账户拥有的权限列表
        $login_priv_data = $this->getAccessList($userInfo['role_id']);

        $json = array();
        foreach ($menuList as $rs) {
            if (!$isadmin) {
                //如果不是超级管理员，筛选出他没有授权的功能。
                if (!$this->isCompetence($rs, $userInfo['role_id'], $login_priv_data)) {
                    continue;
                }
            }

            $data = array(
                'id'       => $rs['id'],
                'parentid' => $rs['parentid'],
                'label'    => $rs['name'].($rs['type'] == 0 ? "(菜单项)" : ""),
            );

            if($is_check) {
                if($this->isCompetence($rs, $roleid, $priv_data))  {
                    $data['checked'] = true;
                } else {
                    $data['checked'] = false;
                }
            }
            $json[] = $data;
        }
        return $json;
    }

    /**
     * 获取菜单列表
     * @param  int  $roleid
     * @param  bool  $isadmin
     * @param  array  $userInfo
     * @param  int  $parentid
     * @param  array  $result
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function getMenuList($roleid = 0, $isadmin = false, $userInfo = [], $parentid = 0, &$result = array())
    {
        $MenuModel = new MenuModel();
        $menu = $MenuModel
            ->where([
                ['parentid','=', $parentid],
            ])
            ->order('listorder desc')
            ->select();
        $list = $this->getMenuAccessList($menu, $roleid, $isadmin, $userInfo,false);
        if (empty($list)) {
            return [];
        }
        foreach ($list as $cm) {
            $thisArr = &$result[];
            $cm["children"] = $this->getMenuList($roleid, $isadmin, $userInfo, $cm["id"], $thisArr);
            $thisArr = $cm;
        }
        return $result;
    }

    /**
     * 获取拥有的菜单id
     *
     * @param $roleid
     * @param $isadmin
     * @param $userInfo
     * @param  bool  $excludeSelectedParent 去除选择的中父菜单选项(为了适配部分前端)
     *
     * @return mixed
     */
    function getSelectMenuId($roleid, $isadmin, $userInfo, $excludeSelectedParent = false)
    {
        $MenuModel = new MenuModel();
        $menu = $MenuModel->select();
        $list = $this->getMenuAccessList($menu, $roleid, $isadmin, $userInfo);
        $checkedMenuId = [];
        $parmentMenuMap = [];
        foreach ($list as $k => $v) {
            if ($v['checked']) {
                $checkedMenuId[] = $v['id'];
            }
            if(!empty($v['parentid'])){
                $parmentMenuMap[$v['parentid']] = true;
            }
        }
        $menuId = [];
        if($excludeSelectedParent){
            foreach ($checkedMenuId as $i => $v){
                if(!isset($parmentMenuMap[$v])){
                    $menuId []= $v;
                }
            }
        } else {
            $menuId = $checkedMenuId;
        }

        return $menuId;
    }

}