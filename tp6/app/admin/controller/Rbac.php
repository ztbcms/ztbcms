<?php
/**
 * Created by PhpStorm.
 * User: Cycle3
 * Date: 2020/9/23
 * Time: 17:28
 */

namespace app\admin\controller;

use app\admin\model\RoleModel;
use app\common\controller\AdminController;

class Rbac extends AdminController
{

    /**
     * 获取角色列表 api
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getrolemanage(){
        $RoleModel = new RoleModel();
        //如果是超级管理员，显示所有角色。如果非超级管理员，只显示下级角色
        if(!$this->is_administrator){
            $data = $RoleModel->where('parentid='.$this->user->role_id)->select();
        }else{
            $data = $RoleModel->select();
        }
        return json(self::createReturn(true, $data, '获取成功'));
    }

}
