<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">
  
  <Admintemplate file="Common/Nav"/>

  <div style="margin: 8px;">
    <a class="btn btn-primary" href="{:U('Cron/Index/index')}" target="_blank">触发定时任务</a>
  </div>
  <div class="table_list">
    <table width="100%">
      <thead>
        <tr>
          <td>计划标题</td>
          <td>执行文件</td>
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
        <td>{$r.cron_file}.class.php</td>
        <td>{$r.type}</td>
        <td>
          <if condition=" $r['isopen'] ">
              <span style="color: green;">开启</span>
            <else />
              <span style="color: red;">关闭</span>
          </if>
        </td>
        <td>{$modified}</td>
        <td>{$next}</td>
        <td>
           <a href="{:U('Cron/edit',array('cron_id'=>$r['cron_id']))}" class="mr5"> 编辑 </a>
          |  <a class="J_ajax_del" href="{:U('Cron/delete',array('cron_id'=>$r['cron_id']))}"> 删除 </a>
          |  <a target="_blank" href="{:U('Cron/runAction',array('cron_id'=>$r['cron_id'], 'cron_file' => $r['cron_file']))}"> 立即执行 </a>
        </td>
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
