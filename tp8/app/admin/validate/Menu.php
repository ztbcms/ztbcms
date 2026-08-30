<?php
/**
 * User: Cycle3
 * Date: 2020/9/23
 * Time: 18:18
 */

namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{

    protected $rule =   [
        'name'  => 'require',
        'app'   => 'require',
        'controller' => 'require',
        'action' => 'require',
    ];

    protected $message  =   [
        'name.require' => '名称不能为空！',
        'app.require'     => '模块不能为空',
        'controller.require'   => '控制器不能为空！',
        'action.require'  => '方法不能为空',
    ];

}