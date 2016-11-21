<?php
namespace Shop\Controller;
use Common\Controller\Base;

class BaseController extends Base {
    public $session_id;
    /*
     * 初始化操作
     */
    public function _initialize() {
        $this->session_id = session_id(); // 当前的 session_id
        define('SESSION_ID', $this->session_id); //将当前的session_id保存为常量，供其它方法调用
    }
}