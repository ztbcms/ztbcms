<?php

// +----------------------------------------------------------------------
// | 商品类型管理
// +----------------------------------------------------------------------

namespace Shop\Controller;
use Common\Controller\AdminBase;
use Shop\Util\AjaxPage;
use Shop\Logic\GoodsLogic;


class SpecController extends AdminBase {
    /**
     * 商品类型  用于设置商品的属性
     */
    public function index() {
        $goodsTypeList = M("GoodsType")->select();
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->display();
    }

    /**
     * 添加修改编辑  商品属性类型
     */
    public function addEditGoodsType() {
        $_GET['id'] = $_GET['id'] ? $_GET['id'] : 0;
        $model = M("GoodsType");
        if (IS_POST) {
            $model->create();
            if ($_GET['id']) {
                $model->save();
            } else {
                $model->add();
            }

            $this->success("操作成功", U('Type/index'));
            exit;
        }
        $goodsType = $model->find($_GET['id']);
        $this->assign('goodsType', $goodsType);
        $this->display('goods_type');
    }
     /**
     * 删除商品类型 
     */
    public function delGoodsType()
    {
        // 判断 商品规格        
        $count = M("Spec")->where("type_id = {$_GET['id']}")->count("1");   
        $count > 0 && $this->error('该类型下有商品规格不得删除!',U('Type/index'));
        // 判断 商品属性        
        $count = M("GoodsAttribute")->where("type_id = {$_GET['id']}")->count("1");   
        $count > 0 && $this->error('该类型下有商品属性不得删除!',U('Type/index'));        
        // 删除分类
        M('GoodsType')->where("id = {$_GET['id']}")->delete();   
        $this->success("操作成功",U('Type/index'));
    }
     /**
     *  商品规格列表
     */
    public function ajaxSpecList(){ 
        //ob_start('ob_gzhandler'); // 页面压缩输出
        $where = ' 1 = 1 '; // 搜索条件                        
        I('type_id')   && $where = "$where and type_id = ".I('type_id') ;        
        // 关键词搜索               
        $model = D('spec');
        $count = $model->where($where)->count();
        $Page       = new AjaxPage($count,20);
        $show = $Page->show();
        $specList = $model->where($where)->order('`type_id` asc')->limit($Page->firstRow.','.$Page->listRows)->select();        
        $GoodsLogic = new GoodsLogic();        
        foreach($specList as $k => $v)
        {       // 获取规格项     
                $arr = $GoodsLogic->getSpecItem($v['id']);
                $specList[$k]['spec_item'] = implode(' , ', $arr);
        }
        
        $this->assign('specList',$specList);
        $this->assign('page',$show);// 赋值分页输出
        $goodsTypeList = M("GoodsType")->select(); // 规格分类
        $goodsTypeList = convert_arr_key($goodsTypeList, 'id');
        $this->assign('goodsTypeList',$goodsTypeList);        
        $this->display('ajax_spec_list');         
    }
     /**
     * 添加修改编辑  商品规格
     */
    public  function addEditSpec(){
                        
            $model = D("spec");                      
            $type = $_POST['id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新             
            if(($_GET['is_ajax'] == 1) && IS_POST)//ajax提交验证
            {                
                C('TOKEN_ON',false);
                if(!$model->create(NULL,$type))// 根据表单提交的POST数据创建数据对象                 
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '',
                        'data'  => $model->getError(),
                    );
                    $this->ajaxReturn($return_arr);
                }else {                   
                   // C('TOKEN_ON',true); //  form表单提交
                    if ($type == 2)
                    {
                        $model->save(); // 写入数据到数据库                        
                        $model->afterSave($_POST['id']);
                    }
                    else
                    {
                        $insert_id = $model->add(); // 写入数据到数据库        
                        $model->afterSave($insert_id);
                    }                    
                    $return_arr = array(
                        'status' => 1,
                        'msg'   => '操作成功',                        
                        'data'  => array('url'=>U('Spec/index')),
                    );
                    $this->ajaxReturn($return_arr);
                }  
            }                
           // 点击过来编辑时                 
           $id = $_GET['id'] ? $_GET['id'] : 0;       
           $spec = $model->find($id);
           $GoodsLogic = new GoodsLogic();  
           $items = $GoodsLogic->getSpecItem($id);
           $spec[items] = implode(PHP_EOL, $items); 
           $this->assign('spec',$spec);
           
           $goodsTypeList = M("GoodsType")->select();           
           $this->assign('goodsTypeList',$goodsTypeList);           
           $this->display('spec');           
    }     
      /**
     * 删除商品规格
     */
    public function delGoodsSpec()
    {
        // 判断 商品规格项
        $count = M("SpecItem")->where("spec_id = {$_GET['id']}")->count("1");   
        $count > 0 && $this->error('清空规格项后才可以删除!',U('Spec/index'));
        // 删除分类
        M('Spec')->where("id = {$_GET['id']}")->delete();   
        $this->success("操作成功",U('Spec/index'));
    }     
}