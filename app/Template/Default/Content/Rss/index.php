<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=7" />
<title>
<if condition=" isset($SEO['title']) && !empty($SEO['title']) ">{$SEO['title']}</if>
{$SEO['site_title']}</title>
<meta name="description" content="{$SEO['description']}" />
<meta name="keywords" content="{$SEO['keyword']}" />
<link href="{$Config.siteurl}statics/css/reset.css" rel="stylesheet" type="text/css" />
<link href="{$Config.siteurl}statics/css/default_blue.css" rel="stylesheet" type="text/css" />
<script language="javascript" type="text/javascript" src="{$Config.siteurl}statics/js/jquery.js"></script>
<style type="text/css">
.header, .main, .footer {
	width: 940px;
	margin: 0 auto;
	overflow: auto
}
</style>
</head>
<body class="rss-channel">
<div class="body-top special-body-top">
  <div class="content"> <span class="rt"> </span> <a href="{:U('Rss/index')}" class="logo"><img src="{$Config.siteurl}statics/images/logo.gif" height="30"></a>
    <div class="nav"> <a href="{$Config.siteurl}">首页</a><span>|</span>
      <content action="category" catid="0" order="listorder ASC">
        <volist name="data" id="vo"> <a href="{$vo.url}" target="_blank">{$vo.catname}</a><span>|</span> </volist>
      </content>
    </div>
  </div>
</div>
<div class="header"> </div>
<style type="text/css">
.header,.main,.footer{width:940px;margin:0 auto; overflow:auto}
</style>
<div class="main">
  <div class="col-left navlist">
    <div class="box memu">
      <h6 class="title-2 f14 text-c">频道列表</h6>
      <div class="content blue">
        <content action="category" catid="0" order="listorder ASC">
          <volist name="data" id="vo">
            <div class="color on">{$vo.catname}</div>
            <ul style="display:block">
              <content action="category" catid="$vo['catid']" order="listorder ASC" return="list">
                <volist name="list" id="r" key="i" mod="2">
                  <li><a href="{:U('Rss/index' , array('catid'=>$r['catid']) )}">{$r.catname}</a> <a href="{:U('Rss/index' , array('rssid'=>$r['catid']) )}" target="_blank"><img src="{$Config.siteurl}statics/images/icon/rss.gif"></a></li>
                </volist>
              </content>
            </ul>
          </volist>
        </content>
      </div>
    </div>
  </div>
  <div class="col-right rsslist">
    <content action="category" catid="$catid" order="listorder ASC">
    <volist name="data" id="vo" key="ii">
    <div class="box" 
    <if condition="  ($ii % 2) "> style="margin-right:10px"</if>
    >
    <h5 class="title-1"><span class="xml"><a rel="" href="{:U('Rss/index' ,array('rssid'=>$vo['catid']) )}" class="xmlbut" target="_blank">xml</a>
      <div class="rss cu" onclick="RssTab('rss_{$vo.catid}')" id="rss_{$vo.catid}">
        <dl>
          <dt>订阅到</dt>
        </dl>
      </div>
      </span>{$vo.catname}</h5>
    <ul class="content list f14 lh24">
      <content action="lists" catid="$vo['catid']" num="5" return="list">
        <volist name="list" id="r" empty="暂无内容！">
          <li>·<a href="{$r.url}" target="_blank">{$r.title}</a></li>
        </volist>
      </content>
    </ul>
  </div>
  <if condition=" $ii == 2 ">
    <div class="bk10"></div>
  </if>
  </volist>
  </content>
  <div class="bk10"></div>
</div>
<div class="clear"></div>
</div>
<script type="text/javascript">

$(function() {
var memu = $('.navlist .memu .content div');
memu.toggle(
  function () {
	$(this).addClass('on');
    $(this).next().show();
  },
  function () {
	$(this).removeClass('on');
    $(this).next().hide();
  }
);	
});
var ppwin='<dl><dt>订阅到</dt><dd><a href="http://reader.youdao.com/b.do?keyfrom=163&url={feedurl}" title="有道" target="_blank"><img src="http://img1.cache.netease.com/cnews/css09/rss100121/icon_subshot02_youdao.gif" width="50" height="14" alt="有道" /></a></dd><dd><a href="http://fusion.google.com/add?feedurl={feedurl}" title="google" target="_blank"><img src="http://img1.cache.netease.com/cnews/css09/rss100121/icon_subshot02_google.gif" width="50" height="14" alt="google" /></a></dd><dd><a href="http://add.my.yahoo.com/rss?url={feedurl}" title="yahoo" target="_blank"><img src="http://img1.cache.netease.com/cnews/css09/rss100121/icon_subshot02_yahoo.gif" width="50" height="14" alt="yahoo" /></a></dd><dd><a href="http://www.xianguo.com/subscribe.php?url={feedurl}" title="鲜果" target="_blank"><img src="http://img1.cache.netease.com/cnews/css09/rss100121/icon_subshot02_xianguo.gif" width="50" height="14" alt="鲜果" /></a></dd><dd><a href="http://www.zhuaxia.com/add_channel.php?url={feedurl}" title="抓虾" target="_blank"><img src="http://img1.cache.netease.com/cnews/css09/rss100121/icon_subshot02_zhuaxia.gif" width="50" height="14" alt="抓虾" /></a></dd><dd><a href="http://mail.qq.com/cgi-bin/feed?u={feedurl}" title="qq" target="_blank"><img src="http://img1.cache.netease.com/cnews/css09/rss100121/icon_subshot02_qq.gif" width="50" height="14" alt="qq" /></a></dd><dd><a href="http://my.msn.com/addtomymsn.armx?id=rss&ut={feedurl}" title="msn" target="_blank"><img src="http://img1.cache.netease.com/cnews/css09/rss100121/msn.jpg" width="44" height="14" alt="msn" /></a></dd></dl>';
function RssTab(id){
	var RssObj=$('span.xml .rss[id='+id+']');
	var RssObjdl=$('span.xml .rss dl');
	for(var i=0,len=RssObj.length;i<len;i++){
		var rp_ppwin=ppwin.replace(/{feedurl}/g,RssObj.siblings().attr('href'));
		RssObjdl.replaceWith(rp_ppwin);
		RssObj.hover(
		  function () {
			$(this).addClass("cur");
		  },
		  function () {
			$(this).removeClass("cur");
		  }
		);
	}

}
</script>
<div class="footer">
  <p class="info"> Powered by <strong><a href="http://www.ztbcms.com" target="_blank">ztbcms.Com</a></strong>? 2016 </p>
</div>
</body>
</body>
</html>
