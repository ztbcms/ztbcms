<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<style>
.pop_nav{
	padding: 0px;
}
.pop_nav ul{
	border-bottom:1px solid #266AAE;
	padding:0 5px;
	height:25px;
	clear:both;
}
.pop_nav ul li.current a{
	border:1px solid #266AAE;
	border-bottom:0 none;
	color:#333;
	font-weight:700;
	background:#F3F3F3;
	position:relative;
	border-radius:2px;
	margin-bottom:-1px;
}

</style>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="pop_nav">
    <ul class="J_tabs_navs">
      <li class="<if condition=" !isset($status) ">current</if>"><a href="{:U('Node/publist',  array('nodeid'=>$nodeid) )}">全部</a></li>
      <li class="<if condition=" $status eq 1 ">current</if>"><a href="{:U('Node/publist',  array('nodeid'=>$nodeid,'status'=>1) )}">未采集</a></li>
      <li class="<if condition=" $status eq 2 ">current</if>"><a href="{:U('Node/publist',  array('nodeid'=>$nodeid,'status'=>2) )}">已采集</a></li>
      <li class="<if condition=" $status eq 3 ">current</if>"><a href="{:U('Node/publist',  array('nodeid'=>$nodeid,'status'=>3) )}">已导入</a></li>
    </ul>
  </div>
  <div class="J_tabs_contents">
  <div class="table_full">
    <form name="myform" action="{:U('Node/content_del')}" method="post" class="J_ajaxForm">
    <div class="table_list">
    <input type="hidden" name="nodeid" value="{$nodeid}" />
    <table width="100%">
      <thead>
        <tr>
          <td align="left" width="20"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></td>
          <td align="left" width="50">状态</td>
          <td align="left">标题</td>
          <td align="left">网址</td>
          <td align="left"width="50">操作</td>
        </tr>
      </thead>
      <volist name="data" id="r">
        <tr>
          <td><input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x"  value="{$r.id}" name="id[]" id="{$r.id}"></td>
          <td><switch name="r.status" ><case value="0">未采集</case><case value="1">已采集</case><case value="2">已导入</case></switch></td>
          <td>{$r.title}</td>
          <td>{$r.url}</td>
          <td><a href="javascript:void(0)"  onclick="$('#tab_{$r.id}').toggle()">查看</a></td>
        </tr>
        <tr id="tab_{$r.id}" style="display:none">
          <td align="left" colspan="5">
          <textarea style="width:98%;height:300px;"><?php print_r(unserialize($r['data'])); ?></textarea>
          </td>
        </tr>
      </volist>
    </table>
       <div class="p10">
          <div class="pages">{$Page}</div>
       </div>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button class="btn J_ajax_submit_btn J_submit_btn" type="submit" data-action="{:U('Node/content_del')}">删除</button>
        <button class="btn J_ajax_submit_btn" type="submit" data-action="{:U('Node/content_del','history=1')}">删除和历史记录</button>
        <if condition=" $status eq 2 ">
        <button class="btn J_submit_btn" type="submit" data-action="{:U('Node/import')}">导入选中</button>
        <button class="btn btn_submit J_submit_btn" type="submit" data-action="{:U('Node/import','type=all&nodeid='.$nodeid)}">全部导入</button>
        </if>
      </div>
    </div>
    </form>
  </div>
  </div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript">
$(function(){
	$('button.J_submit_btn').on('click', function(e) {
		var btn = $(this),form = btn.parents('form.J_ajaxForm');
		if(btn.data('action')){
			form.attr("action",btn.data('action'));
		}
	});
});
//复制
function copy_spider(id) {
    Wind.use("artDialog", "iframeTools", function () {
        art.dialog.open(GV.DIMAUB + "index.php?a=copy&m=Node&g=Collection&nodeid=" + id , {
            title: '复制采集节点' ,
            id: 'copy_spider',
            lock: true,
            fixed: true,
            background: "#CCCCCC",
            opacity: 0
        });
    });
}
</script>
</body>
</html>
