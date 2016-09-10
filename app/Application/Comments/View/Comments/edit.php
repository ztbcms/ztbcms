<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">评论内容</div>
  <form name="myform" action="{:U("Comments/edit")}" method="post" class="J_ajaxForm">
  <input type="hidden" name="id" value="{$data.id}">
  <div class="table_full">
  <table width="100%" class="table_form contentWrap">
        <tbody>
        <volist name="field" id="vo">
        <tr>
          <th width="100">{$vo.fname}</th>
          <td><input type="text" name="{$vo.f}" value="{$data[$vo['f']]}" class="input" id="{$vo.f}" size="30"></td>
        </tr>
        </volist>
        <tr>
          <th>评论正文</th>
          <td><textarea name="content" rows="2" cols="20" id="content"style="height:100px;width:500px;">{$data.content}</textarea></td>
        </tr>
      </tbody></table>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
