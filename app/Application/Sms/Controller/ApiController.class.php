<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Sms\Controller;

use Common\Controller\Base;


// //云之讯
// use Sms\Lib\Ucpaas\Ucpaas;

class ApiController extends Base {

    protected $operator;
    protected $conf;
    
    protected function _initialize() {
        parent::_initialize();

        //获取正在使用的短信平台
        $this->operator = M('smsOperator')->where("enable='1'")->find();
        //获取短信配置
        $this->conf = M("sms" . ucfirst($this->operator['tablename']))->select()[0];

        if (empty($this->conf)) {
            $this->error("短信模块缺失配置，请先到后台设置！");
            return false;
        }

    }

    /**
    * 发送短信
    *
    * @param string $to      短信接收人，多个接收人号码之间使用英文半角逗号隔开
    * @param array  $param  短信模板变量，必须为JSON字符串，非必填字段
    * @return array {result => 短信发送结果，id => 数据库日志ID，msg => 发生错误时，错误信息}
    */
    public function sendSms(){
        
        $to = I('get.to');
        $param = I('get.param');

        //检查是否存在指定的文件
        $file = dirname(dirname(__FILE__)) . "\\Lib\\" . ucfirst($this->operator['tablename']). "\\helper.php";

        if (file_exists($file)){
            //导入当前模块下Lib目录下的指定文件
            require_once (PROJECT_PATH . "application/Sms/Lib/" . ucfirst($this->operator['tablename']) . "/helper.php");
            $className = "\\Sms\\Lib\\" . ucfirst($this->operator['tablename']) . "\\helper";
            $helper = new $className($this->conf);
            $data = $helper->send($to,$param);

            //发送结果存入数据库
            if (M('sms')->create($data['log'])){
                M('sms')->add($data['log']);
                $sms = M('sms')->where(array("recv" => $to))->find();
            }

            //返回此次操作的数据库记录ID
            $data['resp']['dbid'] = $sms['id'];

            return $data['resp'];

        }else{
            $this->error("所选短信平台暂不支持");
        }

         
    }
}