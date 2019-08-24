<?php

return array(
    array(
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
        "parentid" => 0,
        //地址，[模块/]控制器/方法
        "route" => "Member/Member/index",
        //类型，1：权限认证+菜单，0：只作为菜单
        "type" => 0,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
        "status" => 1,
        //名称
        "name" => "会员",
        //备注
        "remark" => "会员管理",
        //子菜单列表
        "child" => array(
            array(
                "route" => "Member/DashboardAdmin/index",
                "type" => 1,
                "status" => 1,
                "name" => "会员概览",
            ),
            array(
                "route" => "Member/Setting/index",
                "type" => 1,
                "status" => 1,
                "name" => "设置",
                "child" => array(
                    array(
                        "route" => "Member/Setting/setting",
                        "type" => 1,
                        "status" => 1,
                        "name" => "会员设置",
                        "child" => array(
                            array(
                                "route" => "Member/Setting/myqsl_test",
                                "type" => 1,
                                "status" => 0,
                                "name" => "Ucenter 测试数据库链接",
                            ),
                        ),
                    ),
                    array(
                        "route" => "Member/Model/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "模型管理",
                        "child" => array(
                            array(
                                "route" => "Member/Model/add",
                                "type" => 1,
                                "status" => 1,
                                "name" => "添加模型",
                            ),
                            array(
                                "route" => "Member/Model/edit",
                                "type" => 1,
                                "status" => 0,
                                "name" => "编辑模型",
                            ),
                            array(
                                "route" => "Member/Model/delete",
                                "type" => 1,
                                "status" => 0,
                                "name" => "删除模型",
                            ),
                            array(
                                "route" => "Member/Model/move",
                                "type" => 1,
                                "status" => 0,
                                "name" => "移动模型",
                            ),
                            array(
                                "route" => "Member/Modelfield/index",
                                "type" => 1,
                                "status" => 0,
                                "name" => "字段管理",
                                "child" => array(
                                    array(
                                        "route" => "Member/Field/add",
                                        "type" => 1,
                                        "status" => 1,
                                        "name" => "添加字段",
                                    ),
                                    array(
                                        "route" => "Member/Field/edit",
                                        "type" => 1,
                                        "status" => 0,
                                        "name" => "字段编辑",
                                    ),
                                    array(
                                        "route" => "Member/Field/delete",
                                        "type" => 1,
                                        "status" => 0,
                                        "name" => "删除字段",
                                    ),
                                    array(
                                        "route" => "Member/Field/listorder",
                                        "type" => 1,
                                        "status" => 0,
                                        "name" => "字段排序",
                                    ),
                                    array(
                                        "route" => "Member/Field/disabled",
                                        "type" => 1,
                                        "status" => 0,
                                        "name" => "字段启用与禁用",
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                "route" => "Member/Member/index",
                "type" => 0,
                "status" => 1,
                "name" => "会员管理",
                "child" => array(
                    array(
                        "route" => "Member/Member/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "会员列表",
                        "child" => array(
                            array(
                                "route" => "Member/Member/add",
                                "type" => 1,
                                "status" => 1,
                                "name" => "添加会员",
                            ),
                            array(
                                "route" => "Member/Member/edit",
                                "type" => 1,
                                "status" => 0,
                                "name" => "修改会员",
                            ),
                            array(
                                "route" => "Member/Member/delete",
                                "type" => 1,
                                "status" => 0,
                                "name" => "删除会员",
                            ),
                            array(
                                "route" => "Member/Member/lock",
                                "type" => 1,
                                "status" => 0,
                                "name" => "锁定",
                            ),
                            array(
                                "route" => "Member/Member/unlock",
                                "type" => 1,
                                "status" => 0,
                                "name" => "解除锁定",
                            ),
                            array(
                                "route" => "Member/Member/memberinfo",
                                "type" => 1,
                                "status" => 0,
                                "name" => "资料查看",
                            ),
                        ),
                    ),
                    array(
                        "route" => "Member/Member/userverify",
                        "type" => 1,
                        "status" => 1,
                        "name" => "审核会员",
                    ),
                    array(
                        "route" => "Member/Member/connect",
                        "type" => 1,
                        "status" => 1,
                        "name" => "登录授权管理",
                    ),

                ),
            ),
            array(
                "route" => "Member/Group/index",
                "type" => 1,
                "status" => 1,
                "name" => "会员组管理",
                "child" => array(
                    array(
                        "route" => "Member/Group/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "会员组列表",
                    ),
                    array(
                        "route" => "Member/Group/add",
                        "type" => 1,
                        "status" => 1,
                        "name" => "添加会员组",
                    ),
                    array(
                        "route" => "Member/Group/edit",
                        "type" => 1,
                        "status" => 0,
                        "name" => "编辑会员组",
                    ),
                    array(
                        "route" => "Member/Group/delete",
                        "type" => 1,
                        "status" => 0,
                        "name" => "删除会员组",
                    ),
                    array(
                        "route" => "Member/Group/sort",
                        "type" => 1,
                        "status" => 0,
                        "name" => "会员组排序",
                    ),
                ),
            ),
        ),
    )
);
