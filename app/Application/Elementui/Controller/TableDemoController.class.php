<?php

namespace Elementui\Controller;

use Common\Controller\AdminBase;

class TableDemoController extends AdminBase
{
    public function table1()
    {
        $this->display();
    }

    public function table2()
    {
        $this->display();
    }

    public function table3()
    {
        $this->display();
    }

    public function table4()
    {
        $this->display();
    }
    public function select()
    {
        $this->display();
    }

    public function detail()
    {
        $this->display();
    }

    public function getDetail()
    {
        $data['content'] = "内容";
        $data['user_name'] = "用户名";
        $data['images'] = ["/statics/images/logo.gif", "/statics/images/logo.gif"];
        $data['reply_status'] = 0;
        $data['reply_content'] = '';
        $this->ajaxReturn(self::createReturn(true, $data));
    }
}