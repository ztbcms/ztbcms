<?php

// +----------------------------------------------------------------------
// | 模型字段新增/更新行为调用
// +----------------------------------------------------------------------

namespace Content\Behavior;

class ContentModelEditFieldBehavior {

    public function run(&$params) {
        $field = M('ModelField')->where($params)->find();
        if ($field['formtype'] == 'box') {
            //如果为选项组件，反序列化设置参数
            $setting = unserialize($field['setting']);
            if ($setting['relation'] == 1) {
                //要求关联表，检查数据库表是否存在
                $model = M('Model')->where(['modelid' => $field['modelid']])->field('modelid,tablename')->find();
                $tableName = C('DB_PREFIX') . $model['tablename'] . '_box_' . $field['field'];
                $tableNameNo = $model['tablename'] . '_box_' . $field['field'];
                M()->execute("DROP TABLE IF EXISTS `$tableName`");
                $createTableSql = str_replace('cms_box_options', $tableName,
                    file_get_contents(APP_PATH . 'Content/Data/Sql/cms_box_field.sql'));
                if (M()->execute($createTableSql) === 0) {
                    $options = explode("\n", $setting['options']);
                    $createData = [];
                    foreach ($options as $_k) {
                        $v = explode("|", $_k);
                        $createData[] = [
                            'modelid' => $field['modelid'],
                            'fieldid' => $field['fieldid'],
                            'label' => $v[0],
                            'value' => $v[1]
                        ];
                    }
                    M($tableNameNo)->addAll($createData);
                }
            }
        }
    }
}
