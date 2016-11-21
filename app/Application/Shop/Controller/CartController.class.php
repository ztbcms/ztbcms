<?php
namespace Shop\Controller;
class CartController extends BaseController {
    /**
     * 初始化函数
     */
    public $cartLogic; // 购物车逻辑操作类
    public $userid = 0;
    public $shop_user = array();    
    public function _initialize() {       
        parent::_initialize();
        $this->cartLogic = new \Shop\Logic\CartLogic();
        
        if(session('user')){
        	$user = session('user');
            $user = M('ShopUsers')->where("userid = {$user['userid']}")->find();
        	$this->user = $user;
        	$this->userid = $user['userid'];
        	$this->assign('user',$user); //存储用户信息               
        }                        
    }
    /**
     * ajax 将商品加入购物车
     */
    function ajaxAddCart(){
        $goods_id = I("goods_id"); // 商品id
        $goods_num = I("goods_num");// 商品数量
        $goods_spec = I("goods_spec"); // 商品规格            
        $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->user_id); // 将商品加入购物车                     
        exit(json_encode($result));       
    }
}