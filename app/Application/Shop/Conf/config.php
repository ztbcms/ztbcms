<?php
return [
    'ORDER_STATUS' => array(
        0 => '待确认',
        1 => '已确认',
        2 => '已收货',
        3 => '已取消',                
        4 => '已完成',//评价完
        5 => '已作废',
    ), 
    'PAY_STATUS' => array(
        0 => '未支付',
        1 => '已支付',
    ),
    'SHIPPING_STATUS' => array(
        0 => '未发货',
        1 => '已发货',
    	2 => '部分发货'	        
    ),
    'AUTH_CODE' => "ZHUTIBANG", //安装完毕之后不要改变，否则所有密码都会出错
];