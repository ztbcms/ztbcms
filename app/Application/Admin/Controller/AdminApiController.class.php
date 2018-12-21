<?php
/**
 * User: jayinton
 * Date: 2018/12/21
 * Time: 16:17
 */

namespace Admin\Controller;


use Admin\Service\User;
use Common\Controller\AdminBase;

class AdminApiController extends AdminBase
{
    protected function _initialize()
    {
        $this->supportCros();
        User::getInstance()->login('admin', 'admin');

        parent::_initialize();
    }

    function supportCros(){
        //http 预检响应
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: *');
            header('Access-Control-Allow-Headers: *,'); //不能设置为 *，必须指定
            header('Access-Control-Max-Age: 86400'); // cache for 1 day

            exit();
        }

        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *'); //不能设置为 *，必须指定
    }


    /**
     * 获取后台用户的菜单
     */
    public function getMenuList(){

        $menuList = D("Admin/Menu")->getAdminUserMenuTree(1);
        $this->ajaxReturn(self::createReturn(true, $menuList));
    }

}