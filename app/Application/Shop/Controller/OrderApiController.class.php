<?php
namespace Shop\Controller;

use Shop\Service\OrderService;

class OrderApiController extends BaseController {
    /*
   * 初始化操作
   */
    public function _initialize() {
        parent::_initialize();
    }

    /*
     * 订单详情
     */
    public function order_detail() {
        $id = I('get.id');
        $map['order_id'] = $id;
        $map['user_id'] = $this->userid;
        $order_info = M('order')->where($map)->find();

        if (!$order_info) {
            $this->error('查不到指定订单信息');
        }
        //获取订单商品
        $order_service = new OrderService();
        $data = $order_service->get_order_goods($order_info['order_id']);
        $order_info['goods_list'] = $data;

        //获取订单操作记录
        $order_action = M('order_action')->where(array('order_id' => $id))->select();

        //订单状态对应的中文描述
        $res_data['order_status'] = OrderService::ORDER_STATUS;
        //订单物流状态对应的中文描述
        $res_data['shipping_status'] = OrderService::SHIPPING_STATUS;
        //订单支付状态
        $res_data['pay_status'] = OrderService::PAY_STATUS;
        $res_data['order_info'] = $order_info;
        $res_data['order_action'] = $order_action;
        $this->success($res_data,'',true);
    }

    /*
    * 订单列表
    */
    public function order_list($page = 1, $limit = 10) {
        $where = ' user_id=' . $this->userid;
        //条件搜索
        if (I('get.type')) {
            $where .= C(strtoupper(I('get.type')));
        }
        // 搜索订单 根据商品名称 或者 订单编号
        $search_key = trim(I('search_key'));
        if ($search_key) {
            $where .= " and (order_sn like '%$search_key%' or order_id in (select order_id from `" . C('DB_PREFIX') . "order_goods` where goods_name like '%$search_key%') ) ";
        }

        $total = M('order')->where($where)->count();
        $order_str = "order_id DESC";
        $order_list = M('order')->order($order_str)->where($where)->page($page, $limit)->select();

        //获取订单商品
//        $model = new \Shop\Logic\ShopUsersLogic();
        foreach ($order_list as $k => $v) {
            $data = OrderService::get_order_goods($v['order_id']);
            $order_list[$k]['goods_list'] = $data['result'];
        }
        $res_data['total'] = $total;
        $res_data['page'] = $page;
        $res_data['limit'] = $limit;
        //订单状态对应的中文描述
        $res_data['order_status'] = OrderService::ORDER_STATUS;
        //订单物流状态对应的中文描述
        $res_data['shipping_status'] = OrderService::SHIPPING_STATUS;
        //订单支付状态
        $res_data['pay_status'] = OrderService::PAY_STATUS;
        //订单列表
        $res_data['lists'] = $order_list;
        $this->success($res_data,'',true);
    }

    /**
     * 根据购物车商品下单
     */
    public function create_order_by_cart() {
        $address_id = I("address_id",0); //  收货地址id
        $invoice_title = I('invoice_title'); // 发票抬头
        $pay_points = I("pay_points", 0); //  使用积分
        $user_money = I("user_money", 0); //  使用余额

        $where_cart['userid'] = $this->userid;
        $where_cart['id'] = array('in', I('cart_ids'));
        $order_goods = M('Cart')->where($where_cart)->select();
        //检测购物车是否有选择商品
        if (count($order_goods) == 0) {
            $this->error('你的购物车没有选中商品');
        } // 返回结果状态
        //检测地址是否存在
        $address = M('UserAddress')->where("address_id = '%d'",$address_id)->find();
        if (!$address || !$address_id) {
            $this->error('请先填写收货人信息');
        } // 返回结果状态

        $order_service=new OrderService();

        //按选中购物车的商品，计算出各个部分的价格
        $result = $order_service->calculate_price($this->userid, $order_goods, 0,$pay_points, $user_money);
        if (!$result) {
            $this->error($order_service->get_err_msg());
        }
        $cart_price = array(
            'postFee' => $result['result']['shipping_price'], // 物流费
            'couponFee' => $result['result']['coupon_price'], // 优惠券
            'balance' => $result['result']['user_money'], // 使用用户余额
            'pointsFee' => $result['result']['integral_money'], // 积分支付
            'payables' => $result['result']['order_amount'], // 应付金额
            'goodsFee' => $result['result']['goods_price'],// 商品价格
            'order_prom_id' => $result['result']['order_prom_id'], // 订单优惠活动id
            'order_prom_amount' => $result['result']['order_prom_amount'], // 订单优惠活动优惠了多少钱
        );
        $result = $order_service->addOrder($this->userid, $order_goods, $address_id,'', $invoice_title, 0,
            $cart_price); // 添加订单
        if($result){
            $this->success($result);
        }else{
            $this->error($order_service->get_err_msg());
        }
    }
}
