<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
<title>新浪微博连接 - {$Config.sitename}</title>
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
<div class="bind_body">
  <div class="bind_box">
    <div class="regMain" id="regMain">
      <div class="register_box_left">
        <div class="option"><span id="existing" class="on"><a href="javascript:;" onclick="$('#qqBinding').show();$('#regData').hide();$('#existing').addClass('on');$('#not').removeClass('on');">已有账号</a></span><span id="not"><a href="javascript:;" onclick="$('#regData').show();$('#qqBinding').hide();$('#not').addClass('on');$('#existing').removeClass('on');">没有账号</a></span></div>
        <div class="title">
          <div class="name">HI, <b>{$connect.userinfo.screen_name}</b>, 现在您可以用新浪微博连接 {$Config.sitename} 了!</div>
        </div>
        <ul id="qqBinding">
          <form onSubmit="return false;">
            <li><span  style="font-size:14px;color:#333;">将已有的账号与您的 新浪微博 进行绑定！</span></li>
            <li>
              <div class="rM_title">登录账号：</div>
              <div class="rM_input" >
                <input type="text" style="width:190px;" class="input_normal" name="rqloginName" id="rqloginName" maxlength="18" />
              </div>
              <div id="mqloginName"></div>
            </li>
            <li>
              <div class="rM_title">登录密码：</div>
              <div class="rM_input" id="rM_rpassword">
                <input type="password" style="width:190px;" class="input_normal" name="rqpassword" id="rqpassword" maxlength="20"/>
              </div>
              <div id="mqpassword"></div>
            </li>
            <li>
              <div class="rM_noleft" >
                <input type="submit" class="input_register" value="确定绑定账号" id="binding" />
              </div>
            </li>
          </form>
        </ul>
        <ul  id="regData" style="display:none;">
          <form onSubmit="return false;">
            <li><span  style="font-size:14px;color:#333;">新用户完善用户信息！</span></li>
            <li>
              <div class="rM_title">用户名：</div>
              <div id="rM_loginName" class="rM_input">
                <input id="username" class="input_normal" type="text" name="username" style="width:190px;"/>
              </div>
              <div id="musername"></div>
            </li>
            <li>
              <div class="rM_title">邮箱：</div>
              <div id="rM_loginName" class="rM_input">
                <input id="remail" class="input_normal" type="text" name="remail" style="width:190px;"/>
              </div>
              <div id="memail"></div>
            </li>
            <li>
              <div class="rM_title">设置密码：</div>
              <div class="rM_input" id="rM_rpassword">
                <input type="password" style="width:190px;" class="input_normal" name="rpassword" id="rpassword" maxlength="20"/>
              </div>
              <div id="mpassword"></div>
            </li>
            <li>
              <div class="rM_title">确认密码：</div>
              <div class="rM_input" id="rM_rpassword2">
                <input type="password" style="width:190px;" class="input_normal" name="rpassword2" id="rpassword2" maxlength="20"/>
              </div>
              <div id="mpassword2"></div>
            </li>
            <li>
              <div class="rM_title">昵称：</div>
              <div class="rM_input" id="rM_nickname">
                <input type="text" style="width:190px;" class="input_normal" name="rnickname" id="rnickname" maxlength="20" value=""/>
              </div>
              <div id="mnickname"></div>
            </li>
            <li>
              <div class="rM_noleft" id="register">
                <input  type="submit" class="input_register" value="完善用户信息" id="submit" />
              </div>
            </li>
          </form>
        </ul>
      </div>
      <div  class="register_box_right">
        <ul class="clearfix">
          <li>邂逅各个城市夜店中美丽的女孩</li>
          <li>和你身边的美女帅哥成为朋友</li>
          <li>用照片和说说记录生活，展示自我</li>
          <li>寻找音乐知音，了解他们的最新动态</li>
          <li>找到喜欢的专区，参加有趣的活动</li>
          <li>与大家分享生活乐趣</li>
        </ul>
        <div class="current"><a href="javascript:;;">当前有
          <p>{$count}</p>
          个帅哥美女活跃！</a></div>
        <ul class="clear">
         <volist name="heat" id="vo">
          <li> <a target="_blank" href="{:UM('Home/index',array('userid'=>$vo['userid']))}"> <img src="{:U('Api/Avatar/index',array('uid'=>$vo['userid'],'size'=>48))}" onerror="this.src='{$model_extresdir}images/noavatar.jpg'"> </a> </li>
        </volist>
        </ul>
      </div>
    </div>
  </div>
</div>
<template file="Member/Public/regBottom.php"/>
<script type="text/javascript" src="{$model_extresdir}js/sinabinding.js"></script>
<script type="text/javascript">
	sinabinding.init();
	</script>
</body>
</html>
