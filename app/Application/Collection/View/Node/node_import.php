<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">采集节点导入</div>
  <form name="myform" action="{:U('Node/node_import')}" method="post" class="J_ajaxForm">
    <div class="table_full">
    <table width="100%" class="table_form">
		<tr>
			<th width="120">采集点名：</th> 
			<td><input type="text" name="name" id="name"  class="input" value="" /></td>
		</tr>
		<tr>
			<th width="120">配置文件：</th> 
			<td><input type="file" name="file" value="" />只支持.txt文件上传</td>
		</tr>
	</table>
    </div>
     <div class="">
      <button type="submit" class="btn btn_submit J_ajax_submit_btn mr10">提交</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
