<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form action="{:U("Rbac/setting_cat_priv")}" method="post" class="J_ajaxForm">
    <div class="h_a">栏目权限</div>
    <div class="table_full"> 
    <table width="100%">
        <tr>
          <th width="100" align="center"><label>全选<input type="checkbox" onclick="select_all(0, this)"></label></th>
          <th align="left">栏目名称</th>
          <th width="50" align="center">查看</th>
          <th width="50" align="center">添加</th>
          <th width="50" align="center">修改</th>
          <th width="50" align="center">删除</th>
          <th width="50" align="center">排序</th>
          <th width="50" align="center">推送</th>
          <th width="50" align="center">移动</th>
        </tr>
      {$categorys}
    </table>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <input type="hidden" name="roleid" value="{$roleid}" />
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript">
function select_all(name, obj) {
    if (obj.checked) {
        if (name == 0) {
			$.each($("input[type='checkbox']"),function(i,rs){
				if($(this).attr('disabled') != 'disabled'){
					$(this).attr('checked', 'checked');
				}
			});
            //$("input[type='checkbox']").attr('checked', 'checked');
        } else {
			$.each($("input[type='checkbox'][name='priv[" + name + "][]']"),function(i,rs){
				if($(this).attr('disabled') != 'disabled'){
					$(this).attr('checked', 'checked');
				}
			});
            //$("input[type='checkbox'][name='priv[" + name + "][]']").attr('checked', 'checked');
        }
    } else {
        if (name == 0) {
            $("input[type='checkbox']").attr('checked', null);
        } else {
            $("input[type='checkbox'][name='priv[" + name + "][]']").attr('checked', null);
        }
    }
}
</script>
</body>
</html>
