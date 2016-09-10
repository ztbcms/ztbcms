<?php

// +----------------------------------------------------------------------
// |  插件升级抽象类
// +----------------------------------------------------------------------

namespace Addons\Util;

abstract class UpgradeBase {

    //错误信息
    protected $error = NULL;

    //实现升级代码
    abstract public function run();

    /**
     * 返回错误信息
     * @return type
     */
    public function getError() {
        return $this->error;
    }

}
