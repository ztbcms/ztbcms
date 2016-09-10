<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">

  <div class="h_a">{$operator['name']} - 添加字段</div>
  <form name="myform" class="J_ajaxForm" action="" method="post">
  <div class="table_full">
  <table width="100%" class="table_form contentWrap">
      <tr>
        <th width="25%"><font color="red">*</font> <strong>字段名</strong><br />
          只能由英文字母组成</th>
        <td><input type="text" name="name" style="width:400px;" class="input"></td>
      </tr>
      
      <tr>
        <th><strong>字段默认值</strong></th>
        <td><input type="text" name="default" style="width:400px;" class="input"></td>
      </tr>
      
      <tr>
        <th><strong>字段描述</strong></th>
        <td><input type="text" name="comment" style="width:400px;" class="input"></td>
      </tr>

    </table>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">添加</button>
        <input name="operator" type="hidden" value="{$operator['tablename']}" />
      </div>
    </div>
  </form>
</div>
</body>
</html>
