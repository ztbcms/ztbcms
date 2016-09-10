<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="h_a">新建发布方案</div>
  <form name="myform" action="{$config_siteurl}index.php" method="get" >
    <input type="hidden" name="g" value="Collection">
	<input type="hidden" name="m" value="Node">
	<input type="hidden" name="a" value="import_program_add">
	<input type="hidden" name="nodeid" value="{$nodeid}">
	<input type="hidden" name="type" value="{$type}">
	<input type="hidden" name="ids" value="{$ids}">
    <div class="table_full">
    <table width="100%" class="table_form">
		<tr>
			<th width="120">栏目：</th> 
			<td><select name="catid">{$select_categorys}</select></td>
		</tr>
	</table>
    </div>
     <div class="">
      <button type="submit" class="btn btn_submit  mr10">提交</button>
    </div>
  </form>
  <div class="bk10"></div>
  <div class="h_a">发布方案列表</div>
  <form name="myform" action="{$config_siteurl}index.php" method="get" class="J_ajaxForm">
    <input type="hidden" name="g" value="Collection">
	<input type="hidden" name="m" value="Node">
	<input type="hidden" name="a" value="import_content">
	<input type="hidden" name="nodeid" value="{$nodeid}">
	<input type="hidden" name="type" value="{$type}">
	<input type="hidden" name="ids" value="{$ids}">
    <div class="table_full">
    <table width="100%" class="table_form">
		<volist name="program_list" id="vo">
        <tr>
			<th width="200"><label><input type="radio" name="programid" value="{$vo.id}">{$vo.name}</label></th> 
			<td>栏目：{$catlist[$vo['catid']]['catname']} &nbsp;&nbsp;&nbsp;&nbsp;操作：<a class="J_ajax_del" href="{:U("Node/import_program_del",array("id"=>$vo['id']))}">删除</a> | <a href="{:U("Node/import_program_edit",array("id"=>$vo['id'],'nodeid'=>$nodeid,'type'=>$type,'ids'=>$ids   ))}">编辑</a></td>
		</tr>
        </volist>
	</table>
    </div>
     <div class="">
      <button type="submit" class="btn btn_submit mr10">提交</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
