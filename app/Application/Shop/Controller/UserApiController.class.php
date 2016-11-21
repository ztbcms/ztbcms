<?php
namespace Shop\Controller;
use Shop\Logic\ShopUsersLogic;

class UserApiController extends BaseController {
    /**
     * 获取登录用户信息
     */
    public function index() {
        $userinfo = service("Passport")->getInfo();
		exit(json_encode($userinfo));
        $shop_user = M('ShopUsers')->find($userinfo['userid']);
        if ($userinfo && $shop_user) {
            exit(json_encode(array('status' => 500, 'data' => $shop_user, 'msg' => 'ok')));
        } else {
            exit(json_encode(array('status' => 500, 'msg' => '没有登录')));
        }
    }
    /**
     * 用户登录api
     */
    public function login() {
        $username = I('post.username');
        $password = I('post.password');
        $username = trim($username);
        $password = trim($password);

        $logic = new ShopUsersLogic();
        $res = $logic->login($username, $password);
        if ($res['status'] == 1) {
            $res['url'] = urldecode(I('post.referurl'));
            session('user', $res['result']);
            setcookie('user_id', $res['result']['user_id'], null, '/');
            setcookie('is_distribut', $res['result']['is_distribut'], null, '/');
            $nickname = empty($res['result']['nickname']) ? $username : $res['result']['nickname'];
            setcookie('uname', urlencode($nickname), null, '/');
            setcookie('cn', 0, time() - 3600, '/');
            $cartLogic = new \Shop\Logic\CartLogic();
            $cartLogic->login_cart_handle($this->session_id, $res['result']['user_id']); //用户登录后 需要对购物车 一些操作
        }
        exit(json_encode($res));
    }
    public function reg() {
        $logic = new ShopUsersLogic();
        //验证码检验
        $username = I('post.username', '');
        $password = I('post.password', '');
        $password2 = I('post.password2', '');
      
        $data = $logic->reg($username, $password, $password2);
        if ($data['status'] != 1) {
            exit(json_encode($data));
        }
        session('user', $data['result']);
        $cartLogic = new \Shop\Logic\CartLogic();
        $cartLogic->login_cart_handle($this->session_id, $data['result']['user_id']); //用户登录后 需要对购物车 一些操作
        exit(json_encode($data));
    }
}