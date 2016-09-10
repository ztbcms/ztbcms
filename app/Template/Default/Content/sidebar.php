<?php if (!defined('CMS_VERSION')) exit(); ?>
<aside id="sidebar" class="g-u" role="complementary"> 
    <!--网站统计开始-->
    <ul class="status block">
      <get sql="SELECT count(*) as tj FROM think_article  WHERE `status`=99" >
        <li><em>{$data[0]['tj']}</em>文章</li>
      </get>
      <get sql="SELECT count(*) as tj FROM think_comments  WHERE `approved`=1" >
        <li><em>{$data[0]['tj']}</em>评论</li>
      </get>
      <get sql="SELECT count(*) as tj FROM think_tags" >
        <li><em>{$data[0]['tj']}</em>标签</li>
      </get>
    </ul>
    <!--网站统计结束--> 
    <!--网站地图导航开始-->
    <div class="subscribe block">
      <content action="category" catid="1" order="listorder ASC">
        <h1 class="sidebar-h1">日志分类</h1>
        <ul class="btn-fl-list grid">
          <volist name="data" id="vo">
            <li class="g-u"> <a href="{$vo.url}" class="tag-link-48" title="{$vo.catname}" style="font-size: 12px;">{$vo.catname}</a> </li>
          </volist>
        </ul>
      </content>
      <content action="category" catid="10" order="listorder ASC">
        <h1 class="sidebar-h1">音乐分类</h1>
        <ul class="btn-fl-list grid">
          <volist name="data" id="vo">
            <li class="g-u"> <a href="{$vo.url}" class="tag-link-48" title="{$vo.catname}" style="font-size: 12px;">{$vo.catname}</a> </li>
          </volist>
        </ul>
      </content>
    </div>
    <!--网站地图导航结束--> 
    <!--热门标签开始-->
    <div class="subscribe block">
      <content action="category" catid="1" order="listorder ASC">
        <h1 class="sidebar-h1">日志分类</h1>
        <ul class="btn-fl-list grid">
          <volist name="data" id="vo">
            <li class="g-u"> <a href="{$vo.url}" class="tag-link-48" title="{$vo.catname}" style="font-size: 12px;">{$vo.catname}</a> </li>
          </volist>
        </ul>
      </content>
      <content action="category" catid="10" order="listorder ASC">
        <h1 class="sidebar-h1">音乐分类</h1>
        <ul class="btn-fl-list grid">
          <volist name="data" id="vo">
            <li class="g-u"> <a href="{$vo.url}" class="tag-link-48" title="{$vo.catname}" style="font-size: 12px;">{$vo.catname}</a> </li>
          </volist>
        </ul>
      </content>
    </div>
    <!--热门标签结束--> 
    <!--最新评论列表-->
    <div class="latest-comments block">
      <h1>最新评论</h1>
      <ol style="display: block; ">
        <!--评论循环开始-->
        <comment action="lists" num="10" return="data" date="Y-m-d H:i:s">
          <volist name="data" id="vo">
            <li id="rc-comment-{$vo.id}" class="rc-item rc-comment rc-clearfix"><img class="rc-avatar rc-left" width="40" height="40" alt="{$vo.author}" src="{$vo.avatar}">
              <div class="rc-info"><a class="rc-reviewer" rel="nofollow">{$vo.author}</a></div>
              <div>
                <div class="rc-excerpt">{$vo.content|strip_tags|str_cut=###,200}</div>
              </div>
            </li>
          </volist>
        </comment>
        <!--评论循环结束-->
      </ol>
    </div>
    <!--最新评论列表结束--> 
    <!--友情链接开始-->
    <div class="subscribe block">
      <h1 class="sidebar-h1">友情链接</h1>
      <ul class="btn-fl-list grid">
        <links action="type_list" termsid="1">
          <volist name="data" id="vo">
            <li class="g-u"> <a href="{$vo.url}" class="tag-link-{$vo.id}" title="前往：{$vo.name}" target="{$vo.target}">{$vo.name}</a> </li>
          </volist>
        </links>
      </ul>
    </div>
    <!--友情链接结束--> 
  </aside>
