<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">搜索</div>
  <form method="get" action="">
  <input type="hidden" value="Formguide" name="g">
  <input type="hidden" value="Info" name="m">
  <input type="hidden" value="index" name="a">
  <input type="hidden" value="1" name="search">
  <input type="hidden" value="{$formid}" name="formid">
    <div class="search_type cc mb10">
      <div class="mb10"> 
        <span class="mr20">时间：
        <input type="text" name="start_time" class="input length_2 J_date" value="{$Think.get.start_time}" style="width:80px;">-<input type="text" class="input length_2 J_date" name="end_time" value="{$Think.get.end_time}" style="width:80px;">
        <select class="select_2" name="type"style="width:70px;">
          <option value="0" <if condition=" $searchtype == '0' "> selected</if>>不限</option>
          <option value="1" <if condition=" $searchtype == '1' "> selected</if>>IP</option>
		  <option value="2" <if condition=" $searchtype == '2' "> selected</if>>用户名</option>
        </select>
        关键字：
        <input type="text" class="input length_2" name="keyword" style="width:200px;" value="{$keyword}" placeholder="请输入关键字...">
        <button class="btn">搜索</button>
        </span>
      </div>
    </div>
  </form>
  <form name="myform" class="J_ajaxForm" action="{:U('Info/delete')}" method="post" >
  <div class="table_list">
  <table width="100%" border="0" cellpadding="5" cellspacing="1" class="tableClass">
      <thead>
        <tr>
          <td width="35" align="center"><input type="checkbox" class="J_check_all" data-direction="y" data-checklist="J_check_y"></td>
          <td width="40" align="center">id</td>
          <td align="left">用户名</td>
          <td width='250' align="center">用户ip</td>
          <td width='250' align="center">时间</td>
          <td width='250' align="center">操作</td>
        </tr>
      </thead>
      <tbody>
        <volist name="data" id="vo">
        <tr>
          <td align="center"><input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x" name="dataid[]" value="{$vo.dataid}"></td>
          <td align="center">{$vo.dataid}</td>
          <td align="left">{$vo.username}</td>
          <td align="center"><font color=blue>{$vo.ip}</font></td>
          <td align="center">{$vo.datetime|date="Y-m-d H:i:s",###}</td>
          <td align="center"><a href="javascript:check('{$formid}', '{$vo.dataid}', '{$vo.username}');">查看</a> | <a href="{:U('Info/delete',array('formid'=>$formid,'dataid'=>$vo['dataid']))};" class="J_ajax_del">删除</a></td>
        </tr>
       </volist>
      </tbody>
    </table>
    <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">       
        <label class="mr20"><input type="checkbox" class="J_check_all" data-direction="y" data-checklist="J_check_y">全选</label>      
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">删除</button>
        <input type="hidden"name="formid" value="{$formid}">
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script type="text/javascript">
//详细信息查看
function check(id, did, title) {
	omnipotent("check", GV.DIMAUB+'index.php?a=public_view&m=Info&g=Formguide&formid=' + id +'&dataid='+did, '查看 ' + title+'---提交的信息', 1, '700px', '500px');
}
</script>
</body>
</html>
