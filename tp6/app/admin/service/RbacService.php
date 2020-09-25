<?php
/**
 * User: Cycle3
 * Date: 2020/9/25
 */

namespace app\admin\service;

use app\admin\model\AccessGroupItemsModel;
use app\admin\model\AccessGroupModel;
use app\admin\model\AccessGroupRoleModel;
use app\admin\model\AccessModel;
use app\common\service\BaseService;

/**
 * 权限管理
 * Class RbacService
 * @package app\admin\service
 */
class RbacService extends BaseService
{
    /**
     * 获取权限组列表
     * @param $role_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getRoleAccessGroup($role_id){
        $AccessGroupRoleModel = new AccessGroupRoleModel();
        $list = $AccessGroupRoleModel->where(['role_id' => $role_id])->select() ?: [];
        return self::createReturn(true, $list);
    }

    /**
     * 为用户添加权限组
     * @param  int  $role_id
     * @param  array  $access_groups
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function updateRoleAccessGroup($role_id = 0, array $access_groups = []){
        //删除用户组
        $AccessGroupRoleModel = new AccessGroupRoleModel();
        $AccessGroupRoleModel->where(['role_id' => $role_id])->delete();

        $instert_items = [];
        $accessGroupItems = [];
        foreach ($access_groups as $index => $access_group){
            $instert_items[] = [
                'role_id' => $role_id,
                'group_id' => $access_group['group_id'],
                'group_name' => $access_group['group_name'],
                'group_parentid' => $access_group['group_parentid'],
            ];

            $accessItemsByAccessGroupList = self::getAccessItemsByAccessGroupId($access_group['group_id'])['data'];
            if($accessItemsByAccessGroupList) {
                foreach ($accessItemsByAccessGroupList as $k => $v){
                    $accessGroupItems[] = $v;
                }
            }
        }

        $AccessGroupRoleModel->insertAll($instert_items);
        $AccessModel = new AccessModel();
        $AccessModel->where(['role_id' => $role_id])->delete();
        foreach ($accessGroupItems as $index => $accessGroupItem){
            $item = [
                'role_id' => $role_id,
                'app' => $accessGroupItem['app'],
                'controller' => $accessGroupItem['controller'],
                'action' => $accessGroupItem['action'],
                'status' => 1
            ];
            $AccessModel->insert($item);
        }
        return self::createReturn(true, null,'操作成功');
    }

    /**
     * 获取权限组下权限列表
     * @param  int  $access_group_id
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getAccessItemsByAccessGroupId($access_group_id = 0){

        $AccessGroupItems = new AccessGroupItemsModel();
        $items = $AccessGroupItems->where(['group_id' => $access_group_id])->select() ?: [];
        return self::createReturn(true, $items);
    }

    /**
     * 获取含有层次(level)树状的权限组
     *
     * 常用于前端列表页显示
     * @param  int  $parentid
     * @param  int  $level
     * @param  array  $ret
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function getAccessGroupTreeArray($parentid = 0, $level = 0, $ret = []){
        $AccessGroupModel = new AccessGroupModel();
        $groups = $AccessGroupModel->where(['parentid' => $parentid, 'status' => AccessGroupModel::STATUS_ENABLE])->select();
        foreach ($groups as $index => $group){
            $group['level'] = $level;

            $children = $AccessGroupModel->where(['parentid' => $group['id'], 'status' => AccessGroupModel::STATUS_ENABLE])->select();
            if($children){
                $group['hasChildren'] = true;
                $ret[] = $group;
                $ret = self::getAccessGroupTreeArray($group['id'], $level + 1, $ret);
            }else{
                $group['hasChildren'] = false;
                $ret[] = $group;
            }
        }
        return $ret;
    }

    /**
     * 权限组删除
     * @param  array  $access_group_ids
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    static function deleteAccessGroup($access_group_ids = []){
        $AccessGroupModel = new AccessGroupModel();
        $delete_items = $AccessGroupModel
            ->where('id','in',$access_group_ids)
            ->select();
        foreach ($delete_items as $index => $item){
            $children = $AccessGroupModel->where(['parentid' => $item['id']])->select()->toArray();
            if(!empty($children)){
                $ids = array_map(function($c){
                    return $c['id'];
                }, $children);
                self::deleteAccessGroup($ids);
            }
            $AccessGroupModel->where(['id' => $item['id']])->delete();
        }
        return self::createReturn(true, [], '操作成功');
    }
}
