<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">回复评论</div>
  <form name="myform" action="{:U("Comments/replycomment")}" method="post" class="J_ajaxForm">
    <input type="hidden" name="parent" value="{$parent}">
    <input type="hidden" name="comment_catid" value="{$catid}">
    <input type="hidden" name="comment_id" value="{$commentid}">
    <div class="table_full"> 
    <table width="100%" class="table_form contentWrap">
        <tbody>
        <tr>
          <th width="100">网站地址</th>
          <td><input type="text" name="author_url" value="{$author_url}" class="input" id="author_url" size="30"></td>
        </tr>
        <tr>
          <th>邮箱</th>
          <td><input type="text" name="author_email" value="{$author_email}" class="input" id="author_email" size="30"></td>
        </tr>
        <tr>
          <th>昵称</th>
          <td><input type="text" name="author" value="{$author}" class="input" id="author" size="30"></td>
        </tr>
        <volist name="field" id="vo">
        <tr>
          <th>{$vo.fname}</th>
          <td><input type="text" name="{$vo.f}" value="" class="input" id="{$vo.f}" size="30"></td>
        </tr>
        </volist>
        <tr>
          <th>评论正文</th>
          <td><textarea name="content" id="content" class="inputtext"><font color="#FF0000">@{$data.author}</font>&nbsp;</textarea><?php echo Form::editor("content"); ?></td>
        </tr>
      </tbody></table>
    </div>
     <div class="btn_wrap" style="z-index:999;">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">回复</button>
      </div>
    </div>
  </form>
</div>
</body>
</html>
