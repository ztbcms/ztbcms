 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">推荐位修改</div>
  <form name="myform" action="{:U("edit")}" method="post" class="J_ajaxForm">
  <div class="table_full"> 
  <table width="100%" class="table_form contentWrap">
        <tr>
          <th  width="80">推荐位名称</th>
          <td><input type="text" name="info[name]" value="{$name}" class="input" id="name"/></td>
        </tr>
        <tr>
          <th>所属模型</th>
          <td><?php echo Form::checkbox($modelinfo,$modelid,'name="info[modelid][]" onchange="category_load();"','');?></tr>
        <tr>
          <th>所属栏目</th>
          <td id="load_catid"></td>
        </tr>
        <tr>
          <th>排序</th>
          <td><input type="text" name="info[listorder]" id="listorder" class="input" size="5" value="{$listorder}"/></td>
        </tr>
        <tr>
          <th>最大保存条数</th>
          <td><input type="text" name="info[maxnum]" id="maxnum" class="input" size="5" value="{$maxnum}"/>
            条</td>
        </tr>
      </table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">修改</button>
        <input name="info[posid]" value="{$posid}" type="hidden" />
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script type="text/javascript">
$(function(){
	category_load();
});
function category_load(obj){
	var modelid = '';
	$('input[name="info[modelid][]"]').each(function() {
		if ($(this).attr("checked")) {
			if(modelid){
				modelid += ','+$(this).val();
			}else{
				modelid = $(this).val();
			}
		}
	});
	$.get(GV.DIMAUB+'index.php?a=public_category_load&m=Position&g=Content&modelid='+modelid+'&catid={$catid}',function(data){
		$('#load_catid').html(data);
	});
}
</script>
</body>
</html>
