<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="body_none" style="width:400px; height:185px;">
<div class="wrap_pop ">
  <div class="h_a">采集节点复制</div>
  <form name="myform" action="{:U('Node/copy',array('nodeid'=>$nodeid))}" method="post" class="J_ajaxForm">
    <div class="table_full"  style="overflow-x:hidden;">
    <table width="100%" class="table_form">
		<tr>
			<th width="120">原采集点名：</th> 
			<td>啊啊啊</td>
		</tr>
		<tr>
			<th width="120">新采集点名：</th> 
			<td><input type="text" name="name" id="name"  class="input" value="" /></td>
		</tr>
	</table>
    </div>
     <div class="">
      <button class="btn fr" id="J_dialog_close" type="button">取消</button>
      <button type="submit" class="btn btn_submit J_ajax_submit_btn fr mr10">提交</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
