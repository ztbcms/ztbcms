<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">

  <div class="h_a">{$operator['name']} - 字段管理</div>
  <form name="myform" class="J_ajaxForm" action="" method="post">
  <div class="table_full">
  <table width="100%" class="table_form contentWrap">
      <tr>
        <th>字段名</th>
        <th>值</th>
        <th>操作</th>
      </tr>
      <volist name="fields" id="item">
        <tr>
            <th><strong>{$key}</strong><br/>{$item['comment']}</th>
            <td>{$item['value']}</td>
            <td>
              <a class="delField" href="{:U('Index/delField')}&operator={$operator['tablename']}&key={$key}">删除</a>
            </td>
        </tr>
      </volist>
    </table>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">添加</button>
        <input name="modelid" type="hidden" value="{$modelid}" />
      </div>
    </div>
  </form>
</div>

<script>
  
</script>
</body>
</html>
