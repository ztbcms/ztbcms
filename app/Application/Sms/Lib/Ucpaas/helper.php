<?php

namespace Sms\Lib\Ucpaas;

class helper{

    protected $conf;

    public function __construct($conf){
        $this->conf = $conf;
    }

    public function send($to, $param){
        
        $ucpass = new Ucpaas($this->conf);
        $resp = $ucpass->templateSMS($to,$param);
        
        $data['resp'] = $resp;
        
        //整理发送结果
        $data['log'] = array(
            "operator" => $this->operator['tablename'],
            "template" => $this->conf['templateid'],
            "recv" => $to,
            'param' => json_encode($param),
            'result' => json_encode($resp)
        );

        return $data;
    }
}