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
}
