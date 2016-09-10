<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我的个人账户 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<link href="/favicon.ico" rel="shortcut icon">
<link type="text/css" href="{$model_extresdir}css/core.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" />
<link type="text/css" href="{$model_extresdir}css/user.css" rel="stylesheet" media="all" />
<style>
.gold { background: url("{$model_extresdir}images/integra_bg.jpg") no-repeat 0 -640px;width:750px;display: inline;float: left;margin:0 15px;height:277px;position: relative;}
.gold .title {display: inline;float: left;margin-top: 5px;padding: 8px 13px;width: 724px;color:#621fba;font-weight: bold;font-size: 14px}
.gold .box{display: inline;float: left;padding:10px 0 0 90px;}
.gold .box p{display: inline;float: left;line-height: 24px;width:100%;}
.gold .box .receive{ background: url("{$model_extresdir}images/integra_but.png") no-repeat 0 -216px;_background: url("{$model_extresdir}images/integra_but.gif") no-repeat 0 -216px;width:82px;height:31px;line-height: 29px;border: 0 none;color: #ffffff;cursor: pointer;display: inline;float: left;text-indent: -9999px;margin-left:40px;}
.gold .box span{display: inline;float: left;padding:15px 0 0 10px;}
.gold .box .residual{display: inline;float: left;font-size: 20px;font-weight: bold;color:#000000;line-height: 31px;}
.gold .box .residual strong{color:#ff0000;}

/*账号绑定*/
          	#accountBindContent{}
			#accountBindContent ul{}
			#accountBindContent ul li{margin-bottom:25px;}
			#accountBindContent ul li span{float:left;display:block;margin-right:20px;}
			#accountBindContent ul li span.qqBind{ background:url({$model_extresdir}images/bindBtn.png) no-repeat 0 0;height:64px;width:64px;}
			#accountBindContent ul li span.sinaBind{ background:url({$model_extresdir}images/bindBtn.png) no-repeat 0 -74px;height:64px;width:64px;}
			#accountBindContent ul li span.BindText{line-height:2.5;padding-top:8px;}
			#accountBindContent ul li span.BindText strong{font:bold 16px/2 'MicroSoft Yahei';color:#88B766;}
			#accountBindContent ul li span.BindBtn{float:right;margin-top:20px;}
			#accountBindContent ul li span.BindBtn a{display:block;height:auto;border:1px solid #eee;padding:5px 10px;background:#88B766;color:#fff;}
			#accountBindContent ul li span.BindBtn a.binded{background:#eee;color:#333;}
</style>
</head>
<body>
<template file="Member/Public/homeHeader.php"/>
<div class="user">
  <div class="user_center">
    <template file="Member/Public/homeUserMenu.php"/>
    <div class="user_main">
      <div class="uMain_content">
        <div class="main_nav">
          <ul>
            <li class="me_account"><a class="on" href="{:U('Account/assets')}">个人账户</a></li>
          </ul>
        </div>
        <div class="main">
          <div id="tooltip" class="refresh"><a id="refresh" class="eda" type="0" cid="4" href="help/class_74_1.html" title="查看帮助" target="_blank"></a></div>
        </div>
        <div class="minHeight500">
          <div id="accountBindContent" class="profile">
            <div class="title">
              <div class="name">您已经绑定的网站</div>
            </div>
            <ul>
 				<if condition=" $Member_config['qq_akey'] && $Member_config['qq_skey'] ">
                <li>
                  <span class="qqBind"></span>
                  <span class="BindText"><strong>腾讯QQ</strong><br />绑定腾讯QQ帐号后，您便可使用腾讯QQ帐号登录网站</span>
                  <if condition=" $isqqlogin ">
                  <span class="BindBtn"><a href="{:U('Account/cancelbind',array('connectid'=>$isqqlogin['connectid']) )}"  class="binded">取消绑定</a></span>
                  <else/>
                  <span class="BindBtn"><a href="{:U('Account/authorize',array('type'=>'qq') )}">立即绑定</a></span>
                  </if>
                </li>
                </if>
                <if condition=" $Member_config['sinawb_akey'] && $Member_config['sinawb_skey'] ">
                <li>
                  <span class="sinaBind"></span>
                  <span class="BindText"><strong>新浪微博</strong><br />绑定新浪微博帐号后，您便可使用新浪微博帐号登录网站</span>
                  <if condition=" $isweibologin ">
                  <span class="BindBtn"><a href="{:U('Account/cancelbind',array('connectid'=>$isweibologin['connectid']) )}"  class="binded">取消绑定</a></span>
                  <else/>
                  <span class="BindBtn"><a href="{:U('Account/authorize',array('type'=>'sina_weibo') )}">立即绑定</a></span>
                  </if>
                </li>	
                </if>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
<template file="Member/Public/homeFooter.php"/>
</div>
<script type="text/javascript" src="{$model_extresdir}js/account.js"></script>
<script type="text/javascript">
account.doAccountInit(); 
account.exchangeMoneyInit(); 
</script>
</body>
</html>
