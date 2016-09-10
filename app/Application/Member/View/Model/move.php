<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text"> 移动模型会员，将会把原有模型里的会员信息删除，不能修复。</div>
  <form name="myform" action="{:U('Model/move')}" method="post" id="myform">
  <input type="hidden" name="modelid" value="{$modelid}">
  <div class="table_full">
  <div class="h_a">模型移动</div>
  <table width="100%" class="table_form">
		<tr>
			<th width="120">所在模型</th> 
			<td>
				<?php echo $modelselect[$modelid]?>
			</td>
		</tr>
		<tr>
			<th width="120">目标模型</th> 
			<td><?php echo Form::select($modelselect,0 ,'id="to_modelid" name="to_modelid"' ,"请选择"); ?></td>
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
</body>
</html>
