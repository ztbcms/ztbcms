<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">平台配置</div>
  <form action="  " method="post">
  <div class="table_full">
  <table class="table_form" width="100%" cellspacing="0">
  <tbody>
	<tr>
		<th width="150"><b style="color:red;">*</b><strong> 平台名称 ：</strong></th>
		<td><input name="name" id="name" class="input" type="text" size="30"></td>
	</tr>
	<tr>
		<th><b style="color:red;">*</b> <strong> 表名 ：</strong></th>
		<td>ztb_sms_<input name="tablename" id="tablename" class="input" type="text" size="25"></td>
	</tr>
	<tr>
		<th><strong>简介：</strong></th>
		<td><textarea name="remark" id="remark" rows="6" cols="50"></textarea></td>
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
</body>
</html>
