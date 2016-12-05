<?php

// +----------------------------------------------------------------------
// | 商品类型管理
// +----------------------------------------------------------------------

namespace Shop\Controller;
use Common\Controller\AdminBase;
use Shop\Util\Page;

class BrandController extends AdminBase {
    /**
     * 品牌列表
     */
    public function index(){  
        $model = M("Brand"); 
        $where = "";
        $keyword = I('keyword');
        $where = $keyword ? " name like '%$keyword%' " : "";
        $count = $model->where($where)->count();
        $Page  = new Page($count,20);        
        $brandList = $model->where($where)->order("`sort` asc")->limit($Page->firstRow.','.$Page->listRows)->select();
        $show  = $Page->show(); 
        $cat_list = M('goods_category')->where("parent_id = 0")->getField('id,name'); // 已经改成联动菜单
        $this->assign('cat_list',$cat_list);       
        $this->assign('show',$show);
        $this->assign('brandList',$brandList);
        $this->display('index');
    }
      /**
     * 添加修改编辑  商品品牌
     */
    public  function addEditBrand(){        
            $id = I('id');
            $model = M("Brand");           
            if(IS_POST)
            {
                    $model->create();
                    if($id)
                        $model->save();
                    else
                        $id = $model->add();
                    
                    $this->success("操作成功",U('Brand/index',array('p'=>$_GET['p'])));               
                    exit;
            }           
           $cat_list = M('goods_category')->where("parent_id = 0")->select(); // 已经改成联动菜单
           $this->assign('cat_list',$cat_list);           
           $brand = $model->find($id);
           $this->assign('brand',$brand);
           $this->display('add_edit_brand');           
    } 
     /**
     * 删除品牌
     */
    public function delBrand()
    {        
        // 判断此品牌是否有商品在使用
        $goods_count = M('Goods')->where("brand_id = {$_GET['id']}")->count('1');        
        if($goods_count)
        {
            $return_arr = array('status' => -1,'msg' => '此品牌有商品在用不得删除!','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);        
            $this->ajaxReturn($return_arr);            
        }
        
        $model = M("Brand"); 
        $model->where('id ='.$_GET['id'])->delete(); 
        $return_arr = array('status' => 1,'msg' => '操作成功','data'  =>'',);   //$return_arr = array('status' => -1,'msg' => '删除失败','data'  =>'',);        
        $this->ajaxReturn($return_arr);
    }     
}