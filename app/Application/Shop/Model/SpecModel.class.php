<?php

namespace Shop\Model;
use Think\Model;

class SpecModel extends Model {
    //protected $tablePrefix = 'tp_';
    protected $patchValidate = true; // 系统支持数据的批量验证功能，
    protected $items = '';
    /**
     *
     *   self::EXISTS_VALIDATE 或者0 存在字段就验证（默认）
     *   self::MUST_VALIDATE 或者1 必须验证
     *   self::VALUE_VALIDATE或者2 值不为空的时候验证
     *
     *
     *  self::MODEL_INSERT或者1新增数据时候验证
     * self::MODEL_UPDATE或者2编辑数据时候验证
     * self::MODEL_BOTH或者3全部情况下验证（默认）
     */
    protected $_validate = array(
        array('name', 'require', '规格名称必须填写！', 1, '', 3),
        array('type_id', 'require', '商品类型必须选择！', 1, '', 3),
        array('items', 'require', '规格项不能为空！', 1, '', 1), // 编辑的时候可以为空  才可以删除规格
        array('order', 'number', '排序必须为数字！', 2, '', 3), //
    );

    /**
     * 后置操作方法
     * 自定义的一个函数 用于数据保存后做的相应处理操作, 使用时手动调用
     * @param int $id 规格id
     */
    public function afterSave($id) {

        $model = M("SpecItem"); // 实例化User对象
        $post_items = explode(PHP_EOL, $_POST['items']);
        foreach ($post_items as $key => $val) // 去除空格
        {
            $val = str_replace('_', '', $val); // 替换特殊字符
            $val = str_replace('@', '', $val); // 替换特殊字符

            $val = trim($val);
            if (empty($val)) {
                unset($post_items[$key]);
            } else {
                $post_items[$key] = $val;
            }

        }
        $db_items = $model->where("spec_id = $id")->getField('id,item');
        // 两边 比较两次

        /* 提交过来的 跟数据库中比较 不存在 插入*/
        foreach ($post_items as $key => $val) {
            if (!in_array($val, $db_items)) {
                $dataList[] = array('spec_id' => $id, 'item' => $val);
            }

        }
        // 批量添加数据
        $dataList && $model->addAll($dataList);

        /* 数据库中的 跟提交过来的比较 不存在删除*/
        foreach ($db_items as $key => $val) {
            if (!in_array($val, $post_items)) {
                //  SELECT * FROM `tp_spec_goods_price` WHERE `key` REGEXP '^11_' OR `key` REGEXP '_13_' OR `key` REGEXP '_21$'
                M("SpecGoodsPrice")->where("`key` REGEXP '^{$key}_' OR `key` REGEXP '_{$key}_' OR `key` REGEXP '_{$key}$' or `key` = '{$key}'")->delete(); // 删除规格项价格表
                $model->where('id=' . $key)->delete(); // 删除规格项
            }
        }
    }
}
