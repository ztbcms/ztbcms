 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">附件配置</div>
  <div class="table_full">
    <form method='post'   id="myform" class="J_ajaxForm"  action="{:U('Config/attach')}">
      <table cellpadding=0 cellspacing=0 width="100%" class="table_form" >
      <tr>
        <th width="140">网站存储方案:</th>
        <th><?php echo \Form::select($dirverList,$Site['attachment_driver'],'name="attachment_driver"');  ?> <em>存储方案请放在 Libs/Driver/Attachment/ 目录下</em></th>
      </tr>
      <tr>
        <th width="140">允许上传附件大小:</th>
        <th><input type="text" class="input" name="uploadmaxsize" id="uploadmaxsize" size="10" value="{$Site.uploadmaxsize}"/>
          <span class="gray">KB</span></th>
      </tr>
      <tr>
        <th width="140">允许上传附件类型:</th>
        <th><input type="text" class="input" name="uploadallowext" id="uploadallowext" size="50" value="{$Site.uploadallowext}"/>
        <span class="gray">多个用"|"隔开</span></th>
      </tr>
       <tr >
        <th width="140">前台允许上传附件大小:</th>
        <th><input type="text" class="input" name="qtuploadmaxsize" id="uploadmaxsize" size="10" value="{$Site.qtuploadmaxsize}"/>
          <span class="gray">KB</span></th>
      </tr>
      <tr >
        <th width="140">前台允许上传附件类型:</th>
        <th><input type="text" class="input" name="qtuploadallowext" id="uploadallowext" size="50" value="{$Site.qtuploadallowext}"/>
        <span class="gray">多个用"|"隔开</span></th>
      </tr>
      <tr>
        <th width="140">保存远程图片过滤域名:</th>
        <th><input type="text" class="input" name="fileexclude" id="fileexclude" style="width:314px;" value="{$Site.fileexclude}"/>
        <span class="gray">多个用"|"隔开，域名以"/"结尾，例如：http://www.ztbcms.com/</span></th>
      </tr>
      <tr>
        <th width="140">FTP服务器地址:</th>
        <th><input type="text" class="input" name="ftphost" id="ftphost" size="30" value="{$Site.ftphost}"/> FTP服务器端口: <input type="text" class="input" name="ftpport" id="ftpport" size="5" value="{$Site.ftpport}"/></th>
      </tr>
      <tr>
        <th width="140">FTP上传目录:</th>
        <th><input type="text" class="input" name="ftpuppat" id="ftpuppat" size="30" value="{$Site.ftpuppat}"/> 
        <span class="gray">"/"表示上传到FTP根目录</span></th>
      </tr>
      <tr>
        <th width="140">FTP用户名:</th>
        <th><input type="text" class="input" name="ftpuser" id="ftpuser" size="20" value="{$Site.ftpuser}"/></th>
      </tr>
      <tr>
        <th width="140">FTP密码:</th>
        <th><input type="password" class="input" name="ftppassword" id="ftppassword" size="20" value="{$Site.ftppassword}"/></th>
      </tr>
      <tr>
        <th width="140">FTP是否开启被动模式:</th>
        <th><input name="ftppasv" type="radio" value="1"  <if condition=" $Site['ftppasv'] == '1' ">checked</if> /> 开启 <input name="ftppasv" type="radio" value="0" <if condition=" $Site['ftppasv'] == '0' ">checked</if> /> 关闭</th>
      </tr>
      <tr>
        <th width="140">FTP是否使用SSL连接:</th>
        <th><input name="ftpssl" type="radio" value="1"  <if condition=" $Site['ftpssl'] == '1' ">checked</if> /> 开启 <input name="ftpssl" type="radio" value="0" <if condition=" $Site['ftpssl'] == '0' ">checked</if> /> 关闭</th>
      </tr>
      <tr>
        <th width="140">FTP超时时间:</th>
        <th><input type="text" class="input" name="ftptimeout" id="ftptimeout" size="5" value="{$Site.ftptimeout}"/>
        <span class="gray">秒</span></th>
      </tr>
      <tr>
        <th width="140">是否开启图片水印:</th>
        <th><input class="radio_style" name="watermarkenable" value="1" <if condition="$Site['watermarkenable'] eq '1' "> checked</if> type="radio">
          启用&nbsp;&nbsp;&nbsp;&nbsp;
          <input class="radio_style" name="watermarkenable" value="0" <if condition="$Site['watermarkenable'] eq '0' "> checked</if>  type="radio">
          关闭 </th>
      </tr>
      <tr>
        <th width="140">水印添加条件:</th>
        <th>宽
          <input type="text" class="input" name="watermarkminwidth" id="watermarkminwidth" size="10" value="{$Site.watermarkminwidth}" />
          X 高
          <input type="text" class="input" name="watermarkminheight" id="watermarkminheight" size="10" value="{$Site.watermarkminheight}" />
          PX</th>
      </tr>
      <tr>
        <th width="140">水印图片:</th>
        <th><input type="text" name="watermarkimg" id="watermarkimg" class="input" size="30" value="{$Site.watermarkimg}"/>
          <span class="gray">水印存放路径从网站根目录起</span></th>
      </tr>
      <tr>
        <th width="140">水印透明度:</th>
        <th><input type="text" class="input" name="watermarkpct" id="watermarkpct" size="10" value="{$Site.watermarkpct}" />
          <span class="gray">请设置为0-100之间的数字，0代表完全透明，100代表不透明</span></th>
      </tr>
      <tr>
        <th width="140">JPEG 水印质量:</th>
        <th><input type="text" class="input" name="watermarkquality" id="watermarkquality" size="10" value="{$Site.watermarkquality}" />
          <span class="gray">水印质量请设置为0-100之间的数字,决定 jpg 格式图片的质量</span></th>
      </tr>
      <tr>
        <th width="140">水印位置:</th>
        <th>
        <div class="locate">
						<ul class="cc" id="J_locate_list">
							<li class="<if condition="$Site['watermarkpos'] eq '1' "> current</if>"><a href="" data-value="1">左上</a></li>
							<li class="<if condition="$Site['watermarkpos'] eq '2' "> current</if>"><a href="" data-value="2">中上</a></li>
							<li class="<if condition="$Site['watermarkpos'] eq '3' "> current</if>"><a href="" data-value="3">右上</a></li>
							<li class="<if condition="$Site['watermarkpos'] eq '4' "> current</if>"><a href="" data-value="4">左中</a></li>
							<li class="<if condition="$Site['watermarkpos'] eq '5' "> current</if>"><a href="" data-value="5">中心</a></li>
							<li class="<if condition="$Site['watermarkpos'] eq '6' "> current</if>"><a href="" data-value="6">右中</a></li>
							<li class="<if condition="$Site['watermarkpos'] eq '7' "> current</if>"><a href="" data-value="7">左下</a></li>
							<li class="<if condition="$Site['watermarkpos'] eq '8' "> current</if>"><a href="" data-value="8">中下</a></li>
							<li class="<if condition="$Site['watermarkpos'] eq '9' "> current</if>"><a href="" data-value="9">右下</a></li>
						</ul>
						<input id="J_locate_input" name="watermarkpos" type="hidden" value="{$Site.watermarkpos}">
					</div>
        </th>
      </tr>
    </table>
      <div class="btn_wrap">
        <div class="btn_wrap_pd">
          <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
$(function(){
	//水印位置
	$('#J_locate_list > li > a').click(function(e){
		e.preventDefault();
		var $this = $(this);
		$this.parents('li').addClass('current').siblings('.current').removeClass('current');
		$('#J_locate_input').val($this.data('value'));
	});
});
</script>
</body>
</html>
