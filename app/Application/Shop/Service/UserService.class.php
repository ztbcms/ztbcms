<?php
namespace Shop\Service;

class UserService extends BaseService {
    public function login($username, $password) {
        if (!$username || !$password) {
            $this->set_err_msg('请填写账号或密码');

            return false;
        }
        //mobile_ 拼凑作为cms的用户名。
        $userid = service('Passport')->loginLocal('mobile_' . $username, $password, 7 * 86400);
        if (!$userid) {
            $this->set_err_msg('账号/密码错误');

            return false;
        }
        $user = M('ShopUsers')->where("userid='%d'", $userid)->find();

        return $user;
    }

    /**
     * 用户注册
     *
     * @param $username 用户名，默认是使用手机登录
     * @param $password
     * @param $password2
     * @return array
     */
    public function register($username, $password, $password2) {
        $is_validated = 0;
        //检查是手机
        if (check_mobile($username)) {
            $is_validated = 1;
            $map['mobile_validated'] = 1;
            $map['nickname'] = $map['mobile'] = $username; //手机注册
            $member_username = "mobile_" . $username;
        }

        if ($is_validated != 1) {
            $this->set_err_msg('请用手机注册');

            return false;
        }

        if (!$username || !$password) {
            $this->set_err_msg('请输入用户名或密码');

            return false;
        }

        //验证两次密码是否匹配
        if ($password2 != $password) {
            $this->set_err_msg('两次输入密码不一致');

            return false;
        }

        //验证是否存在用户名
        if (get_user_info($username, 1) || get_user_info($username, 2)) {
            $this->set_err_msg('账号已存在');

            return false;
        }

        $map['password'] = encrypt($password);
        $map['reg_time'] = time();

        $map['token'] = md5(time() . mt_rand(1, 99999));
        $member_user_id = service("Passport")->userRegister($member_username, $password, $map['mobile'] . "@139.com");
        if (!$member_user_id) {
            $this->set_err_msg('注册失败1');

            return false;
        } else {
            $map['userid'] = $member_user_id;
            $user_id = M('ShopUsers')->add($map);
            if (!$user_id) {
                M('Member')->delete($member_user_id);
                $this->set_err_msg('注册失败2');

                return false;
            }
        }
        $user = M('ShopUsers')->where("userid = {$user_id}")->find();

        return $user;
    }

    /**
     * 添加编辑地址信息
     * @param     $user_id
     * @param int $address_id
     * @param     $data
     * @return bool|int|mixed
     */
    public function add_eidt_address($user_id, $address_id = 0, $data) {
        $post = $data;
        if ($address_id == 0) {
            $c = M('UserAddress')->where("userid = $user_id")->count();
            if ($c >= 20) {
                $this->set_err_msg('最多只能添加20个收货地址');

                return false;
            }
        }

        //检查手机格式
        if ($post['consignee'] == '') {
            $this->set_err_msg('收货人不能为空');

            return false;
        }
        if (!$post['province'] || !$post['city'] || !$post['district']) {
            $this->set_err_msg('所在地区不能为空');

            return false;
        }
        if (!$post['address']) {
            $this->set_err_msg('地址不能为空');

            return false;
        }
        if (!check_mobile($post['mobile'])) {
            $this->set_err_msg('手机号码格式有误'.$post['mobile']);

            return false;
        }

        //编辑模式
        if ($address_id > 0) {
            $address = M('UserAddress')->where(array('address_id' => $address_id, 'userid' => $user_id))->find();
            if ($post['is_default'] == 1 && $address['is_default'] != 1) {
                M('UserAddress')->where(array('userid' => $user_id))->save(array('is_default' => 0));
            }
            $row = M('user_address')->where(array('address_id' => $address_id, 'user_id' => $user_id))->save($post);
            if (!$row) {
                return true;
            }

            return true;
        }
        //添加模式
        $post['userid'] = $user_id;

        // 如果目前只有一个收货地址则改为默认收货地址
        $c = M('UserAddress')->where("userid = {$post['userid']}")->count();
        if ($c == 0) {
            $post['is_default'] = 1;
        }

        $address_id = M('UserAddress')->add($post);
        //如果设为默认地址
        $insert_id = M('UserAddress')->getLastInsID();
        $map['userid'] = $user_id;
        $map['address_id'] = array('neq', $insert_id);

        if ($post['is_default'] == 1) {
            M('UserAddress')->where($map)->save(array('is_default' => 0));
        }
        if (!$address_id) {
            $this->set_err_msg('添加失败');

            return false;
        }

        return $address_id;
    }
}