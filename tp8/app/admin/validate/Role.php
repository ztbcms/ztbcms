<?php
/**
 * User: Cycle3
 * Date: 2020/9/24
 */

namespace app\admin\validate;

use think\Validate;

class Role extends Validate
{

    protected $rule = [
        'name'   => 'require|max:25',
        'status' => 'require|number|between:0,1'

    ];

    protected $message = [
        'name.require'   => '角色名称不能为空！',
        'name.max'       => '角色名称最多不能超过25个字符',
        'status.require' => '状态不能为空！',
        'status.number'  => '状态只能为数字',
        'status.between' => '状态只能是1或者0',
    ];


}