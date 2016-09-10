<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">
  <div class="h_a">功能说明</div>
  <div class="prompt_text">
    <ol>
      <li>计划任务是一项使系统在规定时间自动执行某些特定任务的功能。</li>
      <li>合理设置执行时间，能有效地为服务器减轻负担。</li>
      <li>触发任务除系统指定的时间外，用户行为也可触发。触发任务的任务周期只是初始值。</li>
      <li>想要计划任务顺利执行，需要一个触发媒介！ <br />独立主机用户可以在系统增加计划任务间隔20秒执行访问[http://网站地址/index.php?g=Cron&m=Index&a=index]。<br />虚拟主机用户，需要在网站模板中最底部增加一个js调用[&lt;script type="text/javascript" src="http://网站地址/index.php?g=Cron&m=Index&a=index"&gt;&lt;/script&gt;]以游客访问页面的形式触发！</li>
    </ol>
  </div>
  <Admintemplate file="Common/Nav"/>
  <div class="table_list">
    <table width="100%">
      <thead>
        <tr>
          <td>计划标题</td>
          <td>任务周期</td>
          <td>任务状态</td>
          <td>上次执行时间</td>
          <td>下次执行时间</td>
          <td>操作</td>
        </tr>
      </thead>
      <volist name="data" id="r">
      <?php
	  $modified = $r['modified_time'] ? date("Y-m-d H:i",$r['modified_time']) : '-';
	  $next = $r['next_time'] ? date("Y-m-d H:i",$r['next_time']) : '-';
	  ?>
      <tr>
        <td>{$r.subject}</td>
        <td>{$r.type}</td>
        <td><if condition=" $r['isopen'] ">开启 <else />关闭</if></td>
        <td>{$modified}</td>
        <td>{$next}</td>
        <td>
        <a href="{:U('Cron/edit',array('cron_id'=>$r['cron_id']))}" class="mr5">[编辑]</a> 
        <a class="J_ajax_del" href="{:U('Cron/delete',array('cron_id'=>$r['cron_id']))}">[删除]</a>
      </tr>
      </volist>
    </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script> 
<script>
$(function(){
	$('#J_time_select').on('change', function(){
		$('#J_time_'+ $(this).val()).show().siblings('.J_time_item').hide();
	});

	var lock = false;
	$('a.J_cron_back').on('click', function(e){
		e.preventDefault();
		var $this = $(this);
		if(lock) {
			return false;
		}
		lock = true;

		$.post(this.href, function(data) {
			lock = false;
			if(data.state === 'success') {
				$( '<span class="tips_success fr">' + data.message + '</span>' ).insertAfter($this).fadeIn( 'fast' );
				reloadPage(window);
			}else if( data.state === 'fail' ) {
				Wind.dialog.alert(data.message);
			}
		}, 'json');
	});
});
</script>
</body>
</html>
