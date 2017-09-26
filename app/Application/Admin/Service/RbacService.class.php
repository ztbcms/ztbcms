<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Admin\Service;


use Admin\Model\AccessGroupModel;
use System\Service\BaseService;

class RbacService extends BaseService {

    /**
     * 获取一权限组的信息
     *
     * @param $id
     * @return array
     */
    static function getAccessGroupById($id){
        $accessGroup = D('Admin/AccessGroup')->where(['id' => $id])->relation(true)->find();

        if(!$accessGroup['accessGroupItems']){
            $accessGroup['accessGroupItems'] = [];
        }

        return self::createReturn(true, $accessGroup);
    }

    /**
     * @param $role_id
     * @return array
     */
    static function getRoleAccessGroup($role_id){
        $list = D('Admin/AccessGroupRole')->where(['role_id' => $role_id])->select();

        if(!$list){
            $list = [];
        }
        return self::createReturn(true, $list);
    }

    /**
     * @param        $name
     * @param        $parentid
     * @param string $description
     * @param int    $status
     * @return array
     */
    static function createAccessGroup($name, $parentid, $description = '', $status = AccessGroupModel::STATUS_ENABLE){
        $data = [
            'name' => $name,
            'parentid' => $parentid,
            'status' => $status,
            'description' => $description
        ];
        $id = D('Admin/AccessGroup')->add($data);
        if(!$id){
            return self::createReturn(false, null, '操作失败');
        }

        return self::createReturn(true, $id, '操作成功');
    }

    /**
     * @param        $id
     * @param        $name
     * @param        $parentid
     * @param string $description
     * @param int    $status
     * @return array
     */
    static function editAccessGroup($id, $name, $parentid, $description = '', $status = AccessGroupModel::STATUS_ENABLE){
        $data = [
            'name' => $name,
            'parentid' => $parentid,
            'status' => $status,
            'description' => $description
        ];
        $id = D('Admin/AccessGroup')->where(['id' => $id])->save($data);
        if(!$id){
            return self::createReturn(false, null, '操作失败');
        }

        return self::createReturn(true, $id, '操作成功');
    }

    /**
     * 更新权限组下的权限列表
     *
     * @param       $group_id
     * @param array $accessGroupItems
     * @return array
     */
    static function updateAccessGroupItems($group_id, array $accessGroupItems = []){
        foreach ($accessGroupItems as $index => $item){
            $accessGroupItems[$index]['group_id'] = $group_id;
        }

        M('AccessGroupItems')->where(['group_id' => $group_id])->delete();
        $result = M('AccessGroupItems')->addAll($accessGroupItems);
        if(!$result){
            return self::createReturn(false, null, '操作失败');
        }

        return self::createReturn(true, $result, '操作成功');
    }


    /**
     * 更新角色的权限组
     *
     * @param       $role_id
     * @param array $access_groups
     * @return array
     */
    static function updateRoleAccessGroup($role_id, array $access_groups = []){
        //删除用户组
        M('AccessGroupRole')->where(['role_id' => $role_id])->delete();

        $instert_items = [];
        $accessGroupItems = [];
        foreach ($access_groups as $index => $access_group){
            $instert_items[] = [
                'role_id' => $role_id,
                'group_id' => $access_group['group_id'],
                'group_name' => $access_group['group_name'],
                'group_parentid' => $access_group['group_parentid'],
            ];

            $accessGroupItems = array_merge($accessGroupItems, self::getAccessItemsByAccessGroupId($access_group['group_id'])['data']);
        }

        M('AccessGroupRole')->addAll($instert_items);

        M('Access')->where(['role_id' => $role_id])->delete();
        foreach ($accessGroupItems as $index => $accessGroupItem){
            $item = [
                'role_id' => $role_id,
                'app' => $accessGroupItem['app'],
                'controller' => $accessGroupItem['controller'],
                'action' => $accessGroupItem['action'],
                'status' => 1
            ];

            M('Access')->add($item);
        }

        return self::createReturn(true, null);
    }

    /**
     * 删除指定的权限组
     *
     * @param array $access_group_ids
     * @return array
     */
    static function deleteAccessGroup($access_group_ids = []){
        $delete_items = D('Admin/AccessGroup')->where(['id' => ['IN', $access_group_ids]])->select();
        foreach ($delete_items as $index => $item){
            $children = D('Admin/AccessGroup')->where(['parentid' => $item['id']])->select();
            if($children){
                $ids = array_map(function($c){
                    return $c['id'];
                }, $children);

                self::deleteAccessGroup($ids);
            }

            D('Admin/AccessGroup')->where(['id' => $item['id']])->delete();
        }

        return self::createReturn(true, null, '操作成功');
    }

    /**
     * 获取权限组下权限列表
     *
     * @param $access_group_id
     * @return array
     */
    static function getAccessItemsByAccessGroupId($access_group_id){
        $items = M('AccessGroupItems')->where(['group_id' => $access_group_id])->select();
        if(!$items){
            $items = [];
        }

        return self::createReturn(true, $items);
    }

    /**
     * 获取含有层次(level)树状的权限组
     *
     * @param int $parentid
     * @param int $leve
     * @return array
     */
    static function getAccessGroupTree($parentid = 0, $leve = 1){
        $ret = [];
        $groups = D('Admin/AccessGroup')->where(['parentid' => $parentid, 'status' => AccessGroupModel::STATUS_ENABLE])->select();
        foreach ($groups as $index => $group){
            $group['leve'] = $leve;

            $children = D('Admin/AccessGroup')->where(['parentid' => $parentid, 'status' => AccessGroupModel::STATUS_ENABLE])->select();
            if($children){
                $group['children'] = self::getAccessGroupTree($group['id'], $leve + 1);
            }else{
                $group['children'] = [];
            }

            $ret[] = $group;
        }

        return $ret;
    }

    /**
     * 获取含有层次(level)树状的权限组
     *
     * 常用于前端列表页显示
     *
     * @param int   $parentid
     * @param int   $level
     * @param array $ret
     * @return array
     */
    static function getAccessGroupTreeArray($parentid = 0, $level = 0, $ret = []){
        $groups = D('Admin/AccessGroup')->where(['parentid' => $parentid, 'status' => AccessGroupModel::STATUS_ENABLE])->select();

        foreach ($groups as $index => $group){
            $group['level'] = $level;

            $children = D('Admin/AccessGroup')->where(['parentid' => $group['id'], 'status' => AccessGroupModel::STATUS_ENABLE])->select();
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

}