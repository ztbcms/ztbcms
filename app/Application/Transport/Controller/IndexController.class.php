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

    private $db;

    protected function _initialize() {
        parent::_initialize();

        $this->db = D('Transport/TransportTask');
    }


    /**
     * 任务列表页
     */
    function index(){
        $data = $this->db->select();
        $this->assign('data', $data);

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
        $data = I('post.');


        if($this->db->create($data)){
            $this->db->add();
            $this->success('创建成功');
        }else{
            $this->error($this->db->getDbError());
        }

    }

    /**
     * 删除任务
     */
    function task_delete(){
        $this->db->where(['id' => I('get.id')])->delete();
        $this->success('操作成功');
    }



    /**
     * 编辑任务页
     */
    function task_edit_index(){
        $task_id = I('get.id');
        $task = $this->db->where(['id' => $task_id])->find();
        $this->assign($task);

        $task_conditions = M('TransportCondition')->where(['task_id' => $task_id])->select();
        $this->assign('task_conditions', $task_conditions);


        $task_fields = M('TransportField')->where(['task_id' => $task_id])->select();
        $this->assign('task_fields', $task_fields);

        $this->display();
    }

    /**
     * 编辑任务
     */
    function task_edit(){
        $task_id = I('task_id');
        $data = I('post.');
        $this->db->where(['id' => $task_id])->save($data);

        $this->success('操作成功');
    }

    /**
     * 更新筛选条件信息
     */
    function task_update_condition(){
        $task_id = I('post.task_id');
        $filter = I('post.condition_filter');
        $operator = I('post.condition_operator');
        $value = I('post.condition_value');

        //先清空后加入
        M('TransportCondition')->where(['task_id' => $task_id])->delete();

        $batch_data = [];
        foreach ($filter as $index => $f){
            $batch_data[] = [
                'task_id' => $task_id,
                'filter' => $filter[$index],
                'operator' => $operator[$index],
                'value' => $value[$index],
            ];
        }

        foreach ($batch_data as $index => $data){
            M('TransportCondition')->add($data);
        }

        $this->success('操作成功');
    }

    /**
     * 更新设置字段映射
     */
    function task_update_field(){
        $task_id = I('post.task_id');
        $field_name = I('post.field_field_name');
        $export_name = I('post.field_export_name');
        $filter = I('post.field_filter');

        //先清空后加入
        M('TransportField')->where(['task_id' => $task_id])->delete();

        $batch_data = [];
        foreach ($field_name as $index => $f){
            $batch_data[] = [
                'task_id' => $task_id,
                'field_name' => $field_name[$index],
                'export_name' => $export_name[$index],
                'filter' => $filter[$index],
            ];
        }

        foreach ($batch_data as $index => $data){
            M('TransportField')->add($data);
        }

        $this->success('操作成功');
    }

    /**
     * 执行任务预览页
     */
    function task_exec_index(){
        $task = $this->db->where(['id' => I('get.id')])->find();
        $this->assign($task);
        $this->display();
    }

    /**
     * 执行任务
     */
    function task_exec(){

    }

    /**
     * 任务执行日志
     */
    function task_logs(){
        $this->display();
    }




}