<?php
namespace Shop\Service;

class OrderService extends BaseService {

    /**
     * 订单状态
     */
    const ORDER_STATUS = array(
        0 => '待确认',
        1 => '已确认',
        2 => '已收货',
        3 => '已取消',
        4 => '已完成',
        5 => '已作废',
    );
    /**
     * 支付状态
     */
    const PAY_STATUS = array(
        0 => '未支付',
        1 => '已支付',
    );
    /**
     * 发货状态
     */
    const  SHIPPING_STATUS = array(
        0 => '未发货',
        1 => '已发货',
        2 => '部分发货'
    );

    /**
     *  添加一个订单
     *
     * @param string     $user_id       用户id
     * @param array      $cartList      选中购物车商品
     * @param string     $address_id    地址id
     * @param string     $shipping_code 物流编号
     * @param string     $invoice_title 发票
     * @param string|int $coupon_id     优惠券id
     * @param array      $car_price     各种价格
     * @return string $order_id 返回新增的订单id
     */
    public function addOrder(
        $user_id,
        $cartList,
        $address_id,
        $shipping_code,
        $invoice_title,
        $coupon_id = 0,
        $car_price
    ) {

        // 仿制灌水 1天只能下 50 单  // select * from `tp_order` where user_id = 1  and order_sn like '20151217%'
        $order_count = M('Order')->where("user_id= $user_id and order_sn like '" . date('Ymd') . "%'")->count(); // 查找购物车商品总数量
        if ($order_count >= 50) {
            $this->set_err_msg('一天只能下50个订单');

            return false;
        }

        // 0插入订单 order
        $address = M('UserAddress')->where("address_id = $address_id")->find();
        $shipping = M('Plugin')->where("code = '$shipping_code'")->find();
        $data = array(
            'order_sn' => date('YmdHis') . rand(1000, 9999), // 订单编号
            'user_id' => $user_id, // 用户id
            'consignee' => $address['consignee'], // 收货人
            'province' => $address['province'],//'省份id',
            'city' => $address['city'],//'城市id',
            'district' => $address['district'],//'县',
            'twon' => $address['twon'],// '街道',
            'address' => $address['address'],//'详细地址',
            'mobile' => $address['mobile'],//'手机',
            'zipcode' => $address['zipcode'],//'邮编',
            'email' => $address['email'],//'邮箱',
            'shipping_code' => $shipping_code,//'物流编号',
            'shipping_name' => $shipping['name'], //'物流名称',
            'invoice_title' => $invoice_title, //'发票抬头',
            'goods_price' => $car_price['goodsFee'],//'商品价格',
            'shipping_price' => $car_price['postFee'],//'物流价格',
            'user_money' => $car_price['balance'],//'使用余额',
            'coupon_price' => $car_price['couponFee'],//'使用优惠券',
            'integral' => ($car_price['pointsFee'] * tpCache('shopping.point_rate')), //'使用积分',
            'integral_money' => $car_price['pointsFee'],//'使用积分抵多少钱',
            'total_amount' => ($car_price['goodsFee'] + $car_price['postFee']),// 订单总额
            'order_amount' => $car_price['payables'],//'应付款金额',
            'add_time' => time(), // 下单时间
            'order_prom_id' => $car_price['order_prom_id'],//'订单优惠活动id',
            'order_prom_amount' => $car_price['order_prom_amount'],//'订单优惠活动优惠了多少钱',
        );

        $order_id = M("Order")->data($data)->add();
        if (!$order_id) {
            $this->set_err_msg('添加订单失败');

            return false;
        }

        // 记录订单操作日志
        logOrder($order_id, '您提交了订单，请等待系统确认', '提交订单', $user_id);
        $order = M('Order')->where("order_id = $order_id")->find();
        // 1插入order_goods 表
        $order_goods_ids = array();
        foreach ($cartList as $key => $val) {
            $order_goods_ids[] = $val['goods_id'];
            $goods = M('goods')->where("goods_id = {$val['goods_id']} ")->find();
            $data2['order_id'] = $order_id; // 订单id
            $data2['goods_id'] = $val['goods_id']; // 商品id
            $data2['goods_name'] = $val['goods_name']; // 商品名称
            $data2['goods_sn'] = $val['goods_sn']; // 商品货号
            $data2['goods_num'] = $val['goods_num']; // 购买数量
            $data2['market_price'] = $val['market_price']; // 市场价
            $data2['goods_price'] = $val['goods_price']; // 商品价
            $data2['spec_key'] = $val['spec_key']; // 商品规格
            $data2['spec_key_name'] = $val['spec_key_name']; // 商品规格名称
            $data2['sku'] = $val['sku']; // 商品sku
            $data2['member_goods_price'] = $val['member_goods_price']; // 会员折扣价
            $data2['cost_price'] = $goods['cost_price']; // 成本价
            $data2['give_integral'] = $goods['give_integral']; // 购买商品赠送积分
            $data2['prom_type'] = $val['prom_type']; // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
            $data2['prom_id'] = $val['prom_id']; // 活动id
            M("OrderGoods")->data($data2)->add();
            //扣除商品库存
            if ($val['spec_key']) {
                //如果存在sku库存
                M('SpecGoodsPrice')->where("`goods_id`='%d' AND `key`='%s' ", $val['goods_id'],
                    $val['spec_key'])->setDec('store_count', $val['goods_num']);
            } else {
                M('Goods')->where("goods_id = " . $val['goods_id'])->setDec('store_count', $val['goods_num']); // 商品减少库存
            }
        }
        // 如果应付金额为0  可能是余额支付 + 积分 + 优惠券 这里订单支付状态直接变成已支付
        if ($data['order_amount'] == 0) {
            update_pay_status($order['order_sn'], 1);
        }

        // 3 扣除积分 扣除余额
        if ($car_price['pointsFee'] > 0) {
            M('ShopUsers')->where("userid = $user_id")->setDec('pay_points',
                ($car_price['pointsFee'] * tpCache('shopping.point_rate')));
        } // 消费积分
        if ($car_price['balance'] > 0) {
            M('ShopUsers')->where("userid = $user_id")->setDec('user_money', $car_price['balance']);
        } // 抵扣余额
        // 4 删除已提交订单商品

        $where = array('userid' => $user_id, 'goods_id' => array('in', $order_goods_ids));
        M('Cart')->where($where)->delete();

        // 5 记录log 日志
        $data4['user_id'] = $user_id;
        $data4['user_money'] = -$car_price['balance'];
        $data4['pay_points'] = -($car_price['pointsFee'] * tpCache('shopping.point_rate'));
        $data4['change_time'] = time();
        $data4['desc'] = '下单消费';
        $data4['order_sn'] = $order['order_sn'];
        $data4['order_id'] = $order_id;
        // 如果使用了积分或者余额才记录
        ($data4['user_money'] || $data4['pay_points']) && M("AccountLog")->add($data4);

        return $order_id;
    }

