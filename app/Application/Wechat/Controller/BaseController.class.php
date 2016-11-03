<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhlhuang <zhlhuang888@foxmail.com>
// +----------------------------------------------------------------------

namespace Wechat\Controller;

use Common\Controller\Base;

class BaseController extends Base {
    protected $_wx_user_info = array();
    protected $_userinfo = array();
    protected $_config = array();
    protected function _initialize() {
        parent::_initialize();
        $this->_config = cache('Config');
        $userinfo = service("Passport")->getInfo();
        if ($userinfo) {
            //如果用户已经是登录了的。有必要可能要检查是否有绑定微信信息
            $this->_userinfo = service("Passport")->getInfo();
            $this->_wx_user_info = D('Wechat')->find($this->_userinfo['userid']);
        } else {
            if (!I('get.openid')) {
                //没有登录
                if (session('wx_user_info')) {
                    $this->_wx_user_info = session('wx_user_info');
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
                $this->_wx_user_info = session('wx_user_info');
            }
            $is_register = D('Wechat')->where("openid='%s'", $this->_wx_user_info['openid'])->find();
            if (!$is_register) {
                //还没有注册，默认用户名和密码都是openid
                $info['username'] = $this->_wx_user_info['openid'];
                $info['password'] = $this->_wx_user_info['openid'];
                $info['email'] = $this->_wx_user_info['openid'] . '@163.com';
                //微信用户默认自动注册并登陆
                $userid = service("Passport")->userRegister($info['username'], $info['password'], $info['email']);
                $data = array();
                $data['nickname'] = $this->_wx_user_info['nickname'];
                $data['sex'] = $this->_wx_user_info['sex'];
                $data['userpic'] = $this->_wx_user_info['headimgurl'];
                $data['modelid'] = $this->_config['wx_modelid']; //根据自己设计设置会员模型
                $data['regdate'] = time(); //注册的时间
                $data['regip'] = get_client_ip; //注册的ip地址
                $data['checked'] = 1; //默认用户是通过审核
                D('Member')->where("userid='%d'", $userid)->save($data);
                $add_wx_info = $this->_wx_user_info;
                $add_wx_info['userid'] = $userid;
                $res = D('Wechat')->add($add_wx_info);
                if ($res) {
                    //添加成功，自动登录
                    service('Passport')->loginLocal($info['username'], '', $cookieTime ? 86400 * 180 : 86400);
                } else {
                    //添加失败
                    $this->error('用户注册失败');
                }
            } else {
                // 获取绑定微信的member数据
                $member = D('Member')->find($is_register['userid']);
                if ($member) {
                    //如果有会员信息了，就自动登录
                    service('Passport')->loginLocal($member['username'], '', $cookieTime ? 86400 * 180 : 86400);
                    $this->_wx_user_info = $is_register;
                } else {
                    $this->error('数据错误');
                }
            }
            $this->_userinfo = service("Passport")->getInfo();
        }

        $this->assign('member', $this->_userinfo);
        $this->assign('wx_info', $this->_wx_user_info);
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