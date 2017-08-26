<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Admin\Model;

use Common\Model\RelationModel;

/**
 * 权限组
 */
class AccessGroupModel extends RelationModel {
    /**
     * 启用
     */
    const STATUS_ENABLE = 1;
    /**
     * 取消
     */
    const STATUS_DISABLE = 0;

    /**
     * 关联表
     *
     * @var array
     */
    protected $_link = array(
        //关联权限列表
        'accessGroupItems' => array(
            "mapping_type" => self::HAS_MANY,
            "class_name" => 'Admin/AccessGroupItems',
            "foreign_key" => "group_id",
            "mapping_key" => "id",
//            "mapping_order" => "filter_order ASC",
//            "mapping_fields" => "id,username,nickname,store_id"
            "relation_deep" => false
        ),


    );
}