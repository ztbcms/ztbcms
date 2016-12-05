<?php

namespace Shop\Logic;

use Common\Model\RelationModel;

class ShopUsersLogic extends RelationModel {
       /*
     * 获取订单商品
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
     * 地址添加/编辑
     * @param $user_id 用户id
     * @param $user_id 地址id(编辑时需传入)
     * @return array
     */
    public function add_address($user_id,$address_id=0,$data){
        $post = $data;
        if($address_id == 0)
        {
            $c = M('UserAddress')->where("userid = $user_id")->count();
            if($c >= 20)
                return array('status'=>-1,'msg'=>'最多只能添加20个收货地址','result'=>'');
        }        
        
        //检查手机格式
        if($post['consignee'] == '')
            return array('status'=>-1,'msg'=>'收货人不能为空','result'=>'');
        if(!$post['province'] || !$post['city'] || !$post['district'])
            return array('status'=>-1,'msg'=>'所在地区不能为空','result'=>'');
        if(!$post['address'])
            return array('status'=>-1,'msg'=>'地址不能为空','result'=>'');
        if(!check_mobile($post['mobile']))
            return array('status'=>-1,'msg'=>'手机号码格式有误','result'=>'');

        //编辑模式
        if($address_id > 0){
            $address = M('UserAddress')->where(array('address_id'=>$address_id,'userid'=> $user_id))->find();
            if($post['is_default'] == 1 && $address['is_default'] != 1)
                M('UserAddress')->where(array('userid'=>$user_id))->save(array('is_default'=>0));
            $row = M('user_address')->where(array('address_id'=>$address_id,'user_id'=> $user_id))->save($post);
            if(!$row)
                return array('status'=>-1,'msg'=>'操作完成','result'=>'');
            return array('status'=>1,'msg'=>'编辑成功','result'=>'');
        }
        //添加模式
        $post['userid'] = $user_id;
        
        // 如果目前只有一个收货地址则改为默认收货地址
        $c = M('UserAddress')->where("userid = {$post['userid']}")->count();        
        if($c == 0)  $post['is_default'] = 1;
        
        $address_id = M('UserAddress')->add($post);
        //如果设为默认地址
        $insert_id = M('UserAddress')->getLastInsID();
        $map['userid'] = $user_id;
        $map['address_id'] = array('neq',$insert_id);
               
        if($post['is_default'] == 1)
            M('UserAddress')->where($map)->save(array('is_default'=>0));
        if(!$address_id)
            return array('status'=>-1,'msg'=>'添加失败','result'=>'');
        return array('status'=>1,'msg'=>'添加成功','result'=>$address_id);
    }
    /**
     * 注册
     * @param $username  邮箱或手机
     * @param $password  密码
     * @param $password2 确认密码
     * @return array
     */
    public function reg($username, $password, $password2) {
        $is_validated = 0;
        if (check_email($username)) {
            $is_validated = 1;
            $map['email_validated'] = 1;
            $map['nickname'] = $map['email'] = $username; //邮箱注册
            $member_username = "email_" . $username;
        }

        if (check_mobile($username)) {
            $is_validated = 1;
            $map['mobile_validated'] = 1;
            $map['nickname'] = $map['mobile'] = $username; //手机注册
            $member_username = "mobile_" . $username;
        }

        if ($is_validated != 1) {
            return array('status' => -1, 'msg' => '请用手机号或邮箱注册');
        }

        if (!$username || !$password) {
            return array('status' => -1, 'msg' => '请输入用户名或密码');
        }

        //验证两次密码是否匹配
        if ($password2 != $password) {
            return array('status' => -1, 'msg' => '两次输入密码不一致');
        }

        //验证是否存在用户名
        if (get_user_info($username, 1) || get_user_info($username, 2)) {
            return array('status' => -1, 'msg' => '账号已存在');
        }

        $map['password'] = encrypt($password);
        $map['reg_time'] = time();
        $map['first_leader'] = cookie('first_leader'); // 推荐人id
        // 如果找到他老爸还要找他爷爷他祖父等
        if ($map['first_leader']) {
            $first_leader = M('ShopUsers')->where("userid = {$map['first_leader']}")->find();
            $map['second_leader'] = $first_leader['first_leader'];
            $map['third_leader'] = $first_leader['second_leader'];
        } else {
            $map['first_leader'] = 0;
        }

        $map['token'] = md5(time() . mt_rand(1, 99999));
        $member_user_id = service("Passport")->userRegister($member_username, $password, $map['email'] ? $map['email'] : $map['mobile'] . "@139.com");
        if (!$member_user_id) {
            return array('status' => -1, 'msg' => '注册失败1');
        } else {
            $map['userid']=$member_user_id;
            $user_id = M('ShopUsers')->add($map);
            if (!$user_id) {
                M('Member')->delete($member_user_id);
                return array('status' => -1, 'msg' => '注册失败2');
            }
        }
        $user = M('ShopUsers')->where("userid = {$user_id}")->find();
        return array('status' => 1, 'msg' => '注册成功', 'result' => $user);
    }
    /*
     * 登陆
     */
    public function login($username, $password) {
        $result = array();
        if (!$username || !$password) {
            return $result = array('status' => 0, 'msg' => '请填写账号或密码');
        }
        $userid = service('Passport')->loginLocal('mobile_' . $username, $password, $cookieTime ? 86400 * 180 : 86400);
        if (!userid) {
            $userid = service('Passport')->loginLocal('email_' . $username, $password, $cookieTime ? 86400 * 180 : 86400);
        }
        if (!$userid) {
            return $result = array('status' => 0, 'msg' => '账号/密码错误');
        }
        $user = M('ShopUsers')->where("userid='%d'", $userid)->find();
        if ($user['is_lock'] == 1) {
            $result = array('status' => -3, 'msg' => '账号异常已被锁定！！！');
        } else {
            //查询用户信息之后, 查询用户的登记昵称
            // $levelId = $user['level'];
            // $levelName = M("user_level")->where("level_id = {$levelId}")->getField("level_name");
            // $user['level_name'] = $levelName;
            $result = array('status' => 1, 'msg' => '登陆成功', 'result' => $user);
        }
        return $result;
    }

    /**
     * 获取指定用户信息
     * @param $uid int 用户UID
     * @param bool $relation 是否关联查询
     *
     * @return mixed 找到返回数组
     */
    public function detail($uid, $relation = true) {
        $user = M('ShopUsers')->where(array('user_id' => $uid))->relation($relation)->find();
        return $user;
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