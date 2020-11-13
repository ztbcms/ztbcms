<?php

return [
    // 概览
    [
        "parentid" => 0,
        "name" => "概览",
        "route" => "admin/Main/index",
        "type" => 1,
        "status" => 1,
        "remark" => "",
        "child" => [
            [
                "route" => "admin/Main/getMainInfo",
                "type" => 1,
                "status" => 0,
                "name" => "获取概览页数据",
                "remark" => "",
                "child" => []
            ],
            [
                "route" => "admin/Cache/cache",
                "type" => 1,
                "status" => 0,
                "name" => "清除缓存",
                "remark" => "",
                "child" => []
            ],
        ]
    ],

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
                "route" => "admin/Role/index",
                "type" => 0,
                "status" => 1,
                "remark" => "",
                "child" => [
                    [
                        "route" => "admin/Role/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "角色列表",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Role/roleAdd",
                        "type" => 1,
                        "status" => 1,
                        "name" => "添加角色",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Role/roleEdit",
                        "type" => 1,
                        "status" => 0,
                        "name" => "编辑角色",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Role/roleDelete",
                        "type" => 1,
                        "status" => 0,
                        "name" => "删除角色",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Role/authorize",
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

            //设置
            [
                "parentid" => 0,
                "name" => "设置",
                "route" => "admin/Config/index",
                "type" => 0,
                "status" => 1,
                "remark" => "",
                "child" => [
                    [
                        "route" => "admin/Config/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "站点设置",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Config/email",
                        "type" => 1,
                        "status" => 1,
                        "name" => "邮箱配置",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Config/attach",
                        "type" => 1,
                        "status" => 1,
                        "name" => "附件配置",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Config/extend",
                        "type" => 1,
                        "status" => 1,
                        "name" => "拓展配置",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Config/editExtend",
                        "type" => 1,
                        "status" => 1,
                        "name" => "添加拓展配置",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Config/doDeleteExtendField",
                        "type" => 1,
                        "status" => 0,
                        "name" => "删除配置字段",
                        "remark" => "",
                        "child" => []
                    ],
                ]
            ],

            //日志
            [
                "parentid" => 0,
                "name" => "日志管理",
                "route" => "admin/Logs/loginLogList",
                "type" => 0,
                "status" => 1,
                "remark" => "",
                "child" => [
                    [
                        "route" => "admin/Logs/loginLogList",
                        "type" => 1,
                        "status" => 1,
                        "name" => "登录日志",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Logs/deleteLoginLog",
                        "type" => 1,
                        "status" => 0,
                        "name" => "删除登录日志",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Logs/adminOperationLogList",
                        "type" => 1,
                        "status" => 1,
                        "name" => "操作日志",
                        "remark" => "",
                        "child" => []
                    ],
                    [
                        "route" => "admin/Logs/deleteAdminOperationLog",
                        "type" => 1,
                        "status" => 0,
                        "name" => "删除操作日志",
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
