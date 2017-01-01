<?php

return array(
    array(
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
        "parentid" => 37,
        //地址，[模块/]控制器/方法
        "route" => "Transport/Index/index",
        //类型，1：权限认证+菜单，0：只作为菜单
        "type" => 0,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
        "status" => 1,
        //名称
        "name" => "数据导入导出管理模块",
        //备注
        "remark" => "数据导入导出管理",
        //子菜单列表
        "child" => array(
            array(
                "route" => "Transport/Index/index",
                "type" => 1,
                "status" => 1,
                "name" => "任务列表",
                "child" => array(
                    array(
                        "route" => "Transport/Index/task_create_index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "创建任务",
                    ),
                    array(
                        "route" => "Transport/Index/task_create",
                        "type" => 1,
                        "status" => 0,
                        "name" => "创建任务操作",
                    ),
                    array(
                        "route" => "Transport/Index/task_edit_index",
                        "type" => 1,
                        "status" => 0,
                        "name" => "编辑任务页面",
                    ),
                    array(
                        "route" => "Transport/Index/task_edit",
                        "type" => 1,
                        "status" => 0,
                        "name" => "编辑任务操作",
                    ),
                    array(
                        "route" => "Transport/Index/task_delete",
                        "type" => 1,
                        "status" => 0,
                        "name" => "删除任务操作",
                    ),
                    array(
                        "route" => "Transport/Index/task_update_condition",
                        "type" => 1,
                        "status" => 0,
                        "name" => "更新筛选条件信息操作",
                    ),
                    array(
                        "route" => "Transport/Index/task_update_field",
                        "type" => 1,
                        "status" => 0,
                        "name" => "更新设置字段映射操作",
                    ),
                )
            ),
            array(
                "route" => "Transport/Index/task_logs",
                "type" => 1,
                "status" => 1,
                "name" => "执行记录",
                "child" => array(
                    array(
                        "route" => "Transport/Index/task_log_create",
                        "type" => 1,
                        "status" => 0,
                        "name" => "创建执行日志操作",
                    ),
                    array(
                        "route" => "Transport/Index/task_exec",
                        "type" => 1,
                        "status" => 0,
                        "name" => "执行任务操作",
                    ),
                    array(
                        "route" => "Transport/Index/task_exec_index",
                        "type" => 1,
                        "status" => 0,
                        "name" => "执行任务预览",
                    ),
                )
            ),
        ),
    ),
);
