<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2020-08-26
 * Time: 17:41.
 */

namespace app\common\model;


use think\Model;

class UserModel extends Model
{
    protected $name = 'user';
    protected $hidden = ['password', 'verify'];
}