 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">字段属性</div>
  <form name="myform" class="J_ajaxForm" action="{:U("Field/edit")}" method="post">
  <div class="table_full">
  <table width="100%" class="table_form">
      <tr>
        <th width="250"><strong>字段类型</strong><br /></th>
        <td>
          <select name="formtype" id="formtype" onChange="javascript:field_setting(this.value);">
            <option value='' >请选择字段类型</option>
            <volist name="all_field" id="vo">
            <option value="{$key}" <if condition="$data['formtype'] eq $key"> selected</if>>{$vo}</option>
            </volist>
          </select></td>
      </tr>
      <tr>
        <th width="25%"><font color="red">*</font> <strong>字段名</strong><br />
          只能由英文字母、数字和下划线组成，并且仅能字母开头，不以下划线结尾 </th>
        <td><input type="text" name="field" id="field" size="20" class="input" value="{$data.field}" ></td>
      </tr>
      <tr>
        <th><font color="red">*</font> <strong>字段别名</strong><br />
          例如：文章标题</th>
        <td><input type="text" name="name" id="name" size="30" class="input" value="{$data.name}"></td>
      </tr>
      <tr>
        <th><strong>字段提示</strong><br />
          显示在字段别名下方作为表单输入提示</th>
        <td><textarea name="tips" rows="2" cols="20" id="tips" style="height:40px; width:80%">{$data.tips}</textarea></td>
      </tr>
      <tr>
        <th><strong>相关参数</strong><br />
          设置表单相关属性</th>
        <td><div id="setting">{$form_data}</div></td>
      </tr>
      <tr>
        <th><strong>字符长度取值范围</strong><br />
          系统将在表单提交时检测数据长度范围是否符合要求，如果不想限制长度请留空</th>
        <td>最小值：
          <input type="text" name="minlength" id="field_minlength" value="{$data.minlength}" size="5" class="input">
          最大值：
          <input type="text" name="maxlength" id="field_maxlength" value="{$data.maxlength}" size="5" class="input"></td>
      </tr>
      <tr>
        <th><strong>数据校验正则</strong><br />
          系统将通过此正则校验表单提交的数据合法性，如果不想校验数据请留空</th>
        <td><input type="text" name="pattern" id="pattern" value="{$data.pattern}" size="40" class="input">
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
        <td><input type="text" name="errortips" value="{$data.errortips}" size="50" class="input"></td>
      </tr>
      <tr>
        <th><strong>值唯一</strong></th>
        <td><input type="radio" name="isunique" value="1" id="field_allow_isunique1" <if condition="$data['isunique'] eq '1'"> checked</if> disabled>
          是
          <input type="radio" name="isunique" value="0" id="field_allow_isunique0" <if condition="$data['isunique'] eq '0'"> checked</if> disabled>
          否</td>
      </tr>
	  <tr>
        <th><strong>是否显示</strong></th>
        <td>
          <input type="radio" name="isadd" value="1" id="field_allow_search0" <if condition="$data['isadd'] eq '1'"> checked</if>>
          是
		  <input type="radio" name="isadd" value="0" id="field_allow_search1" <if condition="$data['isadd'] eq '0'"> checked</if>>
          否</td>
      </tr>
    </table>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
        <input name="modelid" type="hidden" value="{$modelid}">
        <input name="fieldid" type="hidden" value="{$fieldid}">
        <input name="oldfield" type="hidden" value="{$data.field}">
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript">

function field_setting(fieldtype) {
    $('#formattribute').css('display', 'none');
    $('#css').css('display', 'none');
	if(fieldtype == ""){
	    return  false;
	}
    $.each(['text', 'textarea', 'box', 'number', 'keyword', 'typeid'], function (i, n) {
        if (fieldtype == n) {
            $('#formattribute').css('display', '');
            $('#css').css('display', '');
        }
    });

    $.getJSON(GV.DIMAUB+"index.php?g=Member&m=Field&a=public_field_setting&fieldtype=" + fieldtype, function (data) {
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
