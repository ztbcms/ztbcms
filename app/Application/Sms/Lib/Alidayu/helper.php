<?php

namespace Sms\Lib\Alidayu;

class helper{
    
    public function send($conf,$to, $param){
        $c = new TopClient;
        $c->appkey = $conf['appkey'];
        $c->secretKey = $conf['secret'];
        $req = new AlibabaAliqinFcSmsNumSendRequest;
        
        if (!empty($conf['extend'])){
            $req->setExtend($conf['extend']);
        }
        $req->setSmsType($conf['type']);
        $req->setSmsFreeSignName($conf['sign']);
        if(!empty($param)){
            $req->setSmsParam($param);
        }
        $req->setRecNum($to);
        $req->setSmsTemplateCode($conf['template']);

        return $c->execute($req);
    }
}