<?php

return array(
    array(
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
        "parentid" => 0,
        //地址，[模块/]控制器/方法
        "route" => "Addons/Addons/index",
        //类型，1：权限认证+菜单，0：只作为菜单
        "type" => 0,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
        "status" => 1,
        //名称
        "name" => "扩展",
        //备注
        "remark" => "扩展管理！",
        //子菜单列表
        "child" => array(
            array(
                "route" => "Addons/Addons/index",
                "type" => 0,
                "status" => 1,
                "name" => "插件管理",
                "child" => array(
                    array(
                        "route" => "Addons/Addons/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "插件管理",
                        "remark" => "",
                        "child" => array(
                            array(
                                "route" => "Addons/Addons/create",
                                "type" => 1,
                                "status" => 1,
                                "name" => "创建新插件",
                            ),
                            array(
                                "route" => "Addons/Addons/local",
                                "type" => 1,
                                "status" => 1,
                                "name" => "本地安装",
                            ),
                            array(
                                "route" => "Addons/Addons/unpack",
                                "type" => 1,
                                "status" => 0,
                                "name" => "插件打包",
                            ),
                        ),
                    ),
                ),
            ),
            array(
                "route" => "Addons/Addons/addonadmin",
                "type" => 0,
                "status" => 0,
                "name" => "插件后台列表",
            ),
        ),),
);
