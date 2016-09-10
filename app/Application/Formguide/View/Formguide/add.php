<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">表单配置</div>
  <form action="{:U('Formguide/add')}" method="post" name="myform" class="J_ajaxForm">
  <div class="table_full">
  <table class="table_form" width="100%" cellspacing="0">
  <tbody>
	<tr>
		<th width="150"><strong>名称：</strong></th>
		<td><input name="info[name]" id="name" class="input" type="text" size="30"></td>
	</tr>
	<tr>
		<th><strong>表名：</strong></th>
		<td><input name="info[tablename]" id="tablename" class="input" type="text" size="25"></td>
	</tr>
	<tr>
		<th><strong>简介：</strong></th>
		<td><textarea name="info[description]" id="description" rows="6" cols="50"></textarea></td>
	</tr>
    <tr>
		<th><strong>提交后跳转地址：</strong></th>
		<td><input type="text" name="setting[forward]" value="" class="input length_6"></td>
	</tr>
	<tr>
		<th><strong>表单有有效期时间限制：</strong></th>
		<td><label><input type="radio" name="setting[enabletime]" value="1"> 启用</label> <label><input type="radio" name="setting[enabletime]" value="0" checked=""> 不启用</label></td>
	</tr>
	<tr id="time_start" style="display:none;">
  		<th><strong>开始时间：</strong></th>
        <td><input type="text" name="setting[starttime]" id="starttime" value="{:date('Y-m-d H:i')}" class="J_datetime input" ></td>
	</tr>
	<tr id="time_end" style="display:none;">
		<th><strong>结束时间：</strong></th>
		<td><input type="text" name="setting[endtime]" id="endtime" value=""  class="J_datetime input" ></td>
	</tr>
	<tr>
		<th><strong>允许发送邮件：</strong></th>
		<td><label><input name="setting[sendmail]" type="radio" value="1">&nbsp;是</label>&nbsp;&nbsp;<label><input name="setting[sendmail]" type="radio" value="0" checked="">&nbsp;否</label></td>
	</tr>
	<tr id="mailaddress" style="display:none;">
		<th><strong>接受邮件的地址：</strong></th>
		<td><input type="text" name="setting[mails]" id="mails" class="input" size="50"> 多个地址请用“,”隔开</td>
	</tr>
	<tr>
		<th><strong>允许同一IP多次提交：</strong></th>
		<td><label><input type="radio" name="setting[allowmultisubmit]" value="1"> 是</label>&nbsp;&nbsp;
	  <label><input type="radio" name="setting[allowmultisubmit]" value="0" checked=""> 否</label></td>
	</tr>
	<tr>
		<th><strong>允许游客提交表单：</strong></th>
		<td><label><input type="radio" name="setting[allowunreg]" value="1"> 是</label>&nbsp;&nbsp;
	  <label><input type="radio" name="setting[allowunreg]" value="0" checked=""> 否</label></td>
	</tr>
    <tr>
		<th><strong>开启验证码：</strong></th>
		<td><label><input type="radio" name="setting[isverify]" value="1"> 是</label>&nbsp;&nbsp;
	  <label><input type="radio" name="setting[isverify]" value="0" checked> 否</label></td>
	</tr>
    <tr>
		<th><strong>提交间隔：</strong></th>
		<td><input name="setting[interval]" id="interval" value="0" class="input" type="text" size="25"> 单位秒，0为不限</td>
	</tr>
    <tr>
		<th><strong>不允许提交IP：</strong></th>
		<td><textarea name="setting[noip]" id="noip" rows="6" cols="50"></textarea><br/>每行一个<br/>192.168.1.1 单个IP<br/>192.168.1.* 整个192.168.1开头的IP段<br/>210.10.2.1-20表示从"210.10.2.1"到"210.10.2.20"的21个ip</td>
	</tr>
	<tr>
		<th><strong>模板选择：</strong></th>
		<td id="show_template"><?php echo \Form::select($show_template,0,'name="setting[show_template]" id="show_template"'); ?> 添加模板，文件名请以“show”开头</td>
	</tr>
	<tr>
		<th><strong>js调用使用的模板：</strong></th>
		<td id="show_js_template"><?php echo \Form::select($show_js_template,0,'name="setting[show_js_template]" id="show_js_template"'); ?> 添加模板，文件名请以“js”开头</td>
	</tr>
	</tbody>
</table>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
$(function () {
    $("input:radio[name='setting[enabletime]']").click(function () {
        if ($("input:radio[name='setting[enabletime]'][checked]").val() == 0) {
            $("#time_start").hide();
            $("#time_end").hide();
        } else if ($("input:radio[name='setting[enabletime]'][checked]").val() == 1) {
            $("#time_start").show();
            $("#time_end").show();
        }
    });
    $("input:radio[name='setting[sendmail]']").click(function () {
        if ($("input:radio[name='setting[sendmail]'][checked]").val() == 0) {
            $("#mailaddress").hide();
        } else if ($("input:radio[name='setting[sendmail]'][checked]").val() == 1) {
            $("#mailaddress").show();
        }
    });
});
</script>
</body>
</html>
