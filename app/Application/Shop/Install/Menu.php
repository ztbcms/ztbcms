<?php

return array(
    array(
        //父菜单ID，NULL或者不写系统默认，0为顶级菜单
         "parentid" => 0,
        //地址，[模块/]控制器/方法
         "route" => "Shop/Shop/index",
        //类型，1：权限认证+菜单，0：只作为菜单
         "type" => 0,
        //状态，1是显示，0不显示（需要参数的，建议不显示，例如编辑,删除等操作）
         "status" => 1,
        //名称
         "name" => "商城",
        //备注
         "remark" => "商城相关操作",
        //子菜单列表
         "child" => array(
            array(
                "route" => "Shop/Shop/index",
                "type" => 1,
                "status" => 1,
                "name" => "商城主页",
            ), array(
                "route" => "Shop/Goods/index",
                "type" => 1,
                "status" => 1,
                "name" => "商品管理",
                "child" => array(
                    array(
                        "route" => "Shop/Goods/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "商品列表",
                    ),
                    array(
                        "route" => "Shop/Category/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "商品分类",
                    ),
                    array(
                        "route" => "Shop/Type/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "商品类型",
                    ), array(
                        "route" => "Shop/Spec/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "商品规格",
                    ), array(
                        "route" => "Shop/Attribute/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "商品属性",
                    ), array(
                        "route" => "Shop/Brand/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "商品品牌",
                    ), array(
                        "route" => "Shop/Comment/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "商品评论",
                    ), array(
                        "route" => "Shop/Comment/ask_list",
                        "type" => 1,
                        "status" => 1,
                        "name" => "商品咨询",
                    ),
                ),
            ),
            array(
                "route" => "Shop/Order/index",
                "type" => 1,
                "status" => 1,
                "name" => "订单管理",
                "child" => array(
                    array(
                        "route" => "Shop/Order/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "订单列表",
                    ),
                    array(
                        "route" => "Shop/Order/add_order",
                        "type" => 1,
                        "status" => 1,
                        "name" => "添加订单",
                    ),
                    array(
                        "route" => "Shop/Order/delivery_list",
                        "type" => 1,
                        "status" => 1,
                        "name" => "发货单",
                    ),
                    array(
                        "route" => "Shop/Order/return_list",
                        "type" => 1,
                        "status" => 1,
                        "name" => "退货列表",
                    ),
                    array(
                        "route" => "Shop/Order/order_log",
                        "type" => 1,
                        "status" => 1,
                        "name" => "订单日志",
                    ),
                ),
            ),
            array(
                "route" => "Shop/User/index",
                "type" => 1,
                "status" => 1,
                "name" => "会员管理",
                "child" => array(
                    array(
                        "route" => "Shop/User/index",
                        "type" => 1,
                        "status" => 1,
                        "name" => "会员列表",
                    ),
                ),
            ),
        ),
    ),
);
