 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">模型信息</div>
  <form name="myform" action="{:U('Model/add')}" method="post" class="J_ajaxForm">
  <div class="table_full">
  <table width="100%" class="table_form">
		<tr>
			<th width="80">模型名称</th> 
			<td><input type="text" name="name"  class="input" id="modelname" size="30"></input></td>
		</tr>
		<tr>
			<th>数据表名</th>
			<td>
			<?php echo C("DB_PREFIX");?>member_<input type="text" name="tablename" value="" class="input" id="tablename" size="16"></input>
			</td>
		</tr>
		<tr>
			<th>模型描述</th>
			<td>
			<input type="text" name="description" value="" class="input" id="description" size="80"></input>
			</td>
		</tr>
	</table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
