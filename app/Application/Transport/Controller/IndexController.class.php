<?php

// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Transport\Controller;


use Common\Controller\AdminBase;
use Transport\Core\Export;
use Transport\Core\ExportField;
use Transport\Core\Import;
use Transport\Model\TransportTaskModel;

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
     * 执行任务
     */
    function task_exec(){
        //设置脚本最大执行时间
		set_time_limit(0);

        $task_log_id = I('task_log_id');

        $task_log = M('TransportTaskLog')->where(['id' => $task_log_id])->find();
        $task = M('TransportTask')->where(['id' => $task_log['task_id']])->find();

        if($task['type'] == TransportTaskModel::TYPE_EXPORT){
            //导出任务处理

            $export = new Export();
            $filename = empty($task_log['filename'])? $task['title'].date('YmdHis', time()) : $task_log['filename'];
            $export->setFilename($filename); //导出文件名
            $export->setModel($task['model']); //导出模型

            //筛选条件
            $task_conditions = M('TransportCondition')->where(['task_id' => $task['id']])->select();
            $where = [];
            foreach ($task_conditions as $index => $condition){

                if(!empty($condition)){
                    $filter = trim($condition['filter']);
                    $operator = trim($condition['operator']);
                    $value = trim($condition['value']);

                    if(empty($where[$filter])){
                        $where[$filter] = [];
                    }

                    if(strtolower($operator) == 'like'){
                        $new_condition = array($operator, '%' . $value . '%');
                    }else{
                        $new_condition = array($operator, $value);
                    }
                    $where[$filter][] = $new_condition;
                }
            }
            $export->setCondition($where);

            //字段映射
            $fields = [];
            $task_fields = M('TransportField')->where(['task_id' => $task['id']])->select();
            foreach ($task_fields as $index => $field){
                $fields[] = new ExportField($field['field_name'], $field['export_name'], $field['filter']);
            }
            $export->setFields($fields);

            //取消下面两行注释,即可预览导出结果
            //$table = $export->exportTable();
            //echo $table; exit();
            $export->exportXls();
        }else{
            //导入
            $import = new Import();

            $import->setModel($task['model']);

            //字段映射
            $fields = [];
            $task_fields = M('TransportField')->where(['task_id' => $task['id']])->select();
            foreach ($task_fields as $index => $field){

                $fields[] = new ExportField($field['field_name'], $field['export_name'], $field['filter']);
            }
            $import->setFields($fields);

            $import->setFilename(getcwd() . $task_log['filename']);

//            $import->exportTable();
            //开始导入
            $import->import();
            $this->success('导入成功');
        }
    }

    /**
     * 任务执行日志
     */
    function task_logs(){
        $default_limit = 20;

        $data = M('TransportTaskLog')->page(I('page', 1))->limit(I('limit', $default_limit))->order('inputtime DESC')->select();
        $this->assign('data', $data);

        $sum = M('TransportTaskLog')->count();
        $page = $this->page( $sum, $default_limit);

        $this->assign('Page', $page->show());
        $this->display();
    }

    /**
     * 创建任务执行日志
     */
    function task_log_create(){
        $data = I('post.');

        $data['inputtime'] = time();
        $id = M('TransportTaskLog')->data($data)->add();
        if($id){
            //跳转
            $this->redirect('task_logs');
            $this->success('创建任务执行日志成功', U('Transport/Index/task_logs'));

        }else{
            $this->error('创建任务执行日志失败');
        }

    }




}