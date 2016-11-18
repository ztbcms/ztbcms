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

    /*
     * 获取地区
     */
    public function getRegion(){
        $parent_id = I('get.parent_id');
        $selected = I('get.selected',0);  
        if(I('get.level')==1){
            $data = M('AreaProvince')->where("parentid=$parent_id")->select();
        }elseif(I('get.level')==2){
            $data = M('AreaCity')->where("parentid=$parent_id")->select();
        }else{
            $data = M('AreaDistrict')->where("parentid=$parent_id")->select();
        }
        $html = '';
        if($data){
            foreach($data as $v){
            	if($v['id'] == $selected){
            		$html .= "<option value='{$v['id']}' selected>{$v['areaname']}</option>";
            	}else{
                    $html .= "<option value='{$v['id']}'>{$v['areaname']}</option>";
                }
            }
        }
        echo $html;
    }

}
