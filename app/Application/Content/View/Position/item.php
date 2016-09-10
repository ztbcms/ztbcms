<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form name="myform" class="J_ajaxForm" action="{:U("item")}" method="post">
    <div class="table_list"> 
    <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td width="10%" align="left"><input type="checkbox" value="" id="check_box" class="J_check_all" data-direction="x" data-checklist="J_check_x">全选</td>
            <td width="10%"  align="left">排序</td>
            <td width="10%"  align="left">ID</td>
            <td width=""  align="left">标题</td>
            <td width="15%" align="center">栏目名称</td>
            <td width="15%" align="center">发表时间</td>
            <td width="15%" align="center">管理操作</td>
          </tr>
        </thead>
        <tbody>
          <volist name="data" id="vo">
            <tr>
              <td align="left"><input type="checkbox" name="items[]" value="{$vo.id}-{$vo.modelid}" class="J_check" data-yid="J_check_y" data-xid="J_check_x"></td>
              <td align="left"><input name='listorders[{$vo.catid}-{$vo.id}]' type='text' size='3' value='{$vo.listorder}' class="input"></td>
              <td align="left">{$vo.id}</td>
              <td align="left">{$vo.data.title} </td>
              <td align="center">{:getCategory($vo['catid'],'catname')}</td>
              <td align="center">{$vo.data.inputtime|date="Y-m-d H:i:s",###}</td>
              <td align="center">
              <a href="{$vo.data.url}" target="_blank">原文</a> | 
              <a onClick="javascript:openwinx('{:U("Content/edit",array("catid"=>$vo['catid'],"id"=>$vo['id']  ))}','')" href="javascript:;">原文编辑</a>
              <?php
			   if(\Libs\System\RBAC::authenticate('item_manage')){
			  ?>
               | <a href="javascript:item_manage({$vo.id},{$vo.posid}, {$vo.modelid},'{$vo.data.title}')">信息管理</a>
              <?php
			   }
			  ?>
              </td>
            </tr>
          </volist>
        </tbody>
      </table>
      <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" data-action="{:U("Content/Position/public_item_listorder")}" type="submit">排序</button>
        <button class="btn mr10 J_ajax_submit_btn" type="submit">移出</button>
        <input type="hidden" value="{$posid}" name="posid">
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script type="text/javascript">
//信息管理
function item_manage(id, posid, modelid, name) {
    Wind.use("artDialog", "iframeTools", function () {
        art.dialog.open(GV.DIMAUB + "index.php?a=item_manage&m=Position&g=Content&id=" + id + "&modelid=" + modelid + "&posid=" + posid, {
            title: '修改--' + name,
            id: 'edit',
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
