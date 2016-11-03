<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhlhuang <zhlhuang888@foxmail.com>
// | 微信绑定模块
// +----------------------------------------------------------------------

namespace Wechat\Controller;

use Common\Controller\Base;

class WxBaseController extends Base {
    public $wx_user_info = array();
    protected function _initialize() {
        parent::_initialize();
        $this->_config = cache('Config');
        //检测是否微信浏览器
        $is_wechat = strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger');
        if ($is_wechat) {
            if (!I('get.openid')) {
                //没有登录
                if (session('wx_user_info')) {
                    $this->wx_user_info = session('wx_user_info');
                } else {
                    //没有微信资料
                    $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    $param = "url=" . urlencode($url);
                    $oauthUrl = 'http://open.zhutibang.cn/oauth2/' . $this->_config['open_alias'] . '.html?' . $param;
                    redirect($this->signEncode($oauthUrl, $this->_config['open_secret_key']));
                }
            } else {
                //签名验证
                $this->signDecode(get_url(), $this->_config['open_secret_key']);
                $wx_user_info = I('get.');
                session('wx_user_info', $wx_user_info);
                $this->wx_user_info = session('wx_user_info');
            }
            //检查是否有会员登录，有会员登录自动绑定会员信息
             $userinfo = service("Passport")->getInfo();
             if($userinfo){
                 //已经是登录会员，检测是否有绑定微信了。
                 $is_binding=D('Wechat')->where("userid='%d'",$userinfo['userid'])->find();
                 if($is_binding['openid']!=$this->wx_user_info['openid']){
                     //如果不是绑定的原有微信，则取消该绑定，绑定现有的微信
                     D('Wechat')->where("id='%d'",$is_binding['id'])->save(array('userid'=>0));
                     $this->wx_user_info['userid']=$userinfo['userid'];
                 }
             }

            //最后的结果都是  $this->_wx_user_info 有微信的信息
            $is_exist=D('Wechat')->where("openid='%s'", $this->wx_user_info['openid'])->find();
            if ($is_exist) {
                D('Wechat')->where("id='%d'",$is_exist['id'])->save($this->wx_user_info);
            } else {
                D('Wechat')->add($this->wx_user_info);
            }
        } else {

        }
    }
    /**
     * 生成认证签名
     */
    public function signEncode($url, $secret_key) {
        $url_arr = explode('?', $url);
        if (empty($url_arr[1])) {
            return $this->error('参数错误');
        } else {
            $param_str = $url_arr[1] . "&time=" . time(); //加上签名的时间戳
            $sign = md5(urlencode(trim($param_str)) . $secret_key); //生成签名
            return $url_arr[0] . "?" . $param_str . "&sign=" . $sign;
        }
    }

    /**
     * 签名认证
     * @param url 带有签名的url
     * @param secret_key 签名私钥
     */
    public function signDecode($url, $secret_key) {
        $url_arr = explode('?', $url);
        if (empty($url_arr[1])) {
            return $this->error('参数错误');
        } else {
            $param_sign = explode('&sign=', $url_arr[1]);
            $param = $param_sign[0]; //对于获取到的参数浏览器可能会decode
            if (empty($param_sign[1])) {
                return $this->error('签名失败');
            } else {
                $sign = $param_sign[1];
            }
            if (md5(urlencode(trim($param)) . $secret_key) == $sign) {
                //签名成功，可以继续操作
                return true;
            } else {
                return $this->error('签名失败');
            }
        }
    }
}