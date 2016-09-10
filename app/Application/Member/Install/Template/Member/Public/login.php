<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>会员登录 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/passport.css" />
</head>
<body>
<template file="Member/Public/regHeader.php"/>
<div class="login_body">
  <div class="login_title">会员登录</div>
  <div class="login_box">
    <div class="login_box_left">
      <form id="form1" action="{:U('Public/doLogin')}" method="post">
      <input id="refer" type="hidden" value="{$forward}" />
        <div class="center"><span id="errMessage"></span></div>
        <div class="site">
          <div class="info_list">
            <div class="username">
              <input class="input" type="text" value=""name="loginName" maxlength="128" id="loginName">
            </div>
          </div>
          <div class="info_list">
            <div class="password">
              <input class="input" type="password" value=""name="password" maxlength="128" id="password">
            </div>
          </div>
          <if condition=" $Member_config['openverification'] ">
          <div class="info_list">
            <div id="loginName" class="input">
              <input id="vCode" class="input_normal" type="text" style="width:81px;" name="vCode" maxlength="4" value="请输入验证码">
            </div>
            <div class="vcode">
              <div class="noleft"> <img id="authCode" align="absmiddle" title="看不清？点击更换" src="{:U("Api/Checkcode/index","type=userlogin&code_len=4&font_size=14&width=80&height=24&font_color=&background=")}"> </div>
              <div class="reloadCode"> <a id="changeAuthCode" href="javascript:;">看不清？换一张</a> </div>
            </div>
          </div>
          </if>
          <div class="info_list">
            <div class="right"> <a href="{:U('Member/Index/lostpassword')}">忘记密码?</a> </div>
            <label><input id="setCookieTime" class="check_box" type="checkbox" name="cookieTime">下次自动登录 </label>
          </div>
          <div class="style">
            <input class="home_btn" type="submit" id="submit2" value="登 陆">
          </div>
        </div>
      </form>
    </div>
    <div  class="login_box_right">
      <div class="title">您还不是网站用户？</div>
      <div class="reg">
        <p>注册后，即可享受更多服务！</p>
        <a href="{:U('Index/register')}" class="home_btn"></a> </div>
      <div class="partner">
        <p class="tit">使用其他账号登录:</p>
        <if condition=" $Member_config['qq_akey'] && $Member_config['qq_skey'] ">
        <a href="{:U('Connectqq/index')}" class="qq_login"> </a>
        </if>
        <if condition=" $Member_config['sinawb_akey'] && $Member_config['sinawb_skey'] ">
        <a href="{:U('Connectsina/index')}" class="siina_weibo"> </a> 
        </if>
      </div>
      <ul class="clearfix">
        <li>邂逅各个城市夜店中美丽的女孩</li>
        <li>和你身边的美女帅哥成为朋友</li>
        <li>用照片和说说记录生活，展示自我</li>
        <li>寻找音乐知音，了解他们的最新动态</li>
        <li>找到喜欢的专区，参加有趣的活动</li>
        <li>与大家分享生活乐趣</li>
      </ul>
    </div>
  </div>
</div>
<template file="Member/Public/regBottom.php"/>
<script type="text/javascript">
	user.loginInit(1);
	user.loginCss();
	</script>
</body>
</html>
