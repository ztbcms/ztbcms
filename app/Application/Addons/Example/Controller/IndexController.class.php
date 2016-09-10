<?php

// +----------------------------------------------------------------------
// |  插件前台管理
// +----------------------------------------------------------------------

namespace Addon\AddonsName\Controller;

use Addons\Util\AddonsBase;

class IndexController extends AddonsBase {

    public function index() {
        $this->display();
    }

}
