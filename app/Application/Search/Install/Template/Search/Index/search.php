<?php if (!defined('CMS_VERSION')) exit(); ?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$keyword} - {$Config.sitename} - 搜索</title>
<link rel="stylesheet" href="{$config_siteurl}statics/css/search.css" />
<script>
//全局变量
var GV = {
    DIMAUB: "{$config_siteurl}",
    JS_ROOT: "statics/js/"
};
</script>
<script src="{$config_siteurl}statics/js/wind.js"></script>
<script src="{$config_siteurl}statics/js/jquery.js"></script>
</head>
<body>
<div id="top">
  <p class="nav" id="s_bar"><a class="active" href="{$Config.siteurl}">网站首页</a></p>
  <p class="user" id="s_user"></p>
</div>
<div id="header">
  <h1 id="rLogo"><a href="{$config_siteurl}" title="到首页"><img src="{$config_siteurl}statics/images/hei_logo.png" alt="到搜搜首页" /></a></h1>
  <div class="sBox">
    <form name="flpage" action="{:U('Search/Index/index')}" method="post">
      <input type="hidden" name="g" value="Search" />
      <input name="q" class="s_input" type="text"  id="smart_input" x-webkit-speech value="{$keyword}" />
      <input value="搜搜" class="s_button" type="submit" />
    </form>
    <div id="smart_pop" style="display:none;"></div>
  </div>
  <div id="sInfo">搜索到约{$count}项结果，用时{$search_time}秒</div>
</div>
<div id="main"  >
  <div id="l" ss_c="filter">
    <div ss_c="guide.intel">
      <ul id='nf'>
        <li><a href="{:U('Index/index',array('q'=>$keyword)) }" class="now">全部结果</a></li>
        <volist name="source" id="r">
        <li><a href="{:U('Index/index?q='.$keyword,array('modelid'=>$r['modelid']) ) }" <if condition=" $modelid EQ $r['modelid'] "> style="background: #D1E5FC;"</if>>{$r.name}</a></li>
        </volist>
      </ul>
      <ul id='tf'>
        <li><a href="{:U('Index/index?q='.$keyword,array('time'=>'','modelid'=>$modelid) ) }" class="now" >全部时间</a></li>
        <li><a href="{:U('Index/index?q='.$keyword,array('time'=>'day','modelid'=>$modelid) ) }" <if condition=" $time EQ 'day' "> style="background: #D1E5FC;"</if>>一天内</a></li>
        <li><a href="{:U('Index/index?q='.$keyword,array('time'=>'week','modelid'=>$modelid) ) }" <if condition=" $time EQ 'week' "> style="background: #D1E5FC;"</if>>一周内</a></li>
        <li><a href="{:U('Index/index?q='.$keyword,array('time'=>'month','modelid'=>$modelid) ) }" <if condition=" $time EQ 'month' "> style="background: #D1E5FC;"</if>>一月内</a></li>
        <li><a href="{:U('Index/index?q='.$keyword,array('time'=>'year','modelid'=>$modelid) ) }" <if condition=" $time EQ 'year' "> style="background: #D1E5FC;"</if>>一年内</a></li>
      </ul>
      <ul id='sf'>
      </ul>
      <dl id="shis">
        <dt>搜索历史</dt>
        <volist name="shistory" id="keys">
        <dd><a href="{:U('Index/index',array('q'=>$keys))}" title="{$keys}">{$keys}</a></dd>
        </volist>
      </dl>
      <dl>
        <span class="hiStop" id="chf" ></span>
      </dl>
    </div>
    <p id="toggle"  style="display:none;" ><span >&nbsp;</span></p>
  </div>
  <div id="r">
   <if condition=" !$result "> 
     <div id="result" class="noResult">
        <h3>抱歉，找不到与 "<em>{$keyword}</em>" 相符的信息</h3>
        <h4>搜搜建议您:</h4>
        <ul>
             <li>请检查输入的关键词是否有误</li>
        </ul>
    </div>
    <else />
    <div id="result" class="result"> 
      <!--result list begin-->
      <ol>
        <volist name="result" id="r">
            <volist name="words" id="vo">
                <php>
                $r["description"] = str_replace($vo,'<font color="red">'.$vo.'</font>',$r["description"]);
                $r["title"] = str_replace($vo,'<font color="red">'.$vo.'</font>',$r["title"]);
                </php>
            </volist>
          <li loc="1">
            <div id='box_0_0' class="selected boxGoogleList">
              <h3><a href="{$r.url}"  class="tt tu"  target="_blank">{$r.title}</a></h3>
              <p class="ds">
              {$r.description}
              </p>
              <div class="result_summary">
                <div class="url"><cite>{$r.url}&nbsp;{$r.updatetime|date="Y-m-d",###} -</cite></div>
                <div class="sp"><a href="{$category[$r['catid']]['url']}" target="_blank" style="color:#999">{$category[$r['catid']]['catname']}</a></div>
              </div>
              <div class="highLight"></div>
            </div>
          </li>
        </volist>
      </ol>
      <!--result list end--></div>
    </if>
    <div id="side">
      <div id="brand"></div>
      <div id="bar">
        <div ss_c="search.hint.r">
          <dl>
            <dt>相关搜索</dt>
            <volist name="relation" id="r">
              <dd><a href="{:U('Index/index',array('q'=>$r['keyword'])) } " title="">{$r.keyword}</a></dd>
            </volist>
          </dl>
        </div>
      </div>
    </div>
    <div id="bottom">
      <div id="pager">
        {$Page}
      </div>
      <div id="bSearch">
        <div class="sBox">
          <form name="flpage" action="index.php" method="get">
            <input type="hidden" name="g" value="Search" />
            <input name="q" class="s_input" type="text"  id="smart_input" x-webkit-speech value="{$keyword}" />
            <input value="搜搜" class="s_button" type="submit" />
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="footer" ss_c="tshh">
  <p><a href="#" target="_blank" >帮助中心</a>|<a href="#" target="_blank" >反馈建议</a>|<a href="http://www.ztbcms.com" target="_blank">ZtbCMS</a></p>
  Copyright &copy; 2016 ztbcms.com. All Rights Reserved. </div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
