<?php

namespace app\home\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return 'think';
    }

    public function hello($name = 'ThinkPHP6')
    {
        return 'hello,' . $name;
    }
}
