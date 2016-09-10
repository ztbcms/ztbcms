<?php

/**
 * 多图片字段类型表单组合处理
 * @param type $field 字段名
 * @param type $value 字段内容
 * @param type $fieldinfo 字段值
 * @return string
 */
function images($field, $value, $fieldinfo) {
    //错误提示
    $errortips = $fieldinfo['errortips'];
    //长度
    if ($fieldinfo['minlength']) {
        //验证规则
        $this->formValidateRules['info[' . $field . ']'] = array("required" => true);
        //验证不通过提示
        $this->formValidateMessages['info[' . $field . ']'] = array("required" => $errortips ? $errortips : $fieldinfo['name'] . "不能为空！");
    }
    //字段扩展配置
    $setting = unserialize($fieldinfo['setting']);
    $list_str = '';
    if ($value) {
        $value = unserialize(html_entity_decode($value, ENT_QUOTES));
        if (is_array($value)) {
            foreach ($value as $_k => $_v) {
                $list_str .= "<div id='image_{$field}_{$_k}' style='padding:1px'><input type='text' name='{$field}_url[]' value='{$_v['url']}' style='width:310px;' ondblclick='image_priview(this.value);' class='input'> <input type='text' name='{$field}_alt[]' value='{$_v['alt']}' style='width:160px;' class='input'> <a href=\"javascript:remove_div('image_{$field}_{$_k}')\">移除</a></div>";
            }
        }
    } else {
        $list_str .= "<center><div class='onShow' id='nameTip'>您最多每次可以同时上传 <font color='red'>{$setting['upload_number']}</font> 张</div></center>";
    }
    $string = '<input name="info[' . $field . ']" type="hidden" value="1">
		<fieldset class="blue pad-10">
        <legend>图片列表</legend>';
    $string .= $list_str;
    $string .= '<div id="' . $field . '" class="picList"></div>
		</fieldset>
		<div class="bk10"></div>
		';
    //模块
    $module = MODULE_NAME;
    //生成上传附件验证
    $authkey = upload_key("{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']},,,{$setting['watermark']}");
    $string .= $str . "<a herf='javascript:void(0);' onclick=\"javascript:flashupload('{$field}_images', '图片上传','{$field}',change_images,'{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']},,,{$setting['watermark']}','{$module}','$this->catid','{$authkey}')\" class=\"btn\"><span class=\"add\"></span>选择图片 </a>";
    return $string;
}