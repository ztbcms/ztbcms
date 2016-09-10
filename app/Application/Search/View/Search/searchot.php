<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form class="J_ajaxForm" action="{:U('Search/searchot')}" method="post">
    <div class="table_list">
      <table width="100%">
        <thead>
          <tr>
            <td width="20"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></td>
            <td width="100">关键字</td>
            <td>拼音</td>
            <td>搜索次数</td>
            <td>分词结果</td>
          </tr>
        </thead>
        <tbody>
          <volist name="data" id="r">
            <tr>
              <td><input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x" name="keyword[]" value="{$r.keyword}"></td>
              <td>{$r.keyword}</td>
              <td>{$r.pinyin}</td>
              <td>{$r.searchnums}</td>
              <td>{$r.data}</td>
            </tr>
          </volist>
        </tbody>
      </table>
      <div class="p10">
        <div class="pages">{$Page}</div>
      </div>
    </div>
    <div class="btn_wrap_pd">
      <button class="btn J_ajax_submit_btn" type="submit">删除</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
