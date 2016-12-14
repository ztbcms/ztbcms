<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Controller;


use Common\Controller\AdminBase;

/**
 * Class IndexController
 *
 * @package Transport\Controller
 */
class IndexController extends AdminBase  {


    /**
     * 任务列表页
     */
    function index(){
        $this->display();
    }

    /**
     * 任务详情
     */
    function task_detail(){
        $this->display();
    }

    /**
     * 创建任务页
     */
    function task_create_index(){
        $this->display();
    }

    /**
     * 创建任务
     */
    function task_create(){

    }

    /**
     * 编辑任务页
     */
    function task_edit_index(){
        $this->display();
    }

    /**
     * 编辑任务
     */
    function task_edit(){

    }

    /**
     * 任务执行日志
     */
    function task_logs(){
        $this->display();
    }




}