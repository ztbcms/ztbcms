<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form name="myform" action="{:U('Group/add')}" method="post" class="J_ajaxForm">
  <div class="table_full">
  <div class="h_a">基本信息</div>
  <table width="100%" class="table_form">
		<tr>
			<th width="120">会员组名称</th> 
			<td><input type="text" name="name"  class="input" id="name" value=""></td>
		</tr>
		<tr>
			<th>积分小于</th> 
			<td>
			<input type="text" name="point" class="input" id="group_point" value="" size="6"></td>
		</tr>
		<tr>
			<th>星星数</th> 
			<td><input type="text" name="starnum" class="input" id="group_starnum" size="6" value=""></td>
		</tr>
	</table>
    <div class="h_a">详细信息</div>
    <table width="100%" class="table_form">
		<tr>
			<th width="120">用户权限</th> 
			<td>
				<span class="ik lf" style="width:120px;">
					<input type="checkbox" name="allowpost" value="1">
					允许投稿				</span>
				<span class="ik lf" style="width:120px;">
					<input type="checkbox" name="allowpostverify" value="1">
					投稿不需审核				</span>
				<span class="ik lf" style="width:120px;">
					<input type="checkbox" name="allowupgrade" value="1">
					允许自助升级 
				</span>
				<span class="ik lf" style="width:120px;">
					<input type="checkbox" name="allowsendmessage" value="1">
					允许发短消息 
				</span>	
				<span class="ik lf" style="width:120px;">
					<input type="checkbox" name="allowattachment" value="1">
					允许上传附件 
				</span>
				<span class="ik lf" style="width:120px;">
					<input type="checkbox" name="allowsearch" value="1">
					搜索权限 
				</span>
			</td>

		</tr>
		<tr>
			<th width="80">最大短消息数</th> 
			<td><input type="text" name="allowmessage" class="input" id="allowmessage" size="8" value=""></td>
		</tr>
		<tr>
			<th width="80">日最大投稿数</th> 
			<td><input type="text" name="allowpostnum" class="input" id="allowpostnum" size="8" value=""> 0为不限制</td>
		</tr>
		<tr>
			<th width="80">用户名颜色</th> 
			<td><span class="color_pick J_color_pick"><em style="background:;" class="J_bg"></em></span> 
            <input type="text" name="usernamecolor" class="input J_hidden_color" id="usernamecolor" size="8" value="">
            </td>
		</tr>
		<tr>
			<th width="80">用户组图标</th> 
			<td><?php echo Form::images("icon","icon","","Member"); ?></td>
		</tr>
		<tr>
			<th width="80">简洁描述</th> 
			<td><input type="text" name="description" class="input" size="60" value=""></td>
		</tr>
        <tr>
			<th width="80">可以上传照片总数</th> 
			<td><input type="text" name="expand[upphotomax]" class="input" id="upphotomax" size="8" value="200"> 0 为不允许上传</td>
		</tr>
        <tr>
			<th width="80">是否可以发送短信息</th> 
			<td><input type="checkbox" name="expand[ismsg]" value="1"></td>
		</tr>
        <tr>
			<th width="80">是否可以留言</th> 
			<td><input type="checkbox" name="expand[iswall]" value="1"></td>
		</tr>
        <tr>
			<th width="80">是否可以关注用户</th> 
			<td><input type="checkbox" name="expand[isrelatio]" value="1"></td>
		</tr>
        <tr>
			<th width="80">是否可以添加收藏</th> 
			<td><input type="checkbox" name="expand[isfavorite]" value="1"></td>
		</tr>
        <tr>
			<th width="80">是否可以发表微博</th> 
			<td><input type="checkbox" name="expand[isweibo]" value="1"></td>
		</tr>
	</table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>
</body>
</html>