    /**
     * 获取订单商品
     *
     * @param $order_id
     * @return mixed
     */
    static function get_order_goods($order_id) {
//        $sql = "SELECT og.*,g.original_img FROM __PREFIX__order_goods og LEFT JOIN __PREFIX__goods g ON g.goods_id = og.goods_id WHERE order_id = " . $order_id;
        $goods_list = M('OrderGoods')->alias('og')->field('og.*,g.original_img')->join(C('DB_PREFIX') . "goods g ON g.goods_id = og.goods_id")->where("order_id='%d'",
            $order_id)->select();

        return $goods_list ? $goods_list : [];
    }

    /**
     * 计算订单的价格
     *
     * @param int $user_id
     * @param     $order_goods
     * @param int $shipping_price
     * @param int $pay_points
     * @param int $user_money
     * @return array|bool
     */
    public function calculate_price(
        $user_id = 0,
        $order_goods,
        $shipping_price = 0,
        $pay_points = 0,
        $user_money = 0
    ) {
        $user = M('ShopUsers')->where("userid = $user_id")->find(); // 找出这个用户

        if (empty($order_goods)) {
            $this->set_err_msg('商品列表不能为空');

            return false;
        }

        $goods_id_arr = get_arr_column($order_goods, 'goods_id');
        $goods_arr = M('goods')->where("goods_id in(" . implode(',',
                $goods_id_arr) . ")")->getField('goods_id,weight,market_price,is_free_shipping'); // 商品id 和重量对应的键值对

        $goods_weight = 0;
        $goods_price = 0;
        foreach ($order_goods as $key => $val) {
            // 如果传递过来的商品列表没有定义会员价
            if (!array_key_exists('member_goods_price', $val)) {
                $user['discount'] = $user['discount'] ? $user['discount'] : 1; // 会员折扣 不能为 0
                $order_goods[$key]['member_goods_price'] = $val['member_goods_price'] = $val['goods_price'] * $user['discount'];
            }
            //如果商品不是包邮的
            if ($goods_arr[$val['goods_id']]['is_free_shipping'] == 0) {
                $goods_weight += $goods_arr[$val['goods_id']]['weight'] * $val['goods_num'];
            }
            //累积商品重量 每种商品的重量 * 数量

            $order_goods[$key]['goods_fee'] = $val['goods_num'] * $val['member_goods_price']; // 小计
            $order_goods[$key]['store_count'] = getGoodNum($val['goods_id'], $val['spec_key']); // 最多可购买的库存数量
            if ($order_goods[$key]['store_count'] <= 0) {
                $this->set_err_msg('库存不足,请重新下单');

                return false;
            }

            $goods_price += $order_goods[$key]['goods_fee']; // 商品总价
            $cut_fee = 0;
            $cut_fee += $val['goods_num'] * $val['market_price'] - $val['goods_num'] * $val['member_goods_price']; // 共节约
            $anum = 0;
            $anum += $val['goods_num']; // 购买数量
        }

        if ($pay_points && ($pay_points > $user['pay_points'])) {
            $this->set_err_msg("你的账户可用积分为:" . $user['pay_points']);

            return false;
        }
        // 返回结果状态
        if ($user_money && ($user_money > $user['user_money'])) {
            $this->set_err_msg("你的账户可用余额为:" . $user['user_money']);

            return false;
        }
        // 返回结果状态

        $order_amount = $goods_price + $shipping_price; // 应付金额 = 商品价格 + 物流费

        $pay_points = ($pay_points / 100); // 积分支付 100 积分等于 1块钱
        $pay_points = ($pay_points > $order_amount) ? $order_amount : $pay_points; // 假设应付 1块钱 而用户输入了 200 积分 2块钱, 那么就让 $pay_points = 1块钱 等同于强制让用户输入1块钱
        $order_amount = $order_amount - $pay_points; //  积分抵消应付金额

        $user_money = ($user_money > $order_amount) ? $order_amount : $user_money; // 余额支付原理等同于积分
        $order_amount = $order_amount - $user_money; //  余额支付抵应付金额

        $total_amount = $goods_price + $shipping_price;
        //订单总价  应付金额  物流费  商品总价 节约金额 共多少件商品 积分  余额  优惠券
        $result = array(
            'total_amount' => $total_amount, // 商品总价
            'order_amount' => $order_amount, // 应付金额
            'shipping_price' => $shipping_price, // 物流费
            'goods_price' => $goods_price, // 商品总价
            'cut_fee' => $cut_fee, // 共节约多少钱
            'anum' => $anum, // 商品总共数量
            'integral_money' => $pay_points, // 积分抵消金额
            'user_money' => $user_money, // 使用余额
            'coupon_price' => 0, // 优惠券抵消金额
            'order_goods' => $order_goods, // 商品列表 多加几个字段原样返回
        );

        return $result;
    }
}