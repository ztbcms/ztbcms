<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="table_list">
  <table width="100%" cellspacing="0" >
    <thead>
      <tr>
        <td width="80" align="center">ModelID</td>
        <td width="150" align="center">模型名称</td>
        <td width="200" align="center">数据表</td>
        <td  align="center">描述</td>
        <td width="100" align="center">状态</td>
        <td width="300" align="center">管理操作</td>
      </tr>
    </thead>
    <tbody>
    <volist name="data" id="vo">
      <tr>
        <td align='center'>{$vo.modelid}</td>
        <td align='center'>{$vo.name}</td>
        <td align='center'>{$vo.tablename}</td>
        <td align='center'>{$vo.description}</td>
        <td align='center'><font color="red"><if condition="$vo['disabled'] eq '1' ">╳<else />√</if></font></td>
        <td align='center'>
        <?php
		$operate = array();
		if(\Libs\System\RBAC::authenticate('edit')){
			$operate[] = '<a href="'.U("edit",array("modelid"=>$vo['modelid'])).'">修改</a>';
		}
		if(\Libs\System\RBAC::authenticate('Field/index')){
			$operate[] = '<a href="'.U("Field/index",array("modelid"=>$vo['modelid'])).'">字段管理</a>';
		}
		if(\Libs\System\RBAC::authenticate('disabled')){
			if($vo['disabled'] == 0){
				$operate[] = '<a href="'.U("disabled",array("modelid"=>$vo['modelid'],"disabled"=>0)).'">禁用</a>';
			}else{
				$operate[] = '<a href="'.U("disabled",array("modelid"=>$vo['modelid'],"disabled"=>1)).'"><font color="#FF0000">启用</font></a>';
			}
		}
		if(\Libs\System\RBAC::authenticate('delete')){
			$operate[] = '<a class="J_ajax_del" href="'.U("delete",array("modelid"=>$vo['modelid'])).'">删除</a>';
		}
		if(\Libs\System\RBAC::authenticate('export')){
			$operate[] = '<a href="'.U("export",array("modelid"=>$vo['modelid'])).'">导出模型</a>';
		}
		echo implode(' | ',$operate);
		?>
        </td>
      </tr>
    </volist>
    </tbody>
  </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript">

</script>
</body>
</html>
