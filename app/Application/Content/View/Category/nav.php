<?php if (!defined('CMS_VERSION')) exit(); ?><div class="subnav">
  <div class="content-menu ib-a blue line-x"> 
  <if condition="$appinfo['action'] eq 'index' ">
  <a href='javascript:;' class="on"><em>管理栏目</em></a><span>|</span>
  <else />
  <a href='{:U("Category/index")}'><em>管理栏目</em></a><span>|</span>
  </if>
  <if condition="$appinfo['action'] eq 'add' ">
  <a href='javascript:;' class="on" ><em>添加栏目</em></a><span>|</span>
  <else />
  <a href='{:U("Category/add")}' ><em>添加栏目</em></a><span>|</span>
  </if>
  <if condition="$appinfo['action'] eq 'wadd' ">
  <a href='javascript:;' class="on" ><em>添加外部链接</em></a><span>|</span>
  <else />
  <a href='{:U("Category/wadd",array("type"=>2))}' ><em>添加外部链接</em></a><span>|</span>
  </if>
  <a href='{:U("Category/public_cache")}' ><em>更新栏目缓存</em></a>
  </div>
</div>
