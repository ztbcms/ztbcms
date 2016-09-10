<?php

return array(
    array(
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
        "parentid" => 45,
        //地址，[模块/]控制器/方法
        "route" => "Collection/Node/index",
        //类型，1：权限认证+菜单，0：只作为菜单
        "type" => 1,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
        "status" => 1,
        //名称
        "name" => "采集管理",
        //备注
        "remark" => "采集模块是可以批量采集目标网站内容入库！",
        //子菜单列表
        "child" => array(
            array(
                "route" => "Collection/Node/add",
                "type" => 1,
                "status" => 1,
                "name" => "添加采集点",
            ),
            array(
                "route" => "Collection/Node/node_import",
                "type" => 1,
                "status" => 1,
                "name" => "导入采集点",
            ),
            array(
                "route" => "Collection/Node/edit",
                "type" => 1,
                "status" => 0,
                "name" => "编辑采集点",
            ),
            array(
                "route" => "Collection/Node/delete",
                "type" => 1,
                "status" => 0,
                "name" => "删除采集点",
            ),
            array(
                "route" => "Collection/Node/copy",
                "type" => 1,
                "status" => 0,
                "name" => "复制采集点",
            ),
            array(
                "route" => "Collection/Node/export",
                "type" => 1,
                "status" => 0,
                "name" => "导出采集点",
            ),
            array(
                "route" => "Collection/Node/col_url_list",
                "type" => 1,
                "status" => 0,
                "name" => "采集网址入库",
            ),
            array(
                "route" => "Collection/Node/col_content",
                "type" => 1,
                "status" => 0,
                "name" => "采集内容入库",
            ),
            array(
                "route" => "Collection/Node/publist",
                "type" => 1,
                "status" => 0,
                "name" => "内容发布",
                "child" => array(
                    array(
                        "route" => "Collection/Node/content_del",
                        "type" => 1,
                        "status" => 0,
                        "name" => "删除已采集文章",
                    ),
                    array(
                        "route" => "Collection/Node/import",
                        "type" => 1,
                        "status" => 0,
                        "name" => "导入文章",
                    ),
                    array(
                        "route" => "Collection/Node/import_content",
                        "type" => 1,
                        "status" => 0,
                        "name" => "导入文章到模型入库",
                    ),
                    array(
                        "route" => "Collection/Node/import_program_add",
                        "type" => 1,
                        "status" => 0,
                        "name" => "添加导入方案",
                    ),
                    array(
                        "route" => "Collection/Node/import_program_del",
                        "type" => 1,
                        "status" => 0,
                        "name" => "删除导入方案",
                    ),
                    array(
                        "route" => "Collection/Node/import_program_edit",
                        "type" => 1,
                        "status" => 0,
                        "name" => "编辑导入方案",
                    ),
                ),
            ),
        ),
    ),
);
