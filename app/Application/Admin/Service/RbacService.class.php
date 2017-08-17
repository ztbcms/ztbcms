<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Admin\Service;


use Admin\Model\AccessGroupModel;
use Shop\Service\BaseService;

class RbacService extends BaseService {

    static function getAccessGroupById($id){
        $accessGroup = D('Admin/AccessGroup')->where(['id' => $id])->find();
        return self::createReturn(true, $accessGroup);
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


    static function updateAccessGroupItems($group_id, array $accessGroupItems = []){
        foreach ($accessGroupItems as $index => $item){
            $accessGroupItems[$index]['group_id'] = $group_id;
        }
        $result = M('AccessGroupItems')->addAll($accessGroupItems);
        if(!$result){
            return self::createReturn(false, null, '操作失败');
        }

        return self::createReturn(true, $result, '操作成功');
    }


    static function updateUserAccessGroup($role_id, array $access_groups = []){
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

    static function getAccessGroupTree($parentid = 0, $leve = 1){
        $ret = [];
        $groups = D('Admin/AccessGroup')->where(['parentid' => $parentid])->select();
        foreach ($groups as $index => $group){
            $group['leve'] = $leve;

            $children = D('Admin/AccessGroup')->where(['parentid' => $parentid])->select();
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
     * 获取含有层次(level)树状
     * @param int   $parentid
     * @param int   $leve
     * @param array $ret
     * @return array
     */
    static function getAccessGroupTreeArray($parentid = 0, $leve = 0, $ret = []){
        $groups = D('Admin/AccessGroup')->where(['parentid' => $parentid])->select();
        foreach ($groups as $index => $group){
            $group['level'] = $leve;

            $ret[] = $group;
            $children = D('Admin/AccessGroup')->where(['parentid' => $parentid])->select();
            if($children){
                $ret = self::getAccessGroupTreeArray($group['id'], $leve + 1, $ret);
            }
        }

        return $ret;
    }

}