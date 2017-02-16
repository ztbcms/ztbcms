
<Admintemplate file="Common/Head"/>
<style>
.logs li { line-height:25px;}
</style>
<body>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">使用说明</div>
  <div class="prompt_text" id="explanation">
   <div class="loading">loading...</div>
  </div>
  <div class="h_a">在线安装日志</div>
  <div class="prompt_text logs" id="record">
    <ul>
    </ul>
  </div>
  <div class="loading" style="display:none;">loading...</div>
  <div class="btn_wrap1">
      <div class="btn_wrap_pd">             
        <input type="hidden" name="id" value="53">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit" id="cloud_button" style="display:none">完成安装</button>
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit" id="cloud_start">开始安装</button>
      </div>
    </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script>
//请求
function getStep(url){
	$.getJSON(url,function(data){
		if(data.status){
			$('#record ul').append('<li>'+data.info+'</li>');
			if(data.url){
				step(data.url);
			}else{
				$('#cloud_button').html('完成安装');
				$('div.loading').hide();
				$('button.J_ajax_submit_btn').click(function(){
					window.location.href = '{:U("index")}';
				});
				$('#cloud_button').show();
				$('#record ul').append('<li>安装结束！</li>');
			}
		}else{
			$('#record ul').append('<li style="color:#F00">'+data.info+'</li>');
			$('#cloud_button').html('重新安装');
			$('div.loading').hide();
			$('button.J_ajax_submit_btn').click(function(){
				location.reload();
			});
			$('#cloud_button').show();
		}
	});
}
//获取使用说明
function getExplanation(sign){
	$('#record ul').append('<li>获取插件安装使用说明....</li>');
	$.getJSON('{:U("public_explanation")}',{ sign:sign },function(data){
		if(data.status){
			$('#record ul').append('<li>获取插件安装使用说明成功....</li>');
			$('#explanation').html(data.data);
		}else{
			$('#explanation').html('<p>暂无说明</p>');
		}
	});
}
function step(url){
	$('div.loading').show();
	getStep(url);
}
$(function(){
	getExplanation('{$sign}');
	$('#cloud_start').click(function(){
		$(this).hide();
		$('#record ul').append('<li>开始执行安装....</li>');
		$('#record ul').append('<li>开始检查目录权限和下载安装包....</li>');
		step('{$stepUrl}');
	});
});
</script>
</body>
</html>