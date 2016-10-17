<?php

// +----------------------------------------------------------------------
// | Copyright (c) Zhutibang.Inc 2016 http://zhutibang.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: Jayin Ton <tonjayin@gmail.com>
// +----------------------------------------------------------------------

namespace Sms\Controller;


use Common\Controller\AdminBase;

class IndexController extends AdminBase{

    //初始化
	protected function _initialize() {
		parent::_initialize();
    }

    public function operator(){
        $this->assign("operators",M('smsOperator')->select());
        $this->display();
    }

    public function addOperator(){
        if(IS_POST){
            $name = trim(I('post.name'));
            $tablename = trim(I('post.tablename'));
            $remark = trim(I('post.remark'));


            $data['name'] = $name;
            $data['tablename'] = $tablename;
            $data['remark'] = $remark;
            $data['enable'] = 0;

            $operatorModel = M('smsOperator');

            if ($operatorModel->create($data)){
            
                $operatorModel->add($data);

                //新建短信平台配置表
                $sql = "CREATE TABLE `ztb_sms_$tablename` (`id` int(11) NOT NULL COMMENT 'ID' AUTO_INCREMENT,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                $Model = new \Think\Model();
                if (false === $Model->execute($sql)){
                    //删除配置
                    $operatorModel->where("name='$name'")->delete();
                    $this->error("创建短信平台失败");
                }else{
                    $this->success("创建平台成功，请前往平台设置字段。");
                }
            }else{
                $this->error($operatorModel->getError());
            }

            
        }else{
            $this->display();
        }
    }

    public function delOperator(){

        $tablename = I('get.operator');

        //删除平台表
        $sql = "DROP TABLE IF EXISTS `ztb_sms_$tablename`;";
        $Model = new \Think\Model();
        if (false === $Model->execute($sql)){
            $this->error("删除短信平台失败");
        }else{
            //删除配置
            M('smsOperator')->where("tablename='$tablename'")->delete();
            $this->success("删除平台成功");
        }
    }

    //短信配置设置
    public function choose(){

        $operator = trim(I('get.operator'));

        //取消所有平台的选中
        $data['enable'] = 0;
        M("smsOperator")->where(true)->save($data);

        //启用所选平台
        $data['enable'] = 1;
        M('smsOperator')->where(array("tablename" => $operator))->save($data);

        $this->success("平台变更成功，使用前请确认平台配置");
    }

    public function model(){
        //获取表字段
        $tablename = ucfirst(I('get.operator'));
        $keys = M('sms'. $tablename)->getDbFields();
        foreach ($keys as $k => $v){
            $fields[$v]['value'] = "";
        }
        unset($fields['id']);
        
        $fields = static::getComments($tablename,$fields);

        $this->assign("operator", M('smsOperator')->where("tablename='%s'", $tablename)->find());
        $this->assign("fields",$fields);
        $this->display();
    }

    public function addField(){
        if(IS_POST){

            $tablename = trim(I("post.operator"));
            $fieldname = trim(I('post.name'));
            $default = trim(I('post.default'));
            $comment = trim(I('post.comment'));

            $sql = "ALTER TABLE `ztb_sms_$tablename` ADD `$fieldname` VARCHAR(255) DEFAULT '$default' COMMENT '$comment'";
            $Model = new \Think\Model();
            if (false === $Model->execute($sql)){
                //删除配置
                $operatorModel->where("name=$name")->delete();
                $this->error("字段新增失败");
            }
            $this->success("字段新增成功");
            
        }else{
            $tablename = ucfirst(I("get.operator"));
            $this->assign("operator", M('smsOperator')->where("tablename='%s'", $tablename)->find());
            $this->display();
        }
    }

    public function delField(){

        $tablename = trim(I('get.operator'));
        $column = trim(I('get.key'));
        
        $sql = "alter table `ztb_sms_$tablename` drop column $column";
        $Model = new \Think\Model();
        if (false === $Model->execute($sql)){
            $this->error("字段删除失败");
        }
        $this->success("字段删除成功");
    }

    public function conf(){
        if(IS_POST){

            $tableName = ucfirst(trim(I('post.operator')));

            foreach($_POST as $k => $v){
                $_POST[$k] = trim($v);
            }

            $table = M('sms'. $tableName);
            $conf = $table->select();

            if (empty($conf)){
                if ($table->create($_POST)!==FALSE){
                    $table->add($_POST);
                    $this->success("配置成功");
                }else{
                    $this->error("配置失败");
                }
            }else{
                $table->where("id='%d'", $conf[0]['id'])->save($_POST);
                $this->success("配置成功");
            }

        }else{
            //获取表字段
            $tablename = ucfirst(I('get.operator'));
            $keys = M('sms'. $tablename)->getDbFields();
            foreach ($keys as $k => $v){
                $fields[$v]['value'] = "";
            }
            unset($fields['id']);
            
            $fields = static::getComments($tablename,$fields);

            $this->assign("operator", M('smsOperator')->where("tablename='%s'", $tablename)->find());
            $this->assign("fields",$fields);
            $this->display();
        }
    }

    public function getComments($tablename, $fields){
        $tablename = C('DB_PREFIX') . "sms_" .$tablename;
        
        $Model = new \Think\Model();        
        $fullFields = $Model->query("show full fields from $tablename");
        
        $fieldsValue = $Model->query("select * from $tablename")[0];

        foreach($fullFields as $k => $v){
            $fieldname = $v['field'];
            if (isset($fields[$fieldname])){
                $fields[$v['field']]['comment'] = $v['comment'];
            }
            if (!empty($fieldsValue)){
                $fields[$fieldname]['value'] = $fieldsValue[$v['field']];
            }
        }

        unset($fields['id']);

        return $fields;
    }

}