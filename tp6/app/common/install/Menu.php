<?php

return array(
    array(
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
        "parentid" => 0,
        //地址，[模块/]控制器/方法
        "route" => "common/index/index",
        //类型，1：权限认证+菜单，0：只作为菜单
        "type" => 0,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
        "status" => 1,
        //名称
        "name" => "系统管理",
        //备注
        "remark" => "",
        "icon" => "icon-empty",
        //子菜单列表
        "child" => [
            [
                "route" => "common/cron.dashboard/index",
                "type" => 0,
                "status" => 1,
                "name" => "计划任务",
                "child" => [
                    [
                        "route" => "common/cron.dashboard/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "概况",
                    ],
                    [
                        "route" => "common/cron.dashboard/cron",
                        "type" => 1,
                        "status" => 1,
                        "name" => "任务列表",
                    ],
                    [
                        "route" => "common/cron.dashboard/createCron",
                        "type" => 1,
                        "status" => 0,
                        "name" => "新增编辑任务",
                    ],
                    [
                        "route" => "common/cron.dashboard/schedulingLog",
                        "type" => 1,
                        "status" => 0,
                        "name" => "调度日志",
                    ],
                    [
                        "route" => "common/cron.dashboard/cronLog",
                        "type" => 1,
                        "status" => 0,
                        "name" => "任务日志",
                    ],
                ]
            ],
            [
                "route" => "common/message.message/index",
                "type" => 0,
                "status" => 1,
                "name" => "消息管理",
                "remark" => "",
                "child" => [
                    [
                        "route" => "common/message.message/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "消息列表",
                        "remark" => "",
                    ],
                    [
                        "route" => "common/message.message/sendLog",
                        "type" => 1,
                        "status" => 1,
                        "name" => "发送日志",
                        "remark" => "",
                    ],
                ]
            ],
            [
                "route" => "common/upload.upload/setting",
                "type" => 0,
                "status" => 1,
                "name" => "上传管理",
                "child" => [
                    [
                        "route" => "common/upload.upload/setting",
                        "type" => 1,
                        "status" => 1,
                        "name" => "上传配置",
                    ],
                    [
                        "route" => "common/upload.upload/demo",
                        "type" => 1,
                        "status" => 1,
                        "name" => "上传示例",
                    ]
                ]
            ],
            [
                "route" => "common/email.Email/index",
                "type" => 0,
                "status" => 1,
                "name" => "邮件服务",
                "child" => [
                    [
                        "route" => "common/email.Email/config",
                        "type" => 1,
                        "status" => 1,
                        "name" => "配置",
                    ],
                    [
                        "route" => "common/email.Email/sendLog",
                        "type" => 1,
                        "status" => 1,
                        "name" => "发送日志",
                    ],
                    [
                        "route" => "common/email.Email/sendEmail",
                        "type" => 1,
                        "status" => 0,
                        "name" => "发送邮件",
                    ]
                ]
            ],
        ],
    ),
);
