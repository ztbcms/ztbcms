<?php

namespace Sms\Lib\Alidayu;

class helper{

    protected $conf;

    public function __construct($conf){
        $this->conf = $conf;
    }

    public function send($to, $param){
        $c = new TopClient;
        $c->appkey = $this->conf['appkey'];
        $c->secretKey = $this->conf['secret'];
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        
        if (!empty($this->conf['extend'])){
            $req->setExtend($this->conf['extend']);
        }
        $req->setSmsType($this->conf['type']);
        $req->setSmsFreeSignName($this->conf['sign']);
        if(!empty(param)){
            $req->setSmsParam($param);
        }
        $req->setRecNum($to);
        $req->setSmsTemplateCode($this->conf['template']);
        
        $resp = $c->execute($req);

        $data['resp'] = $resp;
        
        //整理发送结果
        $data['log'] = array(
            "operator" => $this->operator['tablename'],
            "template" => $this->conf['template'],
            "recv" => $to,
            'param' => json_encode($param),
            'result' => json_encode($resp)
        );

        return $data;
    }
}