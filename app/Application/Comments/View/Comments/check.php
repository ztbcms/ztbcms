<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
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
              <td align="center"><a class="J_ajax_del" href="{:U("Comments/delete",array("id"=>$vo['id']))}">删除</a> | <a href="{:U("Comments/edit",array("id"=>$vo['id']))}">编辑</a> | <a href="{:U("Comments/spamcomment",array("id"=>$vo['id']))}">审核</a></td>
            </tr>
          </volist>
        </tbody>
      </table>
      <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" data-action="{:U("Comments/Comments/check")}" type="submit">全部审核</button>
        <button class="btn  mr10 J_ajax_submit_btn" data-action="{:U("Comments/Comments/index")}" type="submit">删除</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
