<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">搜索</div>
  <form name="searchform" action="{$config_siteurl}index.php" method="get" >
    <input type="hidden" value="Comments" name="g">
    <input type="hidden" value="Comments" name="m">
    <input type="hidden" value="index" name="a">
    <div class="search_type cc mb10">
      <div class="mb10"> <span class="mr20"> 搜索类型：
        <select name="searchtype">
          <option value="1" <if condition="$_GET['searchtype'] eq '1'">selected</if>>评论作者</option>
          <option value="2" <if condition="$_GET['searchtype'] eq '2'">selected</if> >所属文章id</option>
        </select>
        关键字：
        <input type="text" class="input length_2" name="keyword" size='10' value="{$Think.get.keyword}" placeholder="关键字">
        <button class="btn">搜索</button>
        </span> </div>
    </div>
  </form>
  <form name="myform"  class="J_ajaxForm" action="" method="post" >
    <div class="table_list">
      <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td width="20" align="center"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></td>
            <td width="50" align="center">ID</td>
            <td width="100" align="center">作者</td>
            <td >评论内容</td>
            <td width="180" align="center">原文标题</td>
            <td width="180" align="center">操作</td>
          </tr>
        </thead>
        <tbody>
          <volist name="data" id="vo">
            <tr>
              <td align="center"><input class="checkbox J_check "  data-yid="J_check_y" data-xid="J_check_x"  name="ids[]" value="{$vo.id}" type="checkbox"></td>
              <td align="center">{$vo.id}</td>
              <td align="center">{$vo.author}</td>
              <td >{$vo.content}<br/>
                <b>发表时间：{$vo.date|date="Y-m-d H:i:s",###}，IP：{$vo.author_ip}</b></td>
              <td align="center"><a href="{$vo.url}" target="_blank">{$vo.title}</a></td>
              <td align="center"><a class="J_ajax_del" href="{:U("Comments/delete",array("id"=>$vo['id']))}">删除</a> | <a href="{:U("Comments/edit",array("id"=>$vo['id']))}">编辑</a> | <a href="{:U("Comments/replycomment",array("id"=>$vo['id']))}">回复</a> | <a href="{:U("Comments/spamcomment",array("id"=>$vo['id']))}">取消审核</a></td>
            </tr>
          </volist>
        </tbody>
      </table>
      <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
      	<label class="mr20"><input type="checkbox" class="J_check_all" data-direction="y" data-checklist="J_check_y">全选</label>
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">删除</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
