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
    * 购物车列表
    */
    public function index(){
        //如果用户没有登录，是用session_id加入购物车
        $where= $this->userid ? array('user_id'=>$this->userid):array('session_id'=>$this->session_id);
        $cart_list=M("Cart")->where($where)->select();
        $this->assign('cart_list',$cart_list); 
        $this->display();          
    }
    /**
    * 设置购物车数量
    * @param cart_id 购物车id
    */
    public function set_num(){
        $cart_id=I('cart_id');
        $cart=M('Cart')->find($cart_id);
        if($cart&&$cart_id){
            $set_num=(int)I('set_num');
            $goods_id=$cart['goods_id'];
            $goods_num=$set_num-$cart['goods_num'];
            //将sku信息转化成数组
            $spec_key_name=explode(' ',$cart['spec_key_name']);
            $spec_key=explode('_',$cart['spec_key']);
            $spec_arr=array();
            foreach($spec_key_name as $key => $value){
                $spec_arr[explode(':',$value)[0]]=$spec_key[$key];
            };
            $goods_spec=$spec_arr;
            //设置购物车数据操作
            $result = $this->cartLogic->addCart($goods_id, $goods_num, $goods_spec,$this->session_id,$this->user_id); // 将商品加入购物车 
            $result['set_num']=$set_num;
            exit(json_encode($result));       
        }else{
            exit(json_encode(array('status'=>-1,'msg'=>'数据错误')));
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