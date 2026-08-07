<?php
namespace app\common\model;


use think\Model;

class UserModel extends Model
{
    protected $name = 'user';
    protected $hidden = ['password', 'verify'];
}