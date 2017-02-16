
<Admintemplate file="Common/Head"/>
<style>
.logs li { line-height:25px;}
</style>
<body>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">在线升级</div>
  <div class="prompt_text logs" id="record">
    <ul>
      <li>开始执行升级....</li>
      <li>开始检查目录权限和下载升级包....</li>
    </ul>
  </div>
  <div class="loading">loading...</div>
  <div class="btn_wrap1" style="display:none">
      <div class="btn_wrap_pd">             
        <input type="hidden" name="id" value="53">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit" id="cloud_button">完成升级</button>
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
				$('#cloud_button').html('完成升级');
				$('div.loading').hide();
				$('button.J_ajax_submit_btn').click(function(){
					window.location.href = '{:U("index")}';
				});
				$('.btn_wrap1').show();
				$('#record ul').append('<li>升级结束！</li>');
			}
		}else{
			$('#record ul').append('<li style="color:#F00">'+data.info+'</li>');
			$('div.loading').hide();
			$('#cloud_button').html('重新升级');
			$('button.J_ajax_submit_btn').click(function(){
				location.reload();
			});
			$('.btn_wrap1').show();
		}
	});
}
function step(url){
	getStep(url);
}
$(function(){
	step('{$stepUrl}');
});
</script>
</body>
</html>