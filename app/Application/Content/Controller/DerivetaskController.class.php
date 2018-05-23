<?php

// +----------------------------------------------------------------------
// | 下载相关
// +----------------------------------------------------------------------

namespace Content\Controller;

use Common\Controller\AdminBase;

class DerivetaskController extends AdminBase {
    //初始化
    protected function _initialize() {
        parent::_initialize();
        $this->modelfield = D("Content/ModelField");
    }

    //数据库查询
    public function dictionaryField(){
        $modelid = I('modelid','0','intval');
        if(!empty($modelid)){
            $where['modelid'] = $modelid;
        }
        $where['issystem'] = 1;
        $where['disabled'] = 0;
        $data = $this->modelfield->where($where)->field('modelid,field,name,formtype,tips')->select();
        foreach ($data as $key => $val){
            if(empty($val['tips'])){
                $data[$key]['tips']  = '/';
            }
            $data[$key]['tablename'] = M("Model")->where(array("modelid" => $val['modelid']))->field('name,tablename,modelid')->find();
        }

        $result= array();
        foreach ($data as $key => $info) {
            $result[$info['tablename']['modelid']][] = $info;
        }
        $this->assign("data", $result);
        $this->display();
    }


    //根据表查询数据库
    public function dictionaryTable(){
        if(preg_match('/[\x{4e00}-\x{9fa5}]/u', I('table'))>0){
            $this->error('表名不能有字符串');
        }
        if(!empty(I('table'))){
            $table = 'ztb_'.trim(I('table'));
            $isTable =M()->query("SHOW TABLES LIKE '{$table}'");
            if(empty($isTable)) $this->error('您输入的表不存在');

            $sql = "select a.COLUMN_NAME,a.DATA_TYPE,a.CHARACTER_MAXIMUM_LENGTH,a.column_comment,a.numeric_scale,a.is_nullable    
from information_schema.COLUMNS as a   where a.TABLE_NAME= '{$table}' ";
            $data = M(trim(I('table')))->query($sql);
            foreach ($data as $key => $val){
                if(empty($val['character_maximum_length'])) $data[$key]['character_maximum_length'] = '/';
            }
        }
        $this->assign('table',I('table'));
        $this->assign("data", $data);
        $this->display();
    }

    //数据库查询接口
    public function ajaxdictionaryField(){
        $modelid = I('modelid','0','intval');
        if(!empty($modelid)){
            $where['modelid'] = $modelid;
        }
        $where['issystem'] = 1;
        $where['disabled'] = 0;
        $data = $this->modelfield->where($where)->field('modelid,field,name,formtype,tips')->select();
        foreach ($data as $key => $val){
            if(empty($val['tips'])){
                $data[$key]['tips']  = '/';
            }
            $data[$key]['tablename'] = M("Model")->where(array("modelid" => $val['modelid']))->field('name,tablename,modelid')->find();
        }

        $result= array();
        foreach ($data as $key => $info) {
            $result[$info['tablename']['modelid']][] = $info;
        }
        $this->ajaxReturn(self::createReturn(true, $result));
    }
}
