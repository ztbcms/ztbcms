<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Admin\Service;


use Shop\Service\BaseService;

class RbacService extends BaseService {

    function createAccessGroup($name, $parentid, $status){
        $data = [
            'name' => $name,
            'parentid' => $parentid,
            'status' => $status
        ];
        $id = M('AccessGroup')->add($data);
        if(!$id){
            return self::createReturn(false, null, '操作失败');
        }

        return self::createReturn(true, $id, '操作成功');
    }


    function updateAccessGroupItems($group_id, array $accessGroupItems = []){
        foreach ($accessGroupItems as $index => $item){
            $accessGroupItems[$index]['group_id'] = $group_id;
        }
        $result = M('AccessGroupItems')->addAll($accessGroupItems);
        if(!$result){
            return self::createReturn(false, null, '操作失败');
        }

        return self::createReturn(true, $result, '操作成功');
    }


    function updateUserAccessGroup($role_id, array $access_groups = []){
        //删除用户组
        M('AccessGroupUser')->where(['role_id' => $role_id])->delete();

        $instert_items = [];
        $accessGroupItems = [];
        foreach ($access_groups as $index => $access_group){
            $instert_items[] = [
                'role_id' => $role_id,
                'group_id' => $access_group['id'],
                'group_name' => $access_group['name'],
                'parentid' => $access_group['parentid'],
            ];

            $accessGroupItems = array_merge($accessGroupItems, self::getAccessItemsByAccessGroupId($access_group['id']));
        }

        M('AccessGroupUser')->addAll($instert_items);

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

    }

    static function getAccessItemsByAccessGroupId($access_group_id){
        $items = M('AccessGroupItems')->where(['group_id' => $access_group_id])->select();
        if(!$items){
            $items = [];
        }

        return self::createReturn(true, $items);
    }

}