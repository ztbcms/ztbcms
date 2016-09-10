<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">搜索</div>
  <form name="myform" action="{$config_siteurl}index.php" method="get">
  <input type="hidden" value="Collection" name="g">
  <input type="hidden" value="Node" name="m">
  <input type="hidden" value="index" name="a">
  <div class="search_type cc mb10">
      <div class="mb10"> 
        <span class="mr20">
        关键字：
        <input type="text" class="input length_2" name="keyword" style="width:200px;" value="{$keyword}" placeholder="请输入关键字...">
        <button class="btn">搜索</button>
        </span>
      </div>
    </div>
  </form>
  <div class="table_list">
  <form name="myform" action="{:U('Node/delete')}" method="post" class="J_ajaxForm">
    <table width="100%">
      <thead>
        <tr>
          <td align="left" width="20"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></td>
          <td align="left">ID</td>
          <td align="left">名称</td>
          <td align="left">最后采集时间</td>
          <td align="left">内容操作</td>
          <td align="left">操作</td>
        </tr>
      </thead>
      <volist name="data" id="r">
        <tr>
          <td><input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x"  value="{$r.nodeid}" name="nodeid[]" id="{$r.nodeid}"></td>
          <td>{$r.nodeid}</td>
          <td>{$r.name}</td>
          <td><if condition=" $r['lastdate'] eq 0 ">还没有采集过
              <else />
              {$r.lastdate|date="Y-m-d H:i:s",###}</if></td>
          <td>[<a href="{:U('Node/col_url_list',  array('nodeid'=>$r['nodeid'])  )}" target="_blank">采集网址</a>] -> [<a href="{:U('Node/col_content',  array('nodeid'=>$r['nodeid'])  )}" target="_blank">采集内容</a>] -> [<a href="{:U('Node/publist',  array('nodeid'=>$r['nodeid'])  )}" target="_blank">内容发布</a>]</td>
          <td>[<a href="{:U('Node/public_test',  array('nodeid'=>$r['nodeid'])  )}" target="_blank">测试</a>] [<a href="{:U('Node/edit','nodeid='.$r['nodeid'])}">修改</a>] [<a href="javascript:void(0)"  onclick="copy_spider({$r.nodeid})">复制</a>] [<a href="{:U('Node/export','nodeid='.$r['nodeid'])}" target="_blank">导出</a>]</td>
        </tr>
      </volist>
    </table>
    <div class="p10">
      <div class="pages">{$Page}</div>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">删除节点</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript">
//复制
function copy_spider(id) {
    Wind.use("artDialog", "iframeTools", function () {
        art.dialog.open(GV.DIMAUB + "index.php?a=copy&m=Node&g=Collection&nodeid=" + id , {
            title: '复制采集节点' ,
            id: 'copy_spider',
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
