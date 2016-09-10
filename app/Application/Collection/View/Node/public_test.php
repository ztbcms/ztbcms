<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="table_list">
    <table width="100%">
      <thead>
        <tr>
          <td align="left">标题</td>
          <td align="left">地址</td>
          <td align="center">操作</td>
        </tr>
      </thead>
      <volist name="urllist" id="r">
        <tr>
          <td>{$r.title}</td>
          <td><a href="{$r.url}" target="_blank">{$r.url}</a></td>
          <td><a href="javascript:void(0)" onclick="show_content('{$r.url|urlencode}')">查看</a></td>
        </tr>
      </volist>
    </table>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
function show_content(url) {
    Wind.use("artDialog", "iframeTools", function () {
        art.dialog.open(GV.DIMAUB + "index.php?a=public_test_content&m=Node&g=Collection&nodeid={$nodeid}&url="+url , {
            title: '内容查看' ,
            id: 'show_content',
            lock: true,
            fixed: true,
            background: "#CCCCCC",
            opacity: 0
        });
    });
}
</script>
</body>
</html>
