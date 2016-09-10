<?php

return array(
    array(
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
        "parentid" => NULL,
        //地址，[模块/]控制器/方法
        "route" => "Domains/Domains/index",
        //类型，1：权限认证+菜单，0：只作为菜单
        "type" => 0,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
        "status" => 1,
        //名称
        "name" => "域名绑定",
        //备注
        "remark" => "域名绑定管理！",
        //子菜单列表
        "child" => array(
            array(
                "route" => "Domains/Domains/add",
                "type" => 1,
                "status" => 1,
                "name" => "添加域名绑定",
            ),
            array(
                "route" => "Domains/Domains/delete",
                "type" => 1,
                "status" => 0,
                "name" => "删除",
            ),
            array(
                "route" => "Domains/Domains/edit",
                "type" => 1,
                "status" => 0,
                "name" => "编辑",
            ),
        ),
    ),
);
