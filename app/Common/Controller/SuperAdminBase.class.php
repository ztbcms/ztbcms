<?php

/**
 * author: Jayin <tonjayin@gmail.com>
 */

namespace Common\Controller;

/**
 * 超级管理员基类
 */
class SuperAdminBase extends AdminBase {


    protected function _initialize() {
        parent::_initialize();

        if($this->userInfo['role_id'] != 1){
            //没有操作权限
            $this->error('非超级管理员，无法操作！');
        }
    }

}