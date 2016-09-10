<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">模型导入</div>
  <form name="myform" action="{:U('import')}" method="post" enctype="multipart/form-data" method="post" class="J_ajaxForm">
    <div class="table_full">
    <table width="100%" class="table_form">
		<tr>
			<th width="120">模型名称：</th> 
			<td><input type="text" name="name" id="name"  class="input" value="" /> 为空时按配置文件中的</td>
		</tr>
        <tr>
			<th width="120">模型表键名：</th> 
			<td><input type="text" name="tablename" id="tablename"  class="input" value="" /> 为空时按配置文件中的</td>
		</tr>
        <tr>
			<th width="120">配置文件：</th> 
			<td><input type="file" name="file" value="" />只支持.txt文件上传</td>
		</tr>
	</table>
    </div>
     <div class="">
      <button type="submit" class="btn btn_submit J_ajax_submit_btn mr10">导入模型</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
