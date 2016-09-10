<?php

// +----------------------------------------------------------------------
// |  插件后台管理
// +----------------------------------------------------------------------

namespace Addon\AddonsName\Controller;

use Addons\Util\Adminaddonbase;

class AdminController extends Adminaddonbase {

    public function index() {
        $this->display();
    }

}
