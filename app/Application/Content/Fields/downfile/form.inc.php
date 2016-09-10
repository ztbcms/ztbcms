<?php

/**
 * 单文件上传字段表单组合处理
 * @param type $field 字段名
 * @param type $value 字段内容
 * @param type $fieldinfo 字段配置
 * @return type
 */
function downfile($field, $value, $fieldinfo) {
    //错误提示
    $errortips = $fieldinfo['errortips'];
    if ($fieldinfo['minlength']) {
        //验证规则
        $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
        //验证不通过提示
        $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
    }
    //扩展配置
    $setting = unserialize($fieldinfo['setting']);
    //表单长度
    $width = $setting['width'] ? $setting['width'] : 300;
    //生成上传附件验证 //同时允许的上传个数, 允许上传的文件类型, 是否允许从已上传中选择
    $authkey = upload_key("1,{$setting['upload_allowext']},{$setting['isselectimage']}");
    //模块
    $module = MODULE_NAME;
    //文本框模式
    return "<input type='text' name='info[$field]' id='$field' value='$value' style='width:{$width}px;' class='input' />  <input type='button' class='button' onclick=\"flashupload('{$field}_downfile', '附件上传','{$field}',submit_attachment,'1,{$setting['upload_allowext']},{$setting['isselectimage']}','{$module}','$this->catid','$authkey')\"/ value='上传文件'>";
}