<?php

return array(
    array(
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
        "parentid" => 37,
        //地址，[模块/]控制器/方法
        "route" => "Comments/Comments/index",
        //类型，1：权限认证+菜单，0：只作为菜单
        "type" => 0,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
        "status" => 1,
        //名称
        "name" => "评论管理",
        //备注
        "remark" => "评论管理！",
        //子菜单列表
        "child" => array(
            array(
                "route" => "Comments/Comments/config",
                "type" => 1,
                "status" => 1,
                "name" => "评论设置",
                "child" => array(
                    array(
                        "route" => "Comments/Comments/fenbiao",
                        "type" => 1,
                        "status" => 1,
                        "name" => "分表管理",
                        "child" => array(
                            array(
                                "route" => "Comments/Comments/addfenbiao",
                                "type" => 1,
                                "status" => 1,
                                "name" => "创建新的分表",
                            ),
                        ),
                    ),
                    array(
                        "route" => "Comments/Field/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "字段管理",
                        "child" => array(
                            array(
                                "route" => "Comments/Field/add",
                                "type" => 1,
                                "status" => 1,
                                "name" => "添加字段",
                            ),
                            array(
                                "route" => "Comments/Field/delete",
                                "type" => 1,
                                "status" => 0,
                                "name" => "删除字段",
                            ),
                            array(
                                "route" => "Comments/Field/edit",
                                "type" => 1,
                                "status" => 0,
                                "name" => "编辑字段",
                            ),
                        ),
                    ),
                    array(
                        "route" => "Comments/Emotion/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "表情管理",
                    ),
                ),
            ),
            array(
                "route" => "Comments/Comments/index",
                "type" => 1,
                "status" => 1,
                "name" => "评论管理",
                "child" => array(
                    array(
                        "route" => "Comments/Comments/edit",
                        "type" => 1,
                        "status" => 0,
                        "name" => "编辑",
                    ),
                    array(
                        "route" => "Comments/Comments/delete",
                        "type" => 1,
                        "status" => 0,
                        "name" => "删除",
                    ),
                    array(
                        "route" => "Comments/Comments/check",
                        "type" => 1,
                        "status" => 1,
                        "name" => "审核评论",
                    ),
                    array(
                        "route" => "Comments/Comments/spamcomment",
                        "type" => 1,
                        "status" => 0,
                        "name" => "垃圾评论",
                    ),
                    array(
                        "route" => "Comments/Comments/replycomment",
                        "type" => 1,
                        "status" => 0,
                        "name" => "回复评论",
                    ),
                ),
            ),
        ),
    ),
);
