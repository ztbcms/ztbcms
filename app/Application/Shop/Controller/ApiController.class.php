<?php

// +----------------------------------------------------------------------
// | 公用api
// +----------------------------------------------------------------------

namespace Shop\Controller;
use Common\Controller\Base;

class ApiController extends Base {
    /*
     * 商品分类
     */
    public function get_category() {
        $parent_id = I('get.parent_id'); // 商品分类 父id
        $list = M('goods_category')->where("parent_id = $parent_id")->select();

        foreach ($list as $k => $v) {
            $html .= "<option value='{$v['id']}'>{$v['name']}</option>";
        }

        exit($html);
    }

}
