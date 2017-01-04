<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Sms\Controller;

use Common\Controller\Base;

class ApiController extends Base {

    protected $operator;
    protected $conf;

    protected function _initialize() {
        parent::_initialize();

        //获取默认短信平台，如果没有传入短信平台，则使用次短信平台发送
        $this->operator = M('smsOperator')->where("enable='1'")->find()['tablename'];
    }

    /**
     * 发送短信
     *
     * @param string $template 短信模板ID，从后台配置获取
     * @param string $to       短信接收人，多个接收人号码之间使用英文半角逗号隔开
     * @param array  $param    短信模板变量，数组或者json字符串
     * @param array  $operator 短信平台，不传入时，使用后台配置的默认短信平台
     *
     * @return array operator => 发送平台，
     *               template => 短信模板数据，
     *               recv => 短信接收人
     *               param => 短信模版参数
     *               sendtime => 发送时间
     *               result => 发送结果
     */
    public function sendSms($template, $to, $param = NULL, $operator = NULL) {

        $operator = NULL == $operator ? $this->operator : $operator;

        if (is_array($param)) {
            $param = json_encode($param);
        }

        $model = M('sms_' . $operator);
        $conf = $model->find($template);

        //检查是否存在指定的文件
        $file = dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "Lib" . DIRECTORY_SEPARATOR . ucfirst($operator) . DIRECTORY_SEPARATOR."helper.php";

        if (file_exists($file)) {
            //导入当前模块下Lib目录下的指定文件
            require_once(PROJECT_PATH . "Application/Sms/Lib/" . ucfirst($operator) . "/helper.php");
            $className = "\\Sms\\Lib\\" . ucfirst($operator) . "\\helper";
            $helper = new $className();
            $result = json_encode($helper->send($conf, $to, $param));

            //发送结果存入数据库
            $log = array(
                'operator' => $operator,
                'template' => json_encode($conf),
                'recv'     => $to,
                'param'    => $param,
                'sendtime' => time(),
                'result'   => $result,
            );

            if (M('sms_log')->create($log)) {
                M('sms_log')->add($log);
            }

            return json_decode($result,true);

        }
        else {
            $this->error("所选短信平台暂不支持");
        }


    }
}
