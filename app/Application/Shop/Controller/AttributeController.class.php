<?php

// +----------------------------------------------------------------------
// | 商品属性管理
// +----------------------------------------------------------------------

namespace Shop\Controller;
use Common\Controller\AdminBase;
use Shop\Util\Page;
use Shop\Util\AjaxPage;


class AttributeController extends AdminBase {
     /**
     * 商品属性列表
     */
    public function index(){       
        $goodsTypeList = M("GoodsType")->select();
        $this->assign('goodsTypeList',$goodsTypeList);
        $this->display();
    }   
    
    /**
     *  商品属性列表
     */
    public function ajaxGoodsAttributeList(){            
        //ob_start('ob_gzhandler'); // 页面压缩输出
        $where = ' 1 = 1 '; // 搜索条件                        
        I('type_id')   && $where = "$where and type_id = ".I('type_id') ;                
        // 关键词搜索               
        $model = M('GoodsAttribute');
        $count = $model->where($where)->count();
        $Page       = new AjaxPage($count,13);
        $show = $Page->show();
        $goodsAttributeList = $model->where($where)->order('`order` desc,attr_id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
        $goodsTypeList = M("GoodsType")->getField('id,name');
        $attr_input_type = array(0=>'手工录入',1=>' 从列表中选择',2=>' 多行文本框');
        $this->assign('attr_input_type',$attr_input_type);
        $this->assign('goodsTypeList',$goodsTypeList);        
        $this->assign('goodsAttributeList',$goodsAttributeList);
        $this->assign('page',$show);// 赋值分页输出
        $this->display('ajax_goods_attribute_list');         
    }  
     /**
     * 添加修改编辑  商品属性
     */
    public  function addEditGoodsAttribute(){
                        
            $model = D("GoodsAttribute");                      
            $type = $_POST['attr_id'] > 0 ? 2 : 1; // 标识自动验证时的 场景 1 表示插入 2 表示更新         
            $_POST['attr_values'] = str_replace('_', '', $_POST['attr_values']); // 替换特殊字符
            $_POST['attr_values'] = str_replace('@', '', $_POST['attr_values']); // 替换特殊字符            
            $_POST['attr_values'] = trim($_POST['attr_values']);

            if(($_GET['is_ajax'] == 1) && IS_POST)//ajax提交验证
            {                
                C('TOKEN_ON',false);
                if(!$model->create(NULL,$type))// 根据表单提交的POST数据创建数据对象                 
                {
                    //  编辑
                    $return_arr = array(
                        'status' => -1,
                        'msg'   => '提交不成功!',
                        'data'  => $model->getError(),
                    );
                    $this->ajaxReturn($return_arr);
                }else {                   
                   // C('TOKEN_ON',true); //  form表单提交
                    if ($type == 2)
                    {
                        $model->save(); // 写入数据到数据库                        
                    }
                    else
                    {
                        $insert_id = $model->add(); // 写入数据到数据库                        
                    }
                    $return_arr = array(
                        'status' => 1,
                        'msg'   => '操作成功',                        
                        'data'  => array('url'=>U('Attribute/index')),
                    );
                    $this->ajaxReturn($return_arr);
                }  
            }                
           // 点击过来编辑时                 
           $_GET['attr_id'] = $_GET['attr_id'] ? $_GET['attr_id'] : 0;       
           $goodsTypeList = M("GoodsType")->select();           
           $goodsAttribute = $model->find($_GET['attr_id']);           
           $this->assign('goodsTypeList',$goodsTypeList);                   
           $this->assign('goodsAttribute',$goodsAttribute);
           $this->display('add_edit_goods_attribute');           
    }      
       /**
     * 删除商品属性
     */
    public function delGoodsAttribute()
    {         
        // 判断 有无商品使用该属性
        $count = M("GoodsAttr")->where("attr_id = {$_GET['id']}")->count("1");   
        $count > 0 && $this->error('有商品使用该属性,不得删除!',U('Attribute/index'));                        
        // 删除 属性
        M('GoodsAttribute')->where("attr_id = {$_GET['id']}")->delete();   
        $this->success("操作成功",U('Attribute/index'));
    }  
}