<?php
/**
 * Created by PhpStorm.
 * User: zhlhuang
 * Date: 2019-03-29
 * Time: 16:59.
 */

namespace Content\Controller;

use Common\Controller\AdminBase;

class BoxFieldController extends AdminBase {
    //初始化
    protected function _initialize() {
        parent::_initialize();
    }

    /**
     * 显示选项列表
     */
    public function list() {
        $modelid = I('get.modelid');
        $fieldid = I('get.fieldid');
        $model = M('Model')->where(['modelid' => $modelid])->field('tablename')->find();
        $field = M('ModelField')->where(['fieldid' => $fieldid])->field('field')->find();
        $tableName = $model['tablename'] . '_box_' . $field['field'];
        $options = M($tableName)->select();
        $this->assign('options', $options);
        $this->display();
    }

    /**
     * 保存选项记录
     */
    public function save() {
        $postData = I('post.data');
        if (count($postData) > 0) {
            $modelid = $postData[0]['modelid'];
            $fieldid = $postData[0]['fieldid'];
            $model = M('Model')->where(['modelid' => $modelid])->field('tablename')->find();
            $field = M('ModelField')->where(['fieldid' => $fieldid])->find();
            if ($model && $field) {
                $tableName = $model['tablename'] . '_box_' . $field['field'];
                M($tableName)->where(1)->delete();
                $options = [];
                foreach ($postData as $key => &$datum) {
                    unset($datum['id']);
                    $options[] = $datum['label'] . '|' . $datum['value'];
                }
                $optionsString = implode("\n", $options);
                M($tableName)->addAll($postData);
                //更新modelField 信息
                $setting = unserialize($field['setting']);
                $setting['options'] = $optionsString;
                M('ModelField')->where(['fieldid' => $fieldid])->save(['setting' => serialize($setting)]);
                //清楚字段缓存
                S('ModelField', null);
                $this->ajaxReturn(self::createReturn(true, $postData));
            } else {
                $this->ajaxReturn(self::createReturn(false, $postData, '查不到选项记录'));
            }
        } else {
            $this->ajaxReturn(self::createReturn(false, $postData, '选项记录不能为空'));
        }
    }
}