<?php
namespace Shop\Controller;
use Common\Controller\Base;

class BaseController extends Base {
    public $session_id;
    public $userid=0;
    public $userinfo=null;
    public $shop_user=null;
    /*
     * 初始化操作
     */
    public function _initialize() {
        $this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID', $this->session_id); //将当前的session_id保存为常量，供其它方法调用
        $userinfo = service("Passport")->getInfo();
        $shop_user = M('ShopUsers')->find($userinfo['userid']);
        if ($userinfo && $shop_user) {
            $this->userid=$userinfo['userid'];
            $this->userinfo=$userinfo;
            $this->shop_user=$shop_user;
        }else if(ACTION_NAME=='login'||ACTION_NAME=='reg'){
            //不需要登录的action
        } else {
            exit(json_encode(array('status' => -500, 'msg' => '没有登录')));
        }
    }
}