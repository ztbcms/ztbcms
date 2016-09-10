<?php if (!defined('CMS_VERSION')) exit(); ?>
<div class="top">
  <div class="logo left"> <a href="{$config_siteurl}" title="{$Config.sitename}-国内后起的站长资源门户站"><img src="{$config_siteurl}statics/images/logo.gif" alt="{$Config.sitename}-国内后起的站长资源门户站"/></a></div>
  <div class="top_ad right"></div>
  <div style="clear:both"></div>
</div>
<!--top end-->
<div class="nav">
  <div id="nav-menu">
    <div class="container">
      <div class="outerbox">
        <div class="innerbox clearfixmenu">
          <ul class="menu">
            <li class="stmenu">
              <h3><a href="{$config_siteurl}" class="xialaguang <if condition="  !isset($catid) "> navhovers</if>" title="{$Config.sitename}"><span>网站首页</span></a></h3>
            </li>
            <content action="category" catid="0"  order="listorder ASC" >
            <volist name="data" id="vo">
            <if condition=" $vo['child'] ">
            <!--{$vo.catname}-->
            <li class="stmenu">
              <h3><a href="{$vo.url}" class="xialaguang <if condition="  in_array($catid,explode(',',$vo['arrchildid'])) "> navhovers</if>" title="{$vo.description}"><span>{$vo.catname}</span></a></h3>
              <ul style="display: none;" class="children clearfixmenu">
              <content action="category" catid="$vo['catid']"  order="listorder ASC" >
              <volist name="data" id="r">
                <li <if condition=" $i%2 == 0 "> class="noborder"</if>>
                  <h3><a href="{$r.url}" class="stmenu" title="{$r.catname}"><span>{$r.catname}</span></a></h3>
                </li>
                </volist>
                </content>
                <li class="count noborder">
                  <div>{$vo.description}</div>
                </li>
              </ul>
            </li>
            <else />
            <!--{$vo.catname}-->
            <li class="stmenu">
              <h3><a href="{$vo.url}" class="xialaguang <if condition="  in_array($catid,explode(',',$vo['arrchildid'])) "> navhovers</if>" style="margin-left:1px" title="{$vo.description}"><span>{$vo.catname}</span></a></h3>
            </li>
            </if>
            </volist>
            </content>
            <li class="overlay"></li>
          </ul>
          <script type="text/javascript">
		  $('#nav-menu .menu > li').hover(function () {
		      $(this).find('.children').animate({
		          opacity: 'show',
		          height: 'show'
		      }, 300);
		      $(this).find('.xialaguang').addClass('navhover');
		  }, function () {
		      $('.children').stop(true, true).slideUp(100);
		      $('.xialaguang').removeClass('navhover');
		  }).slice(-3, -1).find('.children').addClass('sleft');
          </script></div>
      </div>
    </div>
  </div>
</div>
<!--nav end-->
<div class="search">
  <form  name="formsearch" class="form" action="{:U('Search/Index/index')}" method="post">
    <input type="text" name="q" size="24" class="search-keyword"  id="inputString" class="f-text" x-webkit-speech /><input type="submit" class="search-submit"  id="search-submit" value="搜 索" />
    <div class="suggestionsBox" id="suggestions" style="display: none;">
      <div class="suggestionList">
        <ul id="autoSuggestionsList">
        </ul>
      </div>
    </div>
  </form>
</div>
