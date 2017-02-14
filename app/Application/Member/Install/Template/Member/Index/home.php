 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>个人中心 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<link href="/favicon.ico" rel="shortcut icon">
<link type="text/css" href="{$model_extresdir}css/core.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" />
<link type="text/css" href="{$model_extresdir}css/user.css" rel="stylesheet" media="all" />
</head>
<body>
<template file="Member/Public/homeHeader.php"/>
<div class="user">
  <div class="user_center">
    <template file="Member/Public/homeUserMenu.php"/>
    <div class="user_main">
      <div class="uMain_home_left">
        <div class="stage_box">
          <div class="user_info">
            <div class="info">
              <div class="title">有什么新鲜事想告诉大家？</div>
              <span></span> </div>
            <span class="arrow"></span>
            <div class="doing">
              <div id="note" contenteditable="true" class="blogInput" name="note"></div>
              <div id="act" class="act" style="display: block;">
                <div id="emot_note" class="emot" to="note" style="display:none"></div>
                <!--表情-->
                <div class="button"> <span class="button-main"> <span>
                  <button type="button" class="send">发布</button>
                  </span> </span> </div>
              </div>
            </div>
          </div>
          
          <div class="feed_menu">
            <ul>
              <li> <a id="friend_feed" class="on" onclick="$call(function(){libs.feedNew(0);$('#refresh').attr({'cid':0, type:0})});" href="javascript:;">好友动态</a> </li>
              <li> <a id="feed_all"  onclick="$call(function(){libs.feedNew(2);$('#refresh').attr({'cid':2, type:0})});" href="javascript:;">全站动态</a> </li>
              <li> <a id="feed_me" onclick="$call(function(){libs.feedNew(3);$('#refresh').attr({'cid':3, type:0})});" href="javascript:;">我的动态</a> </li>
            </ul>
          </div>
          <div class="feed_menu2">
            <ul id="a1">
              <li id="feed_0"> <a onclick="$call(function(){libs.showFeedMenu(0, 0);$('#refresh').attr({'cid':0, type:0})});" href="javascript:;">全部动态</a> </li>
              <li id="feed_1"> <a onclick="$call(function(){libs.showFeedMenu(1, 0);$('#refresh').attr({'cid':0, type:1})});" href="javascript:;">资讯分享</a> </li>
              <li id="feed_2"> <a onclick="$call(function(){libs.showFeedMenu(2, 0);$('#refresh').attr({'cid':0, type:2})});" href="javascript:;">发表说说</a> </li>
              <li id="feed_3"> <a onclick="$call(function(){libs.showFeedMenu(3, 0);$('#refresh').attr({'cid':0, type:3})});" href="javascript:;">上传照片</a> </li>
            </ul>
            <ul id="a2" style="display: none;">
              <li id="feedA_0"> <a onclick="$call(function(){libs.showFeedMenu(0, 2);$('#refresh').attr({'cid':2, type:0})});" href="javascript:;">全部动态</a> </li>
              <li id="feedA_1"> <a onclick="$call(function(){libs.showFeedMenu(1, 2);$('#refresh').attr({'cid':2, type:1})});" href="javascript:;">资讯分享</a> </li>
              <li id="feedA_2"> <a onclick="$call(function(){libs.showFeedMenu(2, 2);$('#refresh').attr({'cid':2, type:2})});" href="javascript:;">发表说说</a> </li>
              <li id="feedA_3"> <a onclick="$call(function(){libs.showFeedMenu(3, 2);$('#refresh').attr({'cid':2, type:3})});" href="javascript:;">上传照片</a> </li>
            </ul>
            <div id="tooltip" class="refresh"> <a class="eda" title="刷新" href="javascript:;" id="refresh" cid="0" type="0"> </a> </div>
          </div>
          <div class="feed" id="feed">
            <div class="load"></div>
          </div>
        </div>
      </div>
      <div class="uMain_home_right">
        <if condition=" $visitors ">
        <div class="sFriendTitle"> <span>最近访客</span>
          <p> <a  href="{:U('Relation/visitorin')}">更多</a> </p>
        </div>
        <ul class="sFriend">
           <volist name="visitors" id="vo">
            <li>
              <div class="friendAvatar"><a href="{:UM('Home/index',array('userid'=>$vo['userid']))}" class="user_card" uid="{$vo['userid']}"><img width="48" height="48" src="{:U('Api/Avatar/index',array('uid'=>$vo['userid'],'size'=>48))}" onerror="this.src='{$model_extresdir}images/noavatar.jpg'"/></a></div>
              <div class="friendInfo"><span><a title="{$vo.username}" href="{:UM('Home/index',array('userid'=>$vo['userid']))}" class="<if condition=" $vo['isonline'] ">online_icon</if> user_card" uid="{$vo['userid']}">{$vo.username}</a></span>
                <p>{$vo.datetime|format_date}</p>
              </div>
            </li>
           </volist>
        </ul>
        </if>
        <if condition=" $friends ">
        <div class="sFriendTitle"><span>关注<em>[{$friendsCount}]</em></span><p><a href="{:U('Relation/following')}">更多</a></p></div>
        <ul class="sFriend">
          <volist name="friends" id="vo">
            <li>
              <div class="friendAvatar"><a href="{:UM('Home/index',array('userid'=>$vo['fuserid']))}" class="user_card" uid="{$vo['fuserid']}"><img width="48" height="48"src="{:U('Api/Avatar/index',array('uid'=>$vo['fuserid'],'size'=>48))}" onerror="this.src='{$model_extresdir}images/noavatar.jpg'"/></a></div>
              <div class="friendInfo"><span><a title="{$vo.fusername}" href="{:UM('Home/index',array('userid'=>$vo['fuserid']))}"  class="<if condition=" $vo['isonline'] ">online_icon</if> user_card" uid="{$vo['fuserid']}">{$vo.fusername}</a></span>
                <p></p>
              </div>
            </li>
         </volist>
        </ul>
        </if>
        {:tag('view_member_right',$User)}
      </div>
    </div>
  </div>
<template file="Member/Public/homeFooter.php"/>
</div>
<script type="text/javascript" src="{$model_extresdir}js/slot.js"></script> 
<script type="text/javascript" src="{$model_extresdir}js/miniblog.js"></script> 
<script type="text/javascript" src="{$model_extresdir}js/account.js"></script> 
<script type="text/javascript" src="{$model_extresdir}js/jquery.emotEditor.js"></script>
<script type="text/javascript">
	libs.feed();
	libs.showFeedMenu1();
	miniblogLib.miniblogHomeAddInit();
	account.doListenAccountInit();
</script>
<script type="text/javascript" src="{$model_extresdir}js/card.js"></script>
</body>
</html>
