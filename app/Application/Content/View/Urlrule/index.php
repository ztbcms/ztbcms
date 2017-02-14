 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="table_list">
  <table width="100%" cellspacing="0" >
      <thead>
        <tr>
          <td width="80">ID</td>
          <td width="80">所属模块</td>
          <td width="80">名称</td>
          <td width="80">生成静态</td>
          <td>URL示例</td>
          <td>URL规则</td>
          <td align='center'>管理操作</td>
        </tr>
      </thead>
      <tbody>
        <volist name="info" id="r">
        <tr>
          <td>{$r.urlruleid}</td>
          <td>{$Module[$r['module']]['name']}</td>
          <td>{$r.file}</td>
          <td><if condition="$r['ishtml']"><font color="red">√</font><else /><font color="blue">×</font></if></td>
          <td>{$r.example}</td>
          <td>{$r.urlrule}</td>
          <td align='center'>
          <?php
		  $op = array();
		  if(\Libs\System\RBAC::authenticate('edit')){
			  $op[] =  '<a href="'.U('Urlrule/edit',array('urlruleid'=>$r['urlruleid'])).'">编辑</a>';
		  }
		  if(\Libs\System\RBAC::authenticate('delete')){
			  $op[] = '<a class="J_ajax_del" href="'.U('Urlrule/delete',array('urlruleid'=>$r['urlruleid'])).'">删除</a>';
		  }
		  echo implode(" | ",$op);
		  ?>
          </td>
        </tr>
        </volist>
      </tbody>
    </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
