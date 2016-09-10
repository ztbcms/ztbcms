<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">模型信息</div>
  <form name="myform" action="{:U('Model/edit')}" method="post" class="J_ajaxForm">
  <div class="table_full">
  <table width="100%" class="table_form">
		<tr>
			<th width="80">模型名称</th> 
			<td><input type="text" name="name"  class="input" id="modelname" size="30" value="{$data.name}"></input></td>
		</tr>
		<tr>
			<th>数据表名</th>
			<td>
			<?php echo C("DB_PREFIX");?>{$data.tablename}
			</td>
		</tr>
		<tr>
			<th>模型描述</th>
			<td>
			<input type="text" name="description" class="input" id="description" size="80" value="{$data.description}"></input>
			</td>
		</tr>
		<tr>
			<th>是否禁用模型</th>
			<td>
				<input type="checkbox" value="1" name="disabled" <if condition=" $data['disabled'] eq '1' ">checked</if> >
			</td>
		</tr>
	</table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
        <input type="hidden" value="{$data.modelid}" name="modelid" />
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
