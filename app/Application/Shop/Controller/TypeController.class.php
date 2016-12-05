<?php

// +----------------------------------------------------------------------
// | 商品类型管理
// +----------------------------------------------------------------------

namespace Shop\Controller;
use Common\Controller\AdminBase;
use Shop\Util\Page;

class TypeController extends AdminBase {
    /**
     * 商品类型  用于设置商品的属性
     */
    public function index() {
        $model = M("GoodsType");
        $count = $model->count();
        $Page = new Page($count, 100);
        $show = $Page->show();
        $goodsTypeList = $model->order("id desc")->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('show', $show);
        $this->assign('goodsTypeList', $goodsTypeList);
        $this->display('index');
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
}