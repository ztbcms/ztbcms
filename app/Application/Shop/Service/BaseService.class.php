<?php
namespace Shop\Service;

class BaseService {

    /**
     * 存放错误信息
     * @var string
     */
    public $err_msg='';

    /**
     * 设置错误信息
     * @param $err_msg
     */
    public function set_err_msg($err_msg){
        $this->err_msg=$err_msg;
    }

    /**
     * 返回最近的错误信息
     * @return string
     */
    public function get_err_msg(){
        return $this->err_msg;
    }
}