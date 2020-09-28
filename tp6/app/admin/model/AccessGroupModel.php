<?php

namespace app\admin\model;

/**
 * 权限分组
 * Class AccessGroupModel
 * @package app\admin\model
 */
class AccessGroupModel extends \think\Model
{

    protected $name = 'access_group';

    /**
     * 启用
     */
    const STATUS_ENABLE = 1;
    /**
     * 取消
     */
    const STATUS_DISABLE = 0;


    function accessGroupItems()
    {
        return $this->hasMany('AccessGroupItemsModel','group_id');
    }

}