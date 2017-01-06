<?php
namespace Shop\Controller;

use Shop\Logic\ShopUsersLogic;
use Shop\Service\UserService;

class UserApiController extends BaseController {
    public function _initialize() {
        parent::_initialize();

    }
    /**
     * 获取登录用户信息
     */
    public function index() {
        $userinfo = service("Passport")->getInfo();
        $shop_user = M('ShopUsers')->find($userinfo['userid']);
        if ($userinfo && $shop_user) {
            $this->success($shop_user);
        } else {
            $this->error('没有登录', -500);
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

        $user_service = new UserService();
        $res = $user_service->login($username, $password);
        if ($res) {
            //商城一系列操作登录
            session('user', $res);
            setcookie('user_id', $res['user_id'], null, '/');
            $nickname = empty($res['nickname']) ? $username : $res['nickname'];
            setcookie('uname', urlencode($nickname), null, '/');
            setcookie('cn', 0, time() - 3600, '/');
            unset($res['password']);
            $this->success($res);
        } else {
            $this->error($user_service->get_err_msg());
        }
    }

    public function register() {
        //验证码检验
        $username = I('post.username', '');
        $password = I('post.password', '');
        $password2 = I('post.password2', '');
        $user_service = new UserService();
        $res = $user_service->register($username, $password, $password2);
        if ($res) {
            session('user', $res);
            $this->success($res);
        } else {
            $this->error($user_service->get_err_msg());
        }
    }

    /**
     * 用户地址列表
     */
    public function address_list() {
        $address_lists = M('UserAddress')->where(array('userid' => $this->userid))->select();
        $list = [];
        foreach ($address_lists as $key => $value) {
            $value['province_name'] = getRegionName($value['province'], 1);
            $value['city_name'] = getRegionName($value['city'], 2);
            $value['district_name'] = getRegionName($value['district'], 3);
            $list[] = $value;
        }
        $this->success($list);
    }

    /**
     * 添加地址
     */
    public function add_address() {
        if (IS_POST) {
            $logic = new ShopUsersLogic();
            $data = $logic->add_address($this->userid, 0, I('post.'));
            $this->success($data);
        } else {
            $this->error('请求方法错误');
        }
    }

    /**
     * 编辑地址
     */
    public function edit_address() {
        if (IS_POST) {
            $id = I('post.id');
            if (!$id) {
                exit(json_encode(array('status' => -1, 'msg' => '参数错误')));
            }
            $post = I('post.');
            unset($post['id']);
            $logic = new ShopUsersLogic();
            $data = $logic->add_address($this->userid, $id, $post);
            $this->success($data);
        }else{
            $this->error('请求方法错误');
        }
    }

    /**
     * 设置默认收货地址
     */
    public function set_default() {
        $id = I('post.id');
        M('UserAddress')->where(array('userid' => $this->userid))->save(array('is_default' => 0));
        $row = M('UserAddress')->where(array(
            'userid' => $this->userid,
            'address_id' => $id
        ))->save(array('is_default' => 1));
        if (!$row) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }

    /**
     * 地址删除
     */
    public function del_address() {
        $id = I('post.id');
        $address = M('UserAddress')->where("address_id = $id")->find();
        $row = M('UserAddress')->where(array('userid' => $this->userid, 'address_id' => $id))->delete();
        // 如果删除的是默认收货地址 则要把第一个地址设置为默认收货地址
        if ($address['is_default'] == 1) {
            $address2 = M('UserAddress')->where("userid = {$this->userid}")->find();
            $address2 && M('UserAddress')->where("address_id = {$address2['address_id']}")->save(array('is_default' => 1));
        }
        if (!$row) {
            $this->error('操作失败');
        } else {
            $this->success('操作成功');
        }
    }
}