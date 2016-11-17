<?php

// +----------------------------------------------------------------------
// | 商品分类管理
// +----------------------------------------------------------------------

namespace Shop\Controller;
use Common\Controller\AdminBase;

class AdminApiController extends AdminBase {
    /**
     * ajax 修改指定表数据字段  一般修改状态 比如 是否推荐 是否开启 等 图标切换的
     * table,id_name,id_value,field,value
     */
    public function changeTableVal() {
        $table = I('table'); // 表名
        $id_name = I('id_name'); // 表主键id名
        $id_value = I('id_value'); // 表主键id值
        $field = I('field'); // 修改哪个字段
        $value = I('value'); // 修改字段值
        M($table)->where("$id_name = $id_value")->save(array($field => $value)); // 根据条件保存修改的数据
    }
}
