<?php

namespace Shop\Logic;

use Common\Model\RelationModel;

class ShopUsersLogic extends RelationModel {

    /**
     * 获取订单商品
     *
     * @param $order_id
     * @return mixed
     */
    public function get_order_goods($order_id){
        $sql = "SELECT og.*,g.original_img FROM __PREFIX__order_goods og LEFT JOIN __PREFIX__goods g ON g.goods_id = og.goods_id WHERE order_id = ".$order_id;
        $goods_list = $this->query($sql);

        $return['status'] = 1;
        $return['msg'] = '';
        $return['result'] = $goods_list;
        return $return;
    }
    /**
     * 改变用户信息
     * @param int $uid
     * @param array $data
     * @return array
     */
    public function update($uid = 0, $data = array()) {
        $db_res = M('ShopUsers')->where(array("userid" => $uid))->data($data)->save();
        if ($db_res) {
            return array(1, "用户信息修改成功");
        } else {
            return array(0, "用户信息修改失败");
        }
    }

    /**
     * 添加用户
     * @param $user
     * @return array
     */
    public function addUser($user) {
        if ($user['email']) {
            $where['email'] = $user['email'];
        }
        if ($user['mobile']) {
            $where['mobile'] = $user['mobile'];
        }
        if (M('ShopUsers')->where($where)->count() > 0) {
            return array('status' => -1, 'msg' => '账号已存在');
        }

        $username = $user['mobile'] ? 'mobile_' . $user['mobile'] : 'email_' . $user['email'];
        //注册cms member模块用户
        $userid = service("Passport")->userRegister($username, $user['password'], $user['email'] ? $user['email'] : $user['mobile'] . "@139.com");
        if (!$userid) {
            return array('status' => -1, 'msg' => '注册失败');
        }
        $user['userid'] = $userid;
        $user['password'] = encrypt($user['password']);
        $user['reg_time'] = time();
        $user_id = M('ShopUsers')->add($user);
        if (!$user_id) {
            return array('status' => -1, 'msg' => '添加失败');
        } else {
            // $pay_points = tpCache('basic.reg_integral'); // 会员注册赠送积分
            // if($pay_points > 0)
            //     accountLog($user_id, 0 , $pay_points , '会员注册赠送积分'); // 记录日志流水
            // 会员注册送优惠券
            // $coupon = M('coupon')->where("send_end_time > ".time()." and ((createnum - send_num) > 0 or createnum = 0) and type = 2")->select();
            // if(!empty($coupon)){
            //     foreach ($coupon as $key => $val)
            //     {
            //         M('coupon_list')->add(array('cid'=>$val['id'],'type'=>$val['type'],'uid'=>$user_id,'send_time'=>time()));
            //         M('Coupon')->where("id = {$val['id']}")->setInc('send_num'); // 优惠券领取数量加一
            //     }
            // }
            return array('status' => 1, 'msg' => '添加成功');
        }
    }

}