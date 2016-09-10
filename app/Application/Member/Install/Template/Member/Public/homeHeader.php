<?php if (!defined('CMS_VERSION')) exit(); ?>
<div class="header">
  <div class="h_main">
    <ul class="menus">
      <li class="m_nav_a"><a class="sliding_menu" href="{$Config.siteurl}" >首页<b class="arrow"></b></a></li>
      <li class="m_nav_b" id="djadslink"></li>
    </ul>
    <ul class="member_login" id="userLogin">
      <li> <img src="{:U('Api/Avatar/index',array('uid'=>$uid,'size'=>45))}" onerror="this.src='{$model_extresdir}images/noavatar.jpg'" id="userInfo">  </li>
      <li> <a class="icon" href="{:U('Index/home')}"  title="个人中心">个人中心</a> <span id="feedTips" class="feed_tips" style="display: none;" title="您的好友有新的动态!"></span> 
      </li>
      <li><a class="set_menu icon share " id="userInfo" href="javascript:;" title="我的分享">我的分享</a>
        <div class="m_set_list menu" style="display: none;" id="uploadList"> 
            <em></em> 
            <a class="list"  href="{:U('Share/add')}"><b class="dan"></b>添加分享</a>
            <a class="list"  href="{:U('Share/index')}"><b class="share"></b>我分享的</a>
            <a class="list"  href="{:U('Share/index',array('type'=>'check'))}"><b class="audit"></b>已审核的</a>
            <a class="list"  href="{:U('Share/index',array('type'=>'checking'))}"><b class="audit"></b>审核中的</a>
        </div>
      </li>
      <li><a class="set_menu icon song " id="userInfo" href="{:U('Favorite/index')}" title="我的收藏">我的收藏</a></li>
      <li> <a class= "set_menu icon set" href="javascript:;" title="个人设置">个人设置</a>
        <div class="m_set_list menu" style="display: none;"> 
            <em></em> 
            <a class="list" href="{:U('User/profile')}"> <b class="setup"></b>个人设置 </a> 
            <a class="list" href="{:U('User/profile',array('type'=>'avatar'))}"> <b class="avatar"></b>修改头像 </a> 
            <a class="list" href="{:U('Member/Index/logout')}"> <b class="exit"></b>退出登录 </a> 
        </div>
      </li>
    </ul>
  </div>
  <div class="class_nav">
    <!--搜索-->
    <form id="searchForm" onSubmit="searchDance.init();return false;" >
      <div class="serach right">
        <div class="seh_list"> <a class="seh_list_a" href="javascript:;" id="searchType" sid="1"> 信息 <b class="arrow"></b> </a>
          <div class="seh_sort" style="display: none;"> <a href="javascript:;">信息</a> </div>
        </div>
        <div class="seh_m">
          <input id="txtKey" class="seh_v" type="text" >
          <input class="seh_b f16" type="submit">
          </input>
        </div>
      </div>
    </form>
  </div>
</div>
