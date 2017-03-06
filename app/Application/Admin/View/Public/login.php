<?php if (!defined('CMS_VERSION')) exit();?>
<!doctype html>
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="edge" />
<meta charset="utf-8" />
<title>系统后台 - {$Config.sitename} - by ZtbCMS</title>
<meta name="generator" content="ThinkPHP" />
<admintemplate file="Admin/Common/Js"/>
<style type="text/css">
 html{font-size:62.5%;font-family:Tahoma}
body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,button,textarea,p,blockquote,th,td,hr{margin:0;padding:0}
body{line-height:1.333;font-size:12px;font-size:1.2rem}
h1,h2,h3,h4,h5,h6{font-size:100%}
input,textarea,select,button{font-size:12px;font-weight:normal}
input[type="button"],input[type="submit"],select,button{cursor:pointer}
table{border-collapse:collapse;border-spacing:0}
address,caption,cite,code,dfn,em,th,var{font-style:normal;font-weight:normal}
li{list-style:none}
caption,th{text-align:left}
q:before,q:after{content:''}
abbr,acronym{border:0;font-variant:normal}
sup{vertical-align:text-top}
sub{vertical-align:text-bottom}
fieldset,img,a img,iframe{border-width:0;border-style:none}
iframe{overflow:hidden}
img{ -ms-interpolation-mode:bicubic;}
textarea{overflow-y:auto}
legend{color:#000}
a:link,a:visited{text-decoration:none}
hr{height:0}
label{cursor:pointer}
.os_winXp{font-family:Tahoma}
.os_mac{font-family:"Helvetica Neue",Helvetica,"Hiragino Sans GB",Arial}
.os_vista,.os_win7{font-family:"Microsoft Yahei",Tahoma}
.clearfix:before,.clearfix:after{content:".";display:block;height:0;visibility:hidden}
.clearfix:after{clear:both}
.clearfix{zoom:1}
.header,nav,.footer{display:block}
body{background-color:#f0f0f0}
.wrap{background-color:#f5f5f5}
.wrap .inner{width:1000px;margin:0 auto}
iframe{background-color:transparent}
.header{padding:19px 0 0 100px}
.header h1{ background-image:url({$config_siteurl}statics/images/logo.gif);background-repeat:no-repeat;width:227px;height:78px;line-height:150px;overflow:hidden;font-size:0}
.qzone_login{margin-top:55px;min-height: 485px;}
.qzone_login .qzone_cont{float:left;margin-left:112px;position:relative;width:429px;_display:inline;overflow:hidden;height:321px}
.qzone_cont .img_list{width:429px;height:321px}
.qzone_cont .img_list li{width:429px;height:321px;vertical-align:middle;display:table-cell}
.qzone_cont .img_list .img_link{display:block;width:429px;text-align:center;height:321px;outline:none;overflow:hidden}
.qzone_cont .scroll_img_box{margin:40px auto 0;height:16px;float:left}
.qzone_cont .scroll_img{text-align:center;width:429px}
.qzone_cont .scroll_img li{ width:10px;height:10px;background-image:url({$config_siteurl}statics/images/qzone_login.png);background-position:-663px 0;background-repeat:no-repeat;display:inline-block;margin-right:15px;cursor:pointer;*display:inline;*zoom:1;overflow:hidden}
.qzone_cont .scroll_img .current_img{ background-image:url({$config_siteurl}statics/images/admin_img/qzone_login.png);background-position:-663px -17px}
.qzone_login .login_main{margin:10px 0 0 68px;float:left;_display:inline;width:370px;overflow:hidden}
.qzone_login .login_main a{color:#3da5dc}
.login_main .login_list .input_txt{border:1px solid #d9d9d9;border-radius:3px;font-size:16px;font-family:"Microsoft Yahei",Tahoma;height:23px;width:259px;color:#666;padding:14px 0 14px 9px;margin-bottom:20px}
.login_main .login_list .input_txt:focus{outline:0}
.login_main .login_list .current_input{border-color:#56bdf3;box-shadow:inset 0 1px 3px rgba(0,0,0,.2);-webkit-box-shadow:inset 0 1px 3px rgba(0,0,0,.2);-moz-box-shadow:inset 0 1px 3px rgba(0,0,0,.2)}
.login_main .login_list .login_input{position:relative;width:270px;height:73px}
.login_main .login_list .txt_default{position:absolute;font-size:16px;font-family:"Microsoft Yahei",Tahoma;color:#666;top:17px;left:10px;cursor:text}
.login_main .login_list .txt_click{color:#ccc}
.login_main .login_list .yanzhengma{position:relative;color:#666}
.login_main .login_list .yanzhengma .yanzheng_txt{margin-left:2px}
.login_main .login_list .yanzhengma .input_txt{width:139px;margin-bottom:40px}
.login_main .login_list .yanzhengma .yanzhengma_box{position:absolute;left:160px;top:0}
.login_main .login_list .yanzhengma .yanzheng_img{display:block;margin-bottom:10px}
.login_main .login_btn{ width:148px;height:48px;line-height:150px;overflow:hidden;font-size:0;*background:none;background-image:url({$config_siteurl}statics/images/qzone_login.png);background-position:-514px 0;border:none;cursor:pointer}
.qzone_login .login_main nav{color:#d0d3d7;margin:20px 0 0 3px}
.qzone_login .login_main nav .sep{margin:0 12px}
.login_main .quick_login{color:#5a5b5b}
.login_main .wrong_notice{color:red;margin:0 0 10px 1px}
.login_main .login_change{margin:6px 0 0 3px}
.platform_box{margin:94px 0 0 0;width:1000px;padding-bottom:16px}
.platform_box nav{ background-image:url({$config_siteurl}statics/images/qzone_login.png);background-position:0 0;background-repeat:no-repeat;width:370px;height:52px;margin:0 auto}
.platform_box nav .platform_link{width:86px;margin:0 1px;height:52px;line-height:160px;overflow:hidden;display:inline-block;font-size:0;*margin-top:-64px}
.footer{ color:#999;position: absolute;bottom: 0;text-align: center;}
.footer .inner{width:1000px;margin:0 auto;text-align:center;padding:45px 0}
.footer .links{margin-bottom:15px}
.footer .links .sep{margin:0 12px;color:#d0d3d7}
.footer .copyright{width:580px;margin:0 auto}
.footer .copyright_en{float:left;margin-right:15px}
.footer .copyright_ch{float:left}
.footer .copyright_ch .copyright_link{margin-left:5px}
.wrap {
	overflow:hidden;
	-webkit-animation: bounceIn 600ms linear;
	-moz-animation: bounceIn 600ms linear;
	-o-animation: bounceIn 600ms linear;
	animation: bounceIn 600ms linear;
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: url({$config_siteurl}statics/images/login_big_bg.jpg);
	background-size: cover;
}
/*登录框动画*/
@-webkit-keyframes bounceIn {
	0% {
		opacity: 0;
		-webkit-transform: scale(.3);
	}
	50% {
		opacity: 1;
		-webkit-transform: scale(1.05);
	}
	70% {
		-webkit-transform: scale(.9);
	}
	100% {
		-webkit-transform: scale(1);
	}
}
@-moz-keyframes bounceIn {
	0% {
		opacity: 0;
		-moz-transform: scale(.3);
	}
	50% {
		opacity: 1;
		-moz-transform: scale(1.05);
	}
	70% {
		-moz-transform: scale(.9);
	}
	100% {
		-moz-transform: scale(1);
	}
}
@-o-keyframes bounceIn {
	0% {
		opacity: 0;
		-o-transform: scale(.3);
	}
	50% {
		opacity: 1;
		-o-transform: scale(1.05);
	}
	70% {
		-o-transform: scale(.9);
	}
	100% {
		-o-transform: scale(1);
	}
}
@keyframes bounceIn {
	0% {
		opacity: 0;
		transform: scale(.3);
	}
	50% {
		opacity: 1;
		transform: scale(1.05);
	}
	70% {
		transform: scale(.9);
	}
	100% {
		transform: scale(1);
	}
}
</style>
<script type="text/javascript">
if (window.parent !== window.self) {
	document.write = '';
	window.parent.location.href = window.self.location.href;
	setTimeout(function () {
		document.body.innerHTML = '';
	}, 0);
}
</script>

    <script>
        var _hmt = _hmt || [];
        (function() {
            var hm = document.createElement("script");
            hm.src = "https://hm.baidu.com/hm.js?123929b4d143a8384864a99fd4199190";
            var s = document.getElementsByTagName("script")[0];
            s.parentNode.insertBefore(hm, s);
        })();
    </script>

</head>
<body>
<div class="wrap">
  <div class="inner">
    <div class="header">
      <h1>{$Config.sitename}</h1>
    </div>
		<div class="qzone_login clearfix">
      <!-- end qzone_cont -->
      <div class="login_main" style="float: none;margin: 0 auto;text-align: center;background: rgba(34, 34, 34, 0.13);">
        <p class="wrong_notice" id="err_m" style="display:none;"></p>
        <form id="loginform" method="post" name="loginform" action="{:U('Public/tologin')}">
  				<h1 style="font-size: 18px;font-weight: 500;margin: 14px auto;color: white;">{$Config.sitename}</h1>
          <ul class="login_list" id="web_login">
            <li class="login_input" style="margin: 0 auto;">
              <input value="" id="u" name="username" class="input_txt" tabindex="1" type="text" placeholder="帐号名" title="帐号名">
            </li>
            <li class="login_input" style="margin: 0 auto;">
              <input maxlength="16" type="password" id="p" name="password" tabindex="2" class="input_txt" value="" placeholder="密码" title="密码">
            </li>
            <li class="yanzhengma clearfix" id="verifytip"> 
							<span id="verifyinput" style="float: left;margin-left: 47px;">
              	<input id="verifycode" name="code" maxlength="5" tabindex="3" class="input_txt" type="text" value="" placeholder="请输入验证码">
              </span>
              <div class="yanzhengma_box"  style="float: left;left: 0px;position: relative;" id="verifyshow"> <img class="yanzheng_img" id="code_img" alt="" src="{:U('Api/Checkcode/index','code_len=4&font_size=20&width=130&height=50&font_color=&background=')}"><a href="javascript:;;" onclick="refreshs()" class="change_img" style="color: white;">看不清，换一张</a> </div>
            </li>
            <li>
              <button type="submit" class="" tabindex="4" id="subbtn" style="width: 148px;height: 45px;background: #0AE;border: 0;margin: 8px auto 14px;color: white;font-size: 19px;">登 录</button>
            </li>
          </ul>
        </form>
        <div class="quick_login" id="qlogin"> </div>
      </div>
    </div>
    <div class="platform_box"> 
				<div class="inner" style="text-align: center;color: white !important;">
						<div class="copyright clearfix">
							<p class="copyright_en">Copyright © 2016 - 2016 , ZtbCMS All Rights Reserved.</p>
							<p class="copyright_ch"><a href="http://www.ztbcms.com" target="_blank" style="color: white;	">http://www.ztbcms.com</a></p>
						</div>
					</div>
		</div>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script>
//刷新二维码
function refreshs(){
	document.getElementById('code_img').src='{:U('Api/Checkcode/index','code_len=4&font_size=20&width=130&height=50&font_color=&background=&refresh=1')}&time='+Math.random();void(0);
}
$(function(){
	$('#verifycode').focus(function(){
		$('a.change_img').trigger("click");
	});
});
</script>
</body>
</html>
