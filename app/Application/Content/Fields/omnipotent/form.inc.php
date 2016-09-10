<?php

/**
 * 万能字段字段类型表单处理
 * @param type $field 字段名 
 * @param type $value 字段内容
 * @param type $fieldinfo 字段配置
 * @return type
 */
function omnipotent($field, $value, $fieldinfo) {
    $view = \Think\Think::instance('\Think\View');
    $setting = unserialize($fieldinfo['setting']);
    //特殊处理
    if (in_array($setting['fieldtype'], array('text', 'mediumtext', 'longtext'))) {
        $_value = unserialize($value);
        if ($value && $_value) {
            $value = $_value;
            $this->data[$field] = $value;
        }
    }
    $formtext = str_replace('{FIELD_VALUE}', $value, $setting["formtext"]);
    $formtext = str_replace('{MODELID}', $this->modelid, $formtext);
    $formtext = str_replace('{ID}', $this->id ? $this->id : 0, $formtext);
    // 页面缓存
    ob_start();
    ob_implicit_flush(0);
    $view->assign($this->data);
    $view->display('', '', '', $formtext, '');
    // 获取并清空缓存
    $formtext = ob_get_clean();
    //错误提示
    $errortips = $fieldinfo['errortips'];
    if ($fieldinfo['minlength']) {
        //验证规则
        $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
        //验证不通过提示
        $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
    }
    return $formtext;
}
