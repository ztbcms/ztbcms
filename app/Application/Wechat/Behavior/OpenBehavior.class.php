<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhlhuang <zhlhuang888@foxmail.com>
// | 纷享呗操作
// +----------------------------------------------------------------------
namespace Wechat\Behavior;
class OpenBehavior {
    private $domain='http://open.zhutibang.cn';
     /**
     * 通过img_id 获取人脸识别信息
     * @param img_id string 图片id face++唯一图片id
     */
    public function get_face_detect_info($img_id) {
        $time = time(); //当前时间戳
        //注意配置的模块，如果配置在Wechat/Conf中，其他模块调用可能出问题
        $sign = $this->sign_encode(C('open.appid') . $time . C('open.secret_key'));
        $api_url = $this->domain."/api/face_api/get_detect_info/app_id/" . C('open.appid') . ".html?time={$time}&sign={$sign}";
        $send_data = array(
            'img_id' => $img_id,
        );
        $res = $this->post($api_url, $send_data);
        return json_decode($res, 1);
    }

    /**
     * 人脸识别中，获取基本的脸部信息
     * @param  img_url string 需要识别的图片url
     */
    public function get_face_detect($img_url) {
        $time = time(); //当前时间戳
        //注意配置的模块，如果配置在Wechat/Conf中，其他模块调用可能出问题
        $sign = $this->sign_encode(C('open.appid') . $time . C('open.secret_key'));
        $api_url = $this->domain."/api/face_api/detect/app_id/" . C('open.appid') . ".html?time={$time}&sign={$sign}";
        $send_data = array(
            'img_url' => $img_url,
        );
        $res = $this->post($api_url, $send_data);
        return json_decode($res, 1);
    }
    /**
    * 发送模板消息
    *@param openid string 接收用户的openid
    *@param template_id  string 发送模板消息的id
    *@param data  json  模板需要发送的key和value
    *@param url string 点击模板消息的跳转链接，不设置则不跳转
    *@param topcolo   颜色16进制
    */
    public function send_template($openid,$template_id,$data,$url=null,$topcolor='#f7f7f7'){
        $time=time();//当前时间戳
        //注意配置的模块，如果配置在Wechat/Conf中，其他模块调用可能出问题
        $sign=$this->sign_encode(C('open.appid').$time.C('open.secret_key'));
        $api_url=$this->domain."/api/template_api/send_template/app_id/".C('open.appid').".html?time={$time}&sign={$sign}";
        $send_data=array(
            'touser'=>$openid,
            'template_id'=>$template_id,
            'url'=>$url,
            'topcolor'=>$topcolor,
            'data'=>json_encode($data)
        );
        $res=$this->post($api_url,$send_data);
        return json_decode($res,1);
    }
    /**
    * api调用签名
    * @param appid string open平台的appid
    * @param time int 当前时间戳
    * @param secret_key string open平台的私钥
    */
    public function sign_encode($appid,$time,$secret_key){
        return md5($appid.$time.$secret_key);
    }
    /**
     * get method
     */
    function get($url, $param = array()) {
        if (!is_array($param)) {
            return false;
        }
        $p = '';
        foreach($param as $key => $value) {
            $p = $p.$key.'='.$value.'&';
        }
        if (preg_match('/\?[\d\D]+/', $url)) {
            //matched ?c
            $p = '&'.$p;
        } else if (preg_match('/\?$/', $url)) {
            //matched ?$
            $p = $p;
        } else {
            $p = '?'.$p;
        }
        $p = preg_replace('/&$/', '', $p);
        $url = $url.$p;
        //echo $url;
        $httph = curl_init($url);
        curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($httph, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($httph, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($httph, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($httph, CURLOPT_HEADE, 0);
        $rst = curl_exec($httph);
        curl_close($httph);
        return $rst;
    }
    /**
     * post method
     */
    function post($url, $param = array()) {
        if (empty($param)) {
            return false;
        }
        $httph = curl_init($url);
        curl_setopt($httph, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($httph, CURLOPT_SSL_VERIFYHOST, 1);
        curl_setopt($httph, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($httph, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)");
        curl_setopt($httph, CURLOPT_POST, 1); //设置为POST方式
        curl_setopt($httph, CURLOPT_POSTFIELDS, $param);
        curl_setopt($httph, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($httph, CURLOPT_HEADER, 0); //这里我们已经忽略http报文头的显示
        $rst = curl_exec($httph);
        curl_close($httph);
        return $rst;
    }
}