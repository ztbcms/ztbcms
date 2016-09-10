<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="table_list">
  <table width="100%" cellspacing="0" >
        <thead>
          <tr>
            <td width="38">ID</td>
            <td >分类名称</td>
            <td align='center' width="120">管理操作</td>
          </tr>
        </thead>
        <tbody>
		<volist name="data" id="vo">
          <tr>
	       <td align='center'>{$vo.id}</td>
	       <td align='center'>{$vo.name}</td>
	       <td align='center'><a href="{:U('Links/termsedit',array('id'=>$vo['id']) )}">编辑</a> | <a href="javascript:confirmurl('{:U('Links/termsdelete',array('id'=>$vo['id']) )}','确认要删除吗？')">删除</a></td>
	      </tr>
		</volist>
        </tbody>
      </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
