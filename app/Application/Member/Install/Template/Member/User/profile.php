<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>个人设置 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/swfobject.js"></script>
<script type="text/javascript" src="{$model_extresdir}js/fullAvatarEditor.js"></script>
<link href="/favicon.ico" rel="shortcut icon">
<link type="text/css" href="{$model_extresdir}css/core.css" rel="stylesheet" />
<link type="text/css" href="{$model_extresdir}css/common.css" rel="stylesheet" />
<link type="text/css" href="{$model_extresdir}css/user.css" rel="stylesheet" media="all" />
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
            <li class="modify"> <a class="on" href="{:U('User/profile')}">个人资料</a> </li>
          </ul>
        </div>
        <div class="main_nav2">
          <ul id="aa">
            <li  <if condition="$type eq 'profile' ">class="current"</if>  id="cprofile"> <a href="{:U('User/profile')}"><span>基本资料</span></a> </li>
            <li  <if condition="$type eq 'avatar' ">class="current"</if>  id="cavatar"> <a href="javascript:;"><span>修改头像</span></a> </li>
            <li  id="cpassword"> <a href="javascript:;"><span>修改密码</span></a> </li>
          </ul>
          <div id="tooltip" class="refresh"> <a id="refresh" class="eda" type="0" cid="4" href="help/" title="查看帮助" target="_blank"> </a> </div>
        </div>
        <div class="minHeight500"> 
          <!--修改基本资料-->
          <form id="doprofile" action="{:U('User/doprofile')}" method="post">
          <div id="modifyProfile" class="profile"  <if condition="$type neq 'profile' ">style="display: none;"</if>>
            <div class="title">
              <div class="name">修改资料</div>
            </div>
            <ul>
              <li>
                <div class="name"><span>*</span>昵称：</div>
                <div class="input92cc"> <input id="rnickname" class="input" type="text" value="{$userinfo.nickname}" name="nickname" style="width:190px;"> </div>
                <div id="mnickname" class="input_msg"></div>
              </li>
              <li>
                <div class="name"><span>*</span>邮箱：</div>
                <div class="input92cc"><input id="remail" class="  input" type="text" value="{$userinfo.email}" name="email" style="width:190px;"></div>
				<div id="memail" class="input_msg"></div>
			  </li>
              <li><div class="name">性　　别：</div>
                  <div class="input92cc">
                  <select id="rsex" class="select_normal" name="sex" style="width:70px;">
                      <option <if condition="$userinfo['sex'] eq 0 ">selected="selected"</if> value="1">未 知</option>
                      <option <if condition="$userinfo['sex'] eq 1 ">selected="selected"</if> value="1">帅 哥</option>
                      <option <if condition="$userinfo['sex'] eq 2 ">selected="selected"</if> value="2">美 女</option>
                  </select>
                  </div>
              </li>
              <li><div class="name">个人介绍：</div><div class="input92cc"><textarea name="about" cols="30" rows="7" id="rselfIntroduce" class="input"  style=" height:100px;width: 280px;">{$userinfo.about}</textarea></div><div id="mselfIntroduce" class="input_msg"></div></li>
              <?php foreach($forminfos['base'] as $k=>$v) {?>
              <li>
                <div class="name"><?php echo $v['name']?>：</div>
                <div >
                  <?php echo $v['form']?>
                </div>
                <div id="memail" class="input_msg"></div>
              </li>
              <?php }?>
              <li>
                <div class="name"></div>
                <div > <span class="button-main"> <span>
                  <button id="seveProfile" type="button">保存修改</button>
                  </span> </span> </div>
              </li>
            </ul>
          </div>
          </form>
          <!--修改头像-->
          <div id="modifyAvatar" class="profile"  <if condition="$type neq 'avatar' ">style="display: none;"</if>>
            <div class="title">
              <div class="name">修改头像</div>
            </div>
            <div class="avatar_box">
              <div class="myAvatarUpload" style="border: 0 solid #E7E7E7;display: inline;float: left; height:450px;margin-bottom: 10px;padding-left: 10px;width: 432px;">
              	<div id="myContent"></div>
                {$user_avatar}
              </div>
            </div>
          </div>
          <!--修改密码-->
          <div id="modifyPassword" class="profile"  style="display: none;" >
            <div class="title">
              <div class="name">修改密码</div>
            </div>
            <ul>
              <li>
                <div class="name">当前密码：</div>
                <div class="input92cc">
                  <input id="roldpassword" class="input_normal input" type="password" maxlength="20" name="roldpassword" style="width:190px;" />
                </div>
                <div id="moldpassword" class="input_msg">请输入您当前使用的密码。</div>
              </li>
              <li>
                <div class="name">设置新密码：</div>
                <div class="input92cc">
                  <input id="rpassword" class="input_normal input" type="password" maxlength="20" name="rpassword" style="width:190px;" />
                </div>
                <div id="mpassword" class="input_msg">6到20个字符，请使用英文字母（区分大小写）、符号或数字。</div>
              </li>
              <li>
                <div class="name">确认新密码：</div>
                <div class="input92cc">
                  <input id="rpassword2" class="input_normal input" type="password" maxlength="20" name="rpassword" style="width:190px;" />
                </div>
                <div id="mpassword2" class="input_msg">再次输入您所设置的密码，以确认密码无误。</div>
              </li>
              <li>
                <div class="name"></div>
                <div class="input92cc"> <span class="button-main"> <span>
                  <button id="sevePassword" type="button">保存修改</button>
                  </span> </span> </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
<template file="Member/Public/homeFooter.php"/>
</div>
<script type="text/javascript" src="{$config_siteurl}statics/js/wind.js"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/common.js"></script>
<script type="text/javascript" src="{$model_extresdir}js/profile.js"></script> 
<script type="text/javascript" src="{$config_siteurl}statics/js/ajaxForm.js"></script>
<script type="text/javascript">
profile.init();
//头像上传回调
function fullAvatarCallback(msg) {
    switch (msg.code) {
    case 1:
        
        break;
    case 2:
        
        break;
    case 3:
        if (msg.type == 0) {
            
        } else if (msg.type == 1) {
            alert("摄像头已准备就绪但用户未允许使用！");
        } else {
            alert("摄像头被占用！");
        }
        break;
    case 4:
        alert("图像文件过大！");
        break;
    case 5:
        if (msg.type == 0) {
		    $.tipMessage("头像已经修改，刷新后可见最新头像！", 0, 3000);
        } else {
			alert(msg.content.msg);
		}
        break;
    }
}
</script>
</body>
</html>