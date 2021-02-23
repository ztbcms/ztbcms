<?php

return [
    [
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
        "parentid" => 0,
        //地址，[模块/]控制器/方法
        "route"    => "demo/index/index",
        //类型，1：权限认证+菜单，0：只作为菜单
        "type"     => 0,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
        "status"   => 1,
        //名称
        "name"     => "示例",
        //备注
        "remark"   => "",
        //子菜单列表
        "child"    => [
            [
                "route"  => "demo/admin.router/openNewIframe",
                "type"   => 1,
                "status" => 1,
                "name"   => "打开新页面",
                "remark" => "",
                "child"  => []
            ],
            [
                "route"  => "demo/admin.page/diyForm",
                "type"   => 0,
                "status" => 1,
                "name"   => "页面",
                "remark" => "",
                "child"  => [
                    [
                        "route"  => "demo/admin.page/diyForm",
                        "type"   => 1,
                        "status" => 1,
                        "name"   => "在线表单",
                        "remark" => "",
                        "child"  => []
                    ],
                    [
                        "route"  => "demo/admin.page/list",
                        "type"   => 1,
                        "status" => 1,
                        "name"   => "列表页",
                        "remark" => "",
                        "child"  => []
                    ],
                    [
                        "route"  => "demo/admin.page/form",
                        "type"   => 1,
                        "status" => 1,
                        "name"   => "表单页",
                        "remark" => "",
                        "child"  => []
                    ],
                    [
                        "route"  => "demo/admin.page/image",
                        "type"   => 1,
                        "status" => 1,
                        "name"   => "图片预览",
                        "remark" => "",
                        "child"  => []
                    ],
                ]
            ],
            [
                "route"  => "demo/admin.iconfont/aliIconFont",
                "type"   => 1,
                "status" => 1,
                "name"   => "Iconfont",
                "remark" => "",
                "child"  => [
                    [
                        "route"  => "demo/admin.iconfont/aliIconFont",
                        "type"   => 1,
                        "status" => 1,
                        "name"   => "内置Iconfont",
                        "remark" => "",
                        "child"  => []
                    ],
                    [
                        "route"  => "demo/admin.iconfont/elementIconfont",
                        "type"   => 1,
                        "status" => 1,
                        "name"   => "Element Iconfont",
                        "remark" => "",
                        "child"  => []
                    ],
                ]
            ],
            [
                "route"  => "demo/admin.ImageProcess/index",
                "type"   => 1,
                "status" => 1,
                "name"   => "图片处理",
                "remark" => "",
                "child"  => [
                    [
                        "route"  => "demo/admin.Image/index",
                        "type"   => 1,
                        "status" => 1,
                        "name"   => "图片裁剪",
                        "remark" => "",
                        "child"  => []
                    ],
                    [
                        "route"  => "demo/admin.ImageProcess/index",
                        "type"   => 1,
                        "status" => 1,
                        "name"   => "图片合成",
                        "remark" => "",
                        "child"  => []
                    ],
                    [
                        "route"  => "demo/admin.ImageProcess/createSharePoster",
                        "type"   => 1,
                        "status" => 0,
                        "name"   => "图片合成接口权限",
                        "remark" => "",
                        "child"  => []
                    ],
                ]
            ],
        ],
    ]
];
