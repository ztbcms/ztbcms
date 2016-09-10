<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <p>如果您已经修改模型字段管理中“<font color="#FF0000">在推荐位标签中调用</font>”这个选项，可以使用“<font color="#FF0000">数据重建</font>”功能进行数据重建！</p>
  </div>
  <div class="table_list">
    <table width="100%" cellspacing="0">
      <thead>
        <tr>
          <td width="50">排序</td>
          <td width="50"  align="center">ID</td>
          <td>推荐位名称</td>
          <td width="100" align="center">所属栏目</td>
          <td width="100" align="center">所属模型</td>
          <td width="240" align="center">管理操作</td>
        </tr>
      </thead>
      <tbody>
        <volist name="data" id="vo">
          <tr>
            <td><input name='listorders[{$vo.posid}]' type='text' size='2' value='{$vo.listorder}' class="input"></td>
            <td align="center">{$vo.posid}</td>
            <td>{$vo.name}</td>
            <td align="center">
            <if condition=" empty($vo['catid']) ">
            <font color="#FF0000">无限制</font>
            <else />
            多栏目
            </if>
            </td>
            <td align="center">
            <if condition=" empty($vo['modelid']) ">
            <font color="#FF0000">无限制</font>
            <else />
            多模型
            </if>
            </td>
            <td align="center">
            <?php
		  $op = array();
		  if(\Libs\System\RBAC::authenticate('item')){
			  $op[] =  '<a href="'.U('Position/item',array('posid'=>$vo['posid'])).'">信息管理</a>';
		  }
		   if(\Libs\System\RBAC::authenticate('rebuilding')){
			  $op[] =  '<a href="'.U('Position/rebuilding',array('posid'=>$vo['posid'])).'">数据重建</a>';
		  }
		  if(\Libs\System\RBAC::authenticate('edit')){
			  $op[] = '<a href="'.U('Position/edit',array('posid'=>$vo['posid'])).'">修改</a>';
		  }
		  if(\Libs\System\RBAC::authenticate('delete')){
			  $op[] = '<a class="J_ajax_del" href="'.U('Position/delete',array('posid'=>$vo['posid'])).'">删除</a>';
		  }
		  echo implode(" | ",$op);
		  ?>
          </tr>
        </volist>
      </tbody>
    </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
