<?php
/**
 * User: jayinton
 * Date: 2020/9/18
 */

namespace app\admin\controller;


use app\admin\model\RoleModel;

class Test
{
    function test(){
        $roleModel = new RoleModel();
        $res =$roleModel->getArrchildid(1);

        var_dump($res);
//        return json($res);
    }
}