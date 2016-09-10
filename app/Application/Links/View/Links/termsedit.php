<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">分类详情</div>
  <form method="post" id="myform" action="{:U('Links/termsedit')}">
  <input type="hidden" name="id" value="{$id}" />
  <div class="table_full">
  <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
      <tbody>
        <tr>
          <th width="100">分类名称:</th>
          <td><input type="text" class="input" name="name" id="name" value="{$name}"></td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
