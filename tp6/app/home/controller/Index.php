<?php

namespace app\home\controller;

use app\BaseController;

class Index extends BaseController
{
    public function index()
    {
        return view('index');
    }

}
