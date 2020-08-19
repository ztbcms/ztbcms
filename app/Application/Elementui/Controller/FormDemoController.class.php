<?php

namespace Elementui\Controller;

use Common\Controller\AdminBase;

class FormDemoController extends AdminBase
{
    // 表单生成
    function form_generator(){
        $this->display();
    }

    public function form1()
    {
        $this->display();
    }

    public function form2()
    {
        $this->display();
    }

    public function form3()
    {
        $this->display();
    }
}