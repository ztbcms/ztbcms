<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhlhuang <zhlhuang888@foxmail.com>
// | 微信绑定模块
// +----------------------------------------------------------------------

namespace Wechat\Controller;

use Common\Controller\Base;

class BaseController extends Base {
    public $wx_user_info = array();
    public $userinfo = array();
    protected function _initialize() {
    }
}