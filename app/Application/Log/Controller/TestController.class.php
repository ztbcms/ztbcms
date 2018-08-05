<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Log\Controller;


use Common\Controller\AdminBase;
use Log\Service\LogService;

class TestController extends AdminBase{

    public function test(){
        LogService::log(__CLASS__.'::'.__FUNCTION__,'测试数据...');
        LogService::log('tst','ffff.');
    }

}