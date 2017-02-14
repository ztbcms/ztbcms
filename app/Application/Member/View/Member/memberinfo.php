 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="table_full"> 
  <div class="h_a">基本信息</div>
  <table width="100%" class="table_form">
		<tr>
			<th width="80">用户名</th> 
			<td>{$username}</td>
		</tr>
		<tr>
			<th>头像</th> 
			<td><img src="{:getavatar($userid)}" onerror="this.src='{$config_siteurl}statics/images/member/nophoto.gif'" height=90 width=90></td>
		</tr>
		<tr>
			<th>是否审核</th> 
			<td><if condition=" $checked eq '1' "> 审核通过<else />待审核</if></td>
		</tr>
		<tr>
			<th>昵称</th> 
			<td>{$nickname}</td>
		</tr>
		<tr>
			<th>邮箱</th>
			<td>{$email}</td>
		</tr>
		<tr>
			<th>会员组</th>
			<td><?php echo $groupCache[$groupid];?></td>
		</tr>
		<tr>
			<th>积分点数</th>
			<td>{$point}</td>
		</tr>
        <tr>
			<th>钱金总额</th>
			<td>{$amount}</td>
		</tr>
		<tr>
			<th>会员模型</th>
			<td><?php echo $groupsModel[$modelid];?></td>
		</tr>
	</table>
    <div class="h_a"> 详细信息</div>
    <table width="100%" class="table_form">
	<?php foreach($Model_field as $k=>$v) {?>
		<tr>
			<th width="80"><?php echo $v['name']?>：</th> 
			<td><?php echo $output_data[$v['field']]?></td>
		</tr>
	<?php }?>
	</table>
  </div>
</div>
</body>
</html>
