<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">添加会员</div>
  <form name="myform" action="{:U('Member/add')}" method="post" class="J_ajaxForm">
  <div class="table_full">
  <table width="100%" class="table_form">
		<tr>
			<th width="80">用户名</th> 
			<td><input type="text" name="username"  class="input" id="username"/></td>
		</tr>
		<tr>
			<th>是否审核</th> 
			<td><input name="checked" type="radio" value="1" checked  />审核通过 <label class="type"><input name="checked" type="radio" value="0"   />待审核</td>
		</tr>
		<tr>
			<th>密码</th> 
			<td><input type="password" name="password" class="input" id="password" value=""/></td>
		</tr>
		<tr>
			<th>确认密码</th> 
			<td><input type="password" name="pwdconfirm" class="input" id="pwdconfirm" value=""/></td>
		</tr>
		<tr>
			<th>昵称</th> 
			<td><input type="text" name="nickname" id="nickname" value="" class="input"/></td>
		</tr>
		<tr>
			<th>邮箱</th>
			<td>
			<input type="text" name="email" value="" class="input" id="email" size="30"/>
			</td>
		</tr>
		<tr>
			<th>会员组</th>
			<td><?php echo \Form::select($groupCache, I('get.groupid',0,'intval'), 'name="groupid"', '') ?></td>
		</tr>
		<tr>
			<th>积分点数</th>
			<td><input type="text" name="point" value="0" class="input" id="point" size="10"/><span> 请输入积分点数，积分点数将影响会员用户组</span></td>
		</tr>
		<tr>
			<th>会员模型</th>
			<td><?php echo \Form::select($groupsModel, 0, 'name="modelid"', ''); ?></td>
		</tr>
	</table>
  </div>
  </form>
</div>
</body>
</html>
