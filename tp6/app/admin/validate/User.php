<?php
/**
 * User: Cycle3
 * Date: 2020/9/23
 * Time: 18:18
 */

namespace app\admin\validate;

use think\Validate;

class User extends Validate
{


    protected $rule =   [
        'username'  => 'require|max:25',
        'nickname'   => 'require|max:25',
        'role_id' => 'require',
        'password' => 'require',
        'pwdconfirm' => 'require',
        'email' => 'require|email',
        'status' => 'require',
    ];

    protected $message  =   [
        'username.require' => '用户名不能为空！',
        'username.max'     => '用户名最多不能超过25个字符',
        'nickname.require'   => '真实姓名不能为空！',
        'nickname.max'  => '真实姓名最多不能超过25个字符',
        'role_id'        => '帐号所属角色不能为空',
        'password' => '密码不能为空',
        'pwdconfirm' => '密码不能为空',
        'email' => '邮箱地址有误！',
        'status' => '状态错误，状态只能是1或者0！'
    ];

    // 编辑时验证
    public function sceneEdit()
    {
        return $this->only(['username','nickname','role_id','email','status']);
    }

}