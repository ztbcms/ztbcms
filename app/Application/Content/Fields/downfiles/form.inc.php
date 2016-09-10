<?php

/**
 * 多文件上传 表单组合处理
 * @param type $field 字段名
 * @param type $value 字段内容
 * @param type $fieldinfo 字段配置
 * @return string
 */
function downfiles($field, $value, $fieldinfo) {
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
    $list_str = '';
    if ($value) {
        $value = unserialize(html_entity_decode($value, ENT_QUOTES));
        if (defined("IN_ADMIN") && IN_ADMIN && isModuleInstall('Member')) {
            $Member_group = cache("Member_group");
            foreach ($Member_group as $v) {
                if (in_array($v['groupid'], array("1", "7", "8"))) {
                    continue;
                }
                $group[$v['groupid']] = $v['name'];
            }
        }
        if (is_array($value)) {
            foreach ($value as $_k => $_v) {
                if (defined("IN_ADMIN") && IN_ADMIN && isModuleInstall('Member')) {
                    $list_str .= "<div id='multifile{$_k}'><input type='text' name='{$field}_fileurl[]' value='{$_v['fileurl']}' style='width:310px;' class='input'> <input type='text' name='{$field}_filename[]' value='{$_v['filename']}' style='width:160px;' class='input'> 权限：" . \Form::select($group, $_v['groupid'], 'name="' . $field . '_groupid[]"', '游客') . " 点数：<input type='text' name='{$field}_point[]' value='" . $_v['point'] . "' style='width:60px;' class='input'> <a href=\"javascript:remove_div('multifile{$_k}')\">移除</a></div>";
                } else {
                    $list_str .= "<div id='multifile{$_k}'><input type='text' name='{$field}_fileurl[]' value='{$_v['fileurl']}' style='width:310px;' class='input'> <input type='text' name='{$field}_filename[]' value='{$_v['filename']}' style='width:160px;' class='input'> <a href=\"javascript:remove_div('multifile{$_k}')\">移除</a></div>";
                }
            }
        }
    }
    $string = '<input name="info[' . $field . ']" type="hidden" value="1">
		<fieldset class="blue pad-10">
        <legend>文件列表</legend>';
    $string .= $list_str;
    $string .= '<ul id="' . $field . '" class="picList"></ul>
		</fieldset>
		<div class="bk10"></div>
		';

    //模块
    $module = MODULE_NAME;
    //生成上传附件验证
    $authkey = upload_key("{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']}");
    //后台允许权限设置
    if (defined("IN_ADMIN") && IN_ADMIN && isModuleInstall('Member')) {
        $Member_group = cache("Member_group");
        foreach ($Member_group as $v) {
            if (in_array($v['groupid'], array("1", "7", "8"))) {
                continue;
            }
            $group[$v['groupid']] = $v['name'];
        }
        $js = '<script type="text/javascript">
function change_multifile_admin(uploadid, returnid) {
    var d = uploadid.iframe.contentWindow;
    var in_content = d.$("#att-status").html().substring(1);
    var in_filename = d.$("#att-name").html().substring(1);
    var str = \'\';
    var contents = in_content.split(\'|\');
    var filenames = in_filename.split(\'|\');
    var group = \'权限：' . \Form::select($group, $id, 'name="\' + returnid + \'_groupid[]"', '游客') . '\';
    $(\'#\' + returnid + \'_tips\').css(\'display\', \'none\');
    if (contents == \'\') return true;
    $.each(contents, function (i, n) {
        var ids = parseInt(Math.random() * 10000 + 10 * i);
        var filename = filenames[i].substr(0, filenames[i].indexOf(\'.\'));
        str += "<li id=\'multifile" + ids + "\'><input type=\'text\' name=\'" + returnid + "_fileurl[]\' value=\'" + n + "\' style=\'width:310px;\' class=\'input\'> <input type=\'text\' name=\'" + returnid + "_filename[]\' value=\'" + filename + "\' style=\'width:160px;\' class=\'input\' onfocus=\"if(this.value == this.defaultValue) this.value = \'\'\" onblur=\"if(this.value.replace(\' \',\'\') == \'\') this.value = this.defaultValue;\"> "+group+" 点数：<input type=\'text\' name=\'" + returnid + "_point[]\' value=\'0\' style=\'width:60px;\' class=\'input\'> <a href=\"javascript:remove_div(\'multifile" + ids + "\')\">移除</a> </li>";
    });
    $(\'#\' + returnid).append(str);
}

function add_multifile_admin(returnid) {
    var ids = parseInt(Math.random() * 10000);
    var group = \'权限：' . \Form::select($group, $id, 'name="\' + returnid + \'_groupid[]"', '游客') . '\';
    var str = "<li id=\'multifile" + ids + "\'><input type=\'text\' name=\'" + returnid + "_fileurl[]\' value=\'\' style=\'width:310px;\' class=\'input\'> <input type=\'text\' name=\'" + returnid + "_filename[]\' value=\'附件说明\' style=\'width:160px;\' class=\'input\'> "+group+"  点数：<input type=\'text\' name=\'" + returnid + "_point[]\' value=\'0\' style=\'width:60px;\' class=\'input\'>  <a href=\"javascript:remove_div(\'multifile" + ids + "\')\">移除</a> </li>";
    $(\'#\' + returnid).append(str);
};</script>';
        $string .= $str . "<a herf='javascript:void(0);' class=\"btn\"  onclick=\"javascript:flashupload('{$field}_multifile', '附件上传','{$field}',change_multifile_admin,'{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']}','{$module}','$this->catid','{$authkey}')\"><span class=\"add\"></span>多文件上传</a>    <a  class=\"btn\" herf='javascript:void(0);'  onclick=\"add_multifile_admin('{$field}')\"><span class=\"add\"></span>添加远程地址</a>$js";
    } else {
        $string .= $str . "<a herf='javascript:void(0);'  class=\"btn\" onclick=\"javascript:flashupload('{$field}_multifile', '附件上传','{$field}',change_multifile,'{$setting['upload_number']},{$setting['upload_allowext']},{$setting['isselectimage']}','{$module}','$this->catid','{$authkey}')\"><span class=\"add\"></span>多文件上传</a>    <a herf='javascript:void(0);' class=\"btn\" onclick=\"add_multifile('{$field}')\"><span class=\"add\"></span>添加远程地址</a>";
    }
    return $string;
}