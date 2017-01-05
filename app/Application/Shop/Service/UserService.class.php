<?php
namespace Shop\Service;

class UserService extends BaseService {
    /**
     * 用户注册
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
        $member_user_id = service("Passport")->userRegister($member_username, $password,
            $map['mobile'] . "@139.com");
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
}