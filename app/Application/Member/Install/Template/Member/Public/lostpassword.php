 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>密码找回 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<link rel="shortcut icon" href="/favicon.ico" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/passport.css" />
<style>
/*tip message start*/
.tip_message,.tip_message .tip_ico_succ,.tip_message .tip_ico_fail,.tip_message .tip_ico_hits,.tip_message .tip_content,.tip_message .tip_end{ background-image:url({$model_extresdir}images/tip_message.png);_background-image:url({$model_extresdir}images/tip_message_ie6.png);color:#606060;float:left;font-size:14px;font-weight:bold;height:54px;line-height:54px;}
.tip_message{display:none;background:none;position:absolute;font-family:Arial,Simsun,"Arial Unicode MS",Mingliu,Helvetica;font-size:14px;}
.tip_message .tip_ico_succ
{background-position:-6px 0;background-repeat:no-repeat;width:45px;}
.tip_message .tip_ico_fail{background-position:-6px -108px;background-repeat:no-repeat;width:45px;}
.tip_message .tip_ico_hits{background-position:-6px -54px;background-repeat:no-repeat;width:45px;}
.tip_message .tip_end{background-position:0 0;background-repeat:no-repeat;width:6px;}
.tip_content{background-position:0 -161px;background-repeat:repeat-x;padding:0 20px 0 8px; word-break:keep-all;white-space:nowrap;}
.tip_message .tip_message_content{position:absolute; left:0; top:0; width:100%;height:100%;z-index:65530;}
.ie6_mask{position:absolute; left:0; top:0; width:100%;height:100%;background-color:transparent;z-index:-1;filter:alpha(opacity=0);}
/* tip message end */
</style>
</head>
<body>
<template file="Member/Public/regHeader.php"/>
<div class="retrieve_body">
  <div class="retrieve_title">找回密码</div>
  <div class="retrieve_box">
    <div class="text"><span> 找回您在&nbsp; <strong>{$Config.sitename}</strong> &nbsp;注册的会员账号登录密码。 </span></div>
    <div class="register_box_left">
      <ul id="lostPass">
        <li>
          <div class="rM_title">登录账号</div>
          <div id="rM_loginName" class="rM_input">
            <input id="rloginName" class="input_normal" type="text" maxlength="200" name="rloginName" style="width:190px;"/>
          </div>
          <div id="mloginName"></div>
        </li>
        <li class="vcode">
          <div class="rM_noleft"><img align="absmiddle" src="{:U("Api/Checkcode/index","type=lostpassword&code_len=4&font_size=14&width=100&height=40&font_color=&background=")}" title="看不清？点击更换" id="authCode" /></div>
          <div class="reloadCode"><a href="#" id="changeAuthCode">看不清？换一张</a><span>(不区分大小写.)</span></div>
        </li>
        <li>
          <div class="rM_title">验证码：</div>
          <div id="rM_rvCode" class="rM_input">
            <input id="rvCode" class="input_normal" type="text" maxlength="4" name="rvCode" style="width:66px;"/>
          </div>
          <div id="mvCode"></div>
        </li>
        <li>
          <div class="rM_noleft2"><span class="note">提示：如果您忘记了您的登录账号，请您联系网站管理员。</span></div>
        </li>
        <li>
          <div class="rM_noleft">
            <input id="submit" class="submit" type="submit" value="确定找回密码"/>
          </div>
        </li>
      </ul>
    </div>
  </div>
</div>
<template file="Member/Public/regBottom.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<script type="text/javascript" src="{$model_extresdir}js/lostpass.js"></script>
<script type="text/javascript">
	lostpass.init();
</script>
</body>
</html>
