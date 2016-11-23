<?php
namespace Shop\Controller;

class OrderApiController extends BaseController {
      /*
     * 初始化操作
     */
    public function _initialize() {
        parent::_initialize();
    }
    public function create_order_by_cart(){
        $address_id = I("address_id"); //  收货地址id
        $invoice_title = I('invoice_title'); // 发票抬头
        $pay_points =  I("pay_points",0); //  使用积分
        $user_money =  I("user_money",0); //  使用余额 
        $cartLogic=new \Shop\Logic\CartLogic;
        $where_cart['userid']=$this->userid;
        $where_cart['id']=array('in',I('cart_ids'));
		$order_goods = M('Cart')->where($where_cart)->select();
        //检测购物车是否有选择商品
        if(count($order_goods) == 0 ) exit(json_encode(array('status'=>-2,'msg'=>'你的购物车没有选中商品','result'=>null))); // 返回结果状态
        //检测地址是否存在
        $address = M('UserAddress')->where("address_id = $address_id")->find();
        if(!$address||!$address_id) exit(json_encode(array('status'=>-3,'msg'=>'请先填写收货人信息','result'=>null))); // 返回结果状态
        //按选中购物车的商品，计算出各个部分的价格
        $result = calculate_price($this->userid,$order_goods,$shipping_code,0,$address[province],$address[city],$address[district],$pay_points,$user_money);
        if($result['status'] < 0)	
			exit(json_encode($result));  

        $cart_price = array(
            'postFee'      => $result['result']['shipping_price'], // 物流费
            'couponFee'    => $result['result']['coupon_price'], // 优惠券            
            'balance'      => $result['result']['user_money'], // 使用用户余额
            'pointsFee'    => $result['result']['integral_money'], // 积分支付            
            'payables'     => $result['result']['order_amount'], // 应付金额
            'goodsFee'     => $result['result']['goods_price'],// 商品价格            
            'order_prom_id' => $result['result']['order_prom_id'], // 订单优惠活动id
            'order_prom_amount' => $result['result']['order_prom_amount'], // 订单优惠活动优惠了多少钱
        ); 
        $result = $cartLogic->addOrder($this->userid,$order_goods,$address_id,$shipping_code,$invoice_title,0,$cart_price); // 添加订单                        
        exit(json_encode($result)); 
    }
}
