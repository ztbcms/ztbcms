<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">采集列表：{$url_list}</div>
  <div class="table_full">
  <table width="100%">
        <tr>
          <th align="right" width="100">总共</th>
          <td align="left">{$total}</td>
        </tr>
        <tr>
          <th align="right" width="100">重复记录</th>
          <td align="left">{$re}</td>
        </tr>
        <tr>
          <th align="right" width="100">新入库</th>
          <td align="left"><?php echo $total-$re;?></td>
        </tr>
    </table>
  </div>
  <div class="table_list">
    <table width="100%">
      <thead>
        <tr>
          <td align="left">标题</td>
          <td align="left">地址</td>
        </tr>
      </thead>
      <volist name="urllist" id="r">
        <tr>
          <td>{$r.title}</td>
          <td><a href="{$r.url}" target="_blank">{$r.url}</a></td>
        </tr>
      </volist>
    </table>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<?php 
if ($total_page > $page) {
	echo  "<script type='text/javascript'>location.href='".U('Node/col_url_list', array("page"=>$page+1,"nodeid"=>$nodeid) )."'</script>";
} 
?>
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
