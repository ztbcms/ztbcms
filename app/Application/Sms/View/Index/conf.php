<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">

  <div class="h_a">{$operator['name']} - 参数设置</div>
  <form name="myform" class="J_ajaxForm" action="" method="post">
  <div class="table_full">
  <table width="100%" class="table_form contentWrap">
      <volist name="fields" id="item">
        <tr>
            <th><strong>{$key}</strong><br/>{$item['comment']}</th>
            <td><input type="text" name="{$key}" style="width:400px;" class="input" value="{$item['value']}"></td>
        </tr>
      </volist>
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
