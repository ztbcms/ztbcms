<?php
namespace Shop\Service;

class CartService extends BaseService {
    /**
     * 加入购物车
     * @param     $goods_id
     * @param     $goods_num
     * @param     $goods_spec
     * @param     $session_id
     * @param int $user_id
     * @return bool|int
     */
    function add_cart($goods_id, $goods_num, $goods_spec, $session_id, $user_id = 0) {
        $goods = M('Goods')->where("goods_id = $goods_id")->find(); // 找出这个商品
        $specGoodsPriceList = M('SpecGoodsPrice')->where("goods_id = $goods_id")->getField("key,key_name,price,store_count,sku"); // 获取商品对应的规格价钱 库存 条码
        $user_id = $user_id ? $user_id : 0;
        foreach ($goods_spec as $key => $val) {
            $spec_item[] = $val;
        } // 所选择的规格项
        if (!empty($spec_item)) {
            // 有选择商品规格
            sort($spec_item);
            $spec_key = implode('_', $spec_item);
            if ($specGoodsPriceList[$spec_key]['store_count'] < $goods_num) {
                $this->set_err_msg('商品库存不足');

                return false;
            }
            $spec_price = $specGoodsPriceList[$spec_key]['price']; // 获取规格指定的价格
        }

        $where = " goods_id = $goods_id and spec_key = '$spec_key' "; // 查询购物车是否已经存在这商品
        if ($user_id > 0) {
            $where .= " and (session_id = '$session_id' or user_id = $user_id) ";
        } else {
            $where .= " and  session_id = '$session_id' ";
        }

        $catr_goods = M('Cart')->where($where)->find(); // 查找购物车是否已经存在该商品
        $price = $spec_price ? $spec_price : $goods['shop_price']; // 如果商品规格没有指定价格则用商品原始价格


        if ($user_id) {
            $where .= "  or user_id= $user_id ";
        }
        $catr_count = M('Cart')->where($where)->count(); // 查找购物车商品总数量
        if ($catr_count >= 20) {
            $this->set_err_msg('购物车最多只能放20种商品');

            return false;
        }

        if (!empty($specGoodsPriceList) && empty($goods_spec)) {
            // 有商品规格 但是前台没有传递过来
            $this->set_err_msg('必须传递商品规格');

            return false;
        }
        if ($catr_goods['goods_num'] + $goods_num <= 0) {
            $this->set_err_msg('购买商品数量不能为0');

            return false;
        }
        if (empty($goods)) {
            $this->set_err_msg('购买商品不存在');

            return false;
        }
        if (($goods['store_count'] < ($catr_goods['goods_num'] + $goods_num))) {
            $this->set_err_msg('商品库存不足');

            return false;
        }

        $data = array(
            'user_id' => $user_id,   // 用户id
            'session_id' => $session_id,   // sessionid
            'goods_id' => $goods_id,   // 商品id
            'goods_sn' => $goods['goods_sn'],   // 商品货号
            'goods_name' => $goods['goods_name'],   // 商品名称
            'market_price' => $goods['market_price'],   // 市场价
            'goods_price' => $price,  // 购买价
            'member_goods_price' => $price,  // 会员折扣价 默认为 购买价
            'goods_num' => $goods_num, // 购买数量
            'spec_key' => "{$spec_key}", // 规格key
            'spec_key_name' => "{$specGoodsPriceList[$spec_key]['key_name']}", // 规格 key_name
            'sku' => "{$specGoodsPriceList[$spec_key]['sku']}", // 商品条形码
            'add_time' => time(), // 加入购物车时间
            'prom_type' => $goods['prom_type'],   // 0 普通订单,1 限时抢购, 2 团购 , 3 促销优惠
            'prom_id' => $goods['prom_id'],   // 活动id
        );

        // 如果商品购物车已经存在
        if ($catr_goods) {
            // 如果购物车的已有数量加上 这次要购买的数量  大于  库存输  则不再增加数量
            if (($catr_goods['goods_num'] + $goods_num) > $goods['store_count']) {
                $goods_num = 0;
            }
            $res = M('Cart')->where("id =" . $catr_goods[id])->save(array("goods_num" => ($catr_goods['goods_num'] + $goods_num))); // 数量相加
            $cart_count = cart_goods_num($user_id, $session_id); // 查找购物车数量
            setcookie('cn', $cart_count, null, '/');
        } else {
            $res = M('Cart')->add($data);
            $cart_count = cart_goods_num($user_id, $session_id); // 查找购物车数量
            setcookie('cn', $cart_count, null, '/');
        }
        if($res){
            $cart_count = cart_goods_num($user_id, $session_id); // 查找购物车数量
            return $cart_count;
        }else{
            $this->set_err_msg('添加购物车失败');
            return false;
        }
    }
}