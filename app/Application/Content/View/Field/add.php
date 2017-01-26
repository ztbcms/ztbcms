<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>

  <div class="h_a">模型信息</div>
  <div class="prompt_text">
      <p>名称: {$modelinfo['name']}</p>
      <p>表名: {$modelinfo['tablename']}</p>
  </div>

  <div class="h_a">字段属性</div>
  <form name="myform" class="J_ajaxForm" action="{:U("Field/add")}" method="post">
  <div class="table_full">
  <table width="100%" class="table_form contentWrap">
      <tr>
        <th width="250"><strong>字段类型</strong><br /></th>
        <td><select name="formtype" id="formtype" onChange="javascript:field_setting(this.value);">
            <option value='' selected>请选择字段类型</option>
            <volist name="all_field" id="vo">
            <option value="{$key}" >{$vo}</option>
            </volist>
          </select></td>
      </tr>
      <tr>
        <th><strong>作为主表字段</strong></th>
        <td>
          <input type="radio" name="issystem" id="field_basic_table1" value="1" checked>
          是
          <input type="radio" id="field_basic_table0" name="issystem" value="0">
          否</td>
      </tr>
      <tr>
        <th width="25%"><font color="red">*</font> <strong>字段名</strong><br />
          只能由英文字母、数字和下划线组成，并且仅能字母开头，不以下划线结尾 </th>
        <td><input type="text" name="field" id="field" size="20" class="input"></td>
      </tr>
      <tr>
        <th><font color="red">*</font> <strong>字段别名</strong><br />
          例如：文章标题</th>
        <td><input type="text" name="name" id="name" size="30" class="input"></td>
      </tr>
      <tr>
        <th><strong>字段提示</strong><br />
          显示在字段别名下方作为表单输入提示</th>
        <td><textarea name="tips" rows="2" cols="20" id="tips" style="height:40px; width:80%"></textarea></td>
      </tr>
      <tr>
        <th><strong>相关参数</strong><br />
          设置表单相关属性</th>
        <td><div id="setting"></div></td>
      </tr>
      <tr id="formattribute">
        <th><strong>表单附加属性</strong><br />
          可以通过此处加入javascript事件</th>
        <td><input type="text" name="formattribute" value="" size="50" class="input"></td>
      </tr>
      <tr id="css">
        <th><strong>表单样式名</strong><br />
          定义表单的CSS样式名</th>
        <td><input type="text" name="css" value="" size="10" class="input"></td>
      </tr>
      <tr>
        <th><strong>字符长度取值范围</strong><br />
          系统将在表单提交时检测数据长度范围是否符合要求，如果不想限制长度请留空</th>
        <td>最小值：
          <input type="text" name="minlength" id="field_minlength" value="0" size="5" class="input">
          最大值：
          <input type="text" name="maxlength" id="field_maxlength" value="0" size="5" class="input"></td>
      </tr>
      <tr>
        <th><strong>数据校验正则</strong><br />
          系统将通过此正则校验表单提交的数据合法性，如果不想校验数据请留空</th>
        <td><input type="text" name="pattern" id="pattern" value="" size="40" class="input">
          <select name="pattern_select" onChange="javascript:$('#pattern').val(this.value)">
            <option value="">常用正则</option>
            <option value="/^[0-9.-]+$/">数字</option>
            <option value="/^[0-9-]+$/">整数</option>
            <option value="/^[a-z]+$/i">字母</option>
            <option value="/^[0-9a-z]+$/i">数字+字母</option>
            <option value="/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/">E-mail</option>
            <option value="/^[0-9]{5,20}$/">QQ</option>
            <option value="/^http:\/\//">超级链接</option>
            <option value="/^(1)[0-9]{10}$/">手机号码</option>
            <option value="/^[0-9-]{6,13}$/">电话号码</option>
          </select></td>
      </tr>
      <tr>
        <th><strong>数据校验未通过的提示信息</strong></th>
        <td><input type="text" name="errortips" value="" size="50" class="input"></td>
      </tr>
      <tr>
        <th><strong>后台信息处理函数</strong><br />用法：直接填写函数名称，如果有附带参数可以在函数名后面加###参数1,参数2.完整例子：usfun###a1,a2</th>
        <td><input type="text" name="setting[backstagefun]" value="" size="50" class="input"> <input name="setting[backstagefun_type]" type="radio" value="1" checked="checked" >入库前 <input type="radio" name="setting[backstagefun_type]" value="2" >入库后 <input type="radio" name="setting[backstagefun_type]" value="3" >入库前后</td>
      </tr>
      <tr>
        <th><strong>前台信息处理函数</strong><br />用法：直接填写函数名称，如果有附带参数可以在函数名后面加###参数1,参数2.完整例子：usfun###a1,a2</th>
        <td><input type="text" name="setting[frontfun]" value="" size="50" class="input"> <input name="setting[frontfun_type]" type="radio" value="1" checked="checked" >入库前 <input type="radio" name="setting[frontfun_type]" value="2" >入库后 <input type="radio" name="setting[frontfun_type]" value="3" >入库前后</td>
      </tr>
      <tr>
        <th><strong>值唯一</strong></th>
        <td><input type="radio" name="isunique" value="1" id="field_allow_isunique1">
          是
          <input type="radio" name="isunique" value="0" id="field_allow_isunique0" checked>
          否</td>
      </tr>
      <tr>
        <th><strong>作为基本信息</strong><br />
          基本信息将在添加页面左侧显示</th>
        <td><input type="radio" name="isbase" value="1"  checked>
          是
          <input type="radio" name="isbase" value="0">
          否 </td>
      </tr>
      <tr>
        <th><strong>作为搜索条件</strong></th>
        <td><input type="radio" name="issearch" value="1" id="field_allow_search1">
          是
          <input type="radio" name="issearch" value="0" id="field_allow_search0" checked>
          否</td>
      </tr>
      <tr>
        <th><strong>在前台投稿中显示</strong></th>
        <td><input type="radio" name="isadd" value="1" checked />
          是
          <input type="radio" name="isadd" value="0" />
          否</td>
      </tr>
      <tr>
        <th><strong>作为全站搜索信息</strong></th>
        <td><input type="radio" name="isfulltext" value="1" id="field_allow_fulltext1" checked/>
          是
          <input type="radio" name="isfulltext" value="0" id="field_allow_fulltext0" />
          否</td>
      </tr>
      <tr> 
         <th><strong>作为万能字段的附属字段</strong><br>必须与万能字段结合起来使用，否则内容添加的时候不会正常显示，使用时直接在使用“{当前字段名}”例如{keywords}</th>
         <td><input type="radio" name="isomnipotent" value="1" /> 是 <input type="radio" name="isomnipotent" value="0"  checked/> 否</td>
      </tr>
      <tr> 
        <th><strong>在推荐位标签中调用</strong></th>
        <td><input type="radio" name="isposition" value="1" /> 是 <input type="radio" name="isposition" value="0" checked/> 否</td>
      </tr>
    </table>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">添加</button>
        <input name="modelid" type="hidden" value="{$modelid}" />
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript">
function field_setting(fieldtype) {
    $('#formattribute').css('display', 'none');
    $('#css').css('display', 'none');
    if (fieldtype == "") {
        return false;
    }
    $.each(['text', 'textarea', 'box', 'number', 'keyword', 'typeid'], function (i, n) {
        if (fieldtype == n) {
            $('#formattribute').css('display', '');
            $('#css').css('display', '');
        }
    });

    $.getJSON("{:U('Field/public_field_setting')}",{fieldtype:fieldtype}, function (data) {
        if (data.field_basic_table == '1') {
            $('#field_basic_table0').attr("disabled", false);
            $('#field_basic_table1').attr("disabled", false);
        } else {
            $('#field_basic_table0').attr("checked", true);
            $('#field_basic_table0').attr("disabled", true);
            $('#field_basic_table1').attr("disabled", true);
        }
        if (data.field_allow_search == '1') {
            $('#field_allow_search0').attr("disabled", false);
            $('#field_allow_search1').attr("disabled", false);
        } else {
            $('#field_allow_search0').attr("checked", true);
            $('#field_allow_search0').attr("disabled", true);
            $('#field_allow_search1').attr("disabled", true);
        }
        if (data.field_allow_fulltext == '1') {
            $('#field_allow_fulltext0').attr("disabled", false);
            $('#field_allow_fulltext1').attr("disabled", false);
        } else {
            $('#field_allow_fulltext0').attr("checked", true);
            $('#field_allow_fulltext0').attr("disabled", true);
            $('#field_allow_fulltext1').attr("disabled", true);
        }
        if (data.field_allow_isunique == '1') {
            $('#field_allow_isunique0').attr("disabled", false);
            $('#field_allow_isunique1').attr("disabled", false);
        } else {
            $('#field_allow_isunique0').attr("checked", true);
            $('#field_allow_isunique0').attr("disabled", true);
            $('#field_allow_isunique1').attr("disabled", true);
        }
        $('#field_minlength').val(data.field_minlength);
        $('#field_maxlength').val(data.field_maxlength);
        $('#setting').html(data.setting);

    });
}
</script>
</body>
</html>
