<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form name="myform" class="J_ajaxForm" action="{:U('Group/delete')}" method="post">
  <div class="table_list">
  <table width="100%" cellspacing="0">
	<thead>
		<tr>
			<td align="left" width="30px"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></td>
			<td align="left">ID</td>
			<td align="center">排序</td>
			<td align="center">用户组名</td>
			<td align="center">系统组</td>
			<td align="center">会员数</td>
			<td align="center">星星数</td>
			<td align="center">积分小于</td>
			<td align="center">允许上传附件</td>
			<td align="center">投稿权限</td>
			<td align="center">投稿不需审核</td>
			<td align="center">搜索权限</td>
			<td align="center">自助升级</td>
			<td align="center">发短消息</td>
			<td align="center">操作</td>
		</tr>
	</thead>
<tbody>
<volist name="data" id="vo">
    <tr>
		<td align="left"><if condition=" $vo['issystem'] neq '1' "> <input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x"  value="{$vo.groupid}" name="groupid[]"></if></td>
		<td align="left">{$vo.groupid}</td>
		<td align="center"><if condition=" $vo['issystem'] neq '1' "><input type="text" name="sort[{$vo.groupid}]" class="input" size="1" value="{$vo.sort}"></if></th>
		<td align="center" title="">{$vo.name}</td>
		<td align="center"><font color="red">√</font></td>
		<td align="center">{$vo._count}</th>
		<td align="center">{$vo.starnum}</td>
		<td align="center">{$vo.point}</td>
		<td align="center"><if condition=" $vo['allowattachment'] eq '0' "><font color="blue">×</font><else /><font color="red">√</font></if></td>
		<td align="center"><if condition=" $vo['allowpost'] eq '0' "><font color="blue">×</font><else /><font color="red">√</font></if></td>
		<td align="center"><if condition=" $vo['allowpostverify'] eq '0' "><font color="blue">×</font><else /><font color="red">√</font></if></td>
		<td align="center"><if condition=" $vo['allowsearch'] eq '0' "><font color="blue">×</font><else /><font color="red">√</font></if></td>
		<td align="center"><if condition=" $vo['allowupgrade'] eq '0' "><font color="blue">×</font><else /><font color="red">√</font></if></td>
		<td align="center"><if condition=" $vo['allowsendmessage'] eq '0' "><font color="blue">×</font><else /><font color="red">√</font></if></td>
		<td align="center"><a href="{:U('Group/edit', array('groupid'=>$vo['groupid']) )}">[修改]</a></td>
    </tr>
</volist>
</tbody>
 </table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" data-action="{:U('Group/sort')}" type="submit">排序</button>
        <button class="btn  mr10 J_ajax_submit_btn" type="submit">删除</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
