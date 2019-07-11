<?php

/**
 * 关联栏目字段类型内容获取
 *
 * @param $field
 * @param $value
 *
 * @throws \Think\Exception
 * @return string
 */
function relationbox($field, $value)
{
    $setting = unserialize($this->fields[$field]['setting']);
    if ($setting['outputtype']) {
        return $value;
    } else {
        $catid = $setting['options'];
        $catInfo = getCategory($catid);
        $model = Content\Model\ContentModel::getInstance($catInfo['modelid']);
        $records = $model->where(['status' => 99])->select();
        foreach ($records as $record) {
            $option[$record['id']] = $record[$setting['fieldkey']];
        }
        $string = '';
        switch ($setting['boxtype']) {
            case 'radio':
                $string = $option[$value];
                break;
            case 'checkbox':
                $value_arr = explode(',', $value);
                foreach ($value_arr as $_v) {
                    if ($_v)
                        $string .= $option[$_v].' 、';
                }
                break;
            case 'select':
                $string = $option[$value];
                break;
            case 'multiple':
                $value_arr = explode(',', $value);
                foreach ($value_arr as $_v) {
                    if ($_v)
                        $string .= $option[$_v].' 、';
                }
                break;
        }
        return $string;
    }
}