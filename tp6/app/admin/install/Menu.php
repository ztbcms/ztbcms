<?php

return [
    // 消息
    [
        "parentid" => 0,
        "name" => "消息",
        "route" => "admin/AdminMessage/index",
        "type" => 0,
        "status" => 1,
        "remark" => "",
        "child" => [
            [
                "route" => "admin/AdminMessage/index",
                "type" => 1,
                "status" => 1,
                "name" => "所有消息",
                "remark" => "",
                "child" => []
            ],
            [
                "route" => "admin/AdminMessage/noRead",
                "type" => 1,
                "status" => 1,
                "name" => "未读消息",
                "remark" => "",
                "child" => []
            ],
            [
                "route" => "admin/AdminMessage/system",
                "type" => 1,
                "status" => 1,
                "name" => "系统消息",
                "remark" => "",
                "child" => []
            ],
        ]
    ],

    // 我的面板
    [
        "parentid" => 0,
        "name" => "我的面板",
        "route" => "admin/Management/myInfo",
        "type" => 0,
        "status" => 1,
        "remark" => "",
        "child" => [
            [
                "route" => "admin/Management/myBasicsInfo",
                "type" => 1,
                "status" => 1,
                "name" => "修改个人信息",
                "remark" => "",
                "child" => []
            ],
            [
                "route" => "admin/Management/chanpass",
                "type" => 1,
                "status" => 1,
                "name" => "修改密码",
                "remark" => "",
                "child" => []
            ],

        ]
    ],

    // 设置
    [
        "parentid" => 0,
        "name" => "设置",
        "route" => "admin/Setting/index",
        "type" => 0,
        "status" => 1,
        "remark" => "",
        "child" => [
            [
                "parentid" => 0,
                "name" => "菜单管理",
                "route" => "admin/Meu/index",
                "type" => 0,
                "status" => 1,
                "remark" => "",
                "child" => [
                    [
                        "route" => "admin/Menu/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "后台菜单",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Menu/details",
                        "type" => 1,
                        "status" => 1,
                        "name" => "添加菜单",
                        "remark" => "",
                        "child" => []
                    ],

                ]
            ],
            //管理员管理
            [
                "parentid" => 0,
                "name" => "管理员管理",
                "route" => "admin/Management/manage",
                "type" => 0,
                "status" => 1,
                "remark" => "",
                "child" => [
                    [
                        "route" => "admin/Management/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "管理员",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Management/details",
                        "type" => 1,
                        "status" => 1,
                        "name" => "添加管理员",
                        "remark" => "",
                        "child" => []
                    ],

                ]
            ],

            // 角色管理
            [
                "parentid" => 0,
                "name" => "角色管理",
                "route" => "admin/Rbac/index",
                "type" => 0,
                "status" => 1,
                "remark" => "",
                "child" => [
                    [
                        "route" => "admin/Rbac/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "角色列表",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Rbac/roleAdd",
                        "type" => 1,
                        "status" => 1,
                        "name" => "添加角色",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Rbac/authorize",
                        "type" => 1,
                        "status" => 0,
                        "name" => "权限设置",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Management/index",
                        "type" => 1,
                        "status" => 0,
                        "name" => "成员管理",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/AccessGroup/accessGroupRoleSetting",
                        "type" => 1,
                        "status" => 0,
                        "name" => "权限组设置",
                        "remark" => "",
                        "child" => []
                    ],

                ]
            ],

            //权限组管理
            [
                "parentid" => 0,
                "name" => "权限组管理",
                "route" => "admin/AccessGroup/index",
                "type" => 0,
                "status" => 1,
                "remark" => "",
                "child" => [
                    [
                        "route" => "admin/AccessGroup/accessGroupList",
                        "type" => 1,
                        "status" => 1,
                        "name" => "权限组",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/AccessGroup/accessGroupDetails",
                        "type" => 1,
                        "status" => 1,
                        "name" => "添加权限组",
                        "remark" => "",
                        "child" => []
                    ],
                ]
            ],


        ]
    ],

    // 模块
    [
        "parentid" => 0,
        "name" => "模块",
        "route" => "admin/AccessGroup/index",
        "type" => 0,
        "status" => 1,
        "remark" => "",
        "child" => [
            [
                "route" => "admin/Module/index",
                "type" => 1,
                "status" => 1,
                "name" => "本地模块",
                "remark" => "",
                "child" => []
            ],
        ]
    ],

];
