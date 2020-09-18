<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;

use Admin\Service\User;
use app\BaseController;
use think\facade\View;

class Login extends BaseController
{
    /**
     * 登录页
     * @return string
     */
    function index(){
        return View::fetch('index');
    }

    /**
     * 登录操作
     */
    function doLogin(){

    }

    /**
     * 登出操作
     */
    function doLogout() {

    }

}