<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form name="myform" action="{:U('Formguide/index')}" method="post" class="J_ajaxForm">
  <div class="table_list">
  <table width="100%" border="0" cellpadding="5" cellspacing="1" class="tableClass">
      <thead>
        <tr>
          <td width="3%" align="center"><input type="checkbox" class="J_check_all" data-direction="y" data-checklist="J_check_y"></td>
          <td width="12%" align="left">名称(信息数)</td>
          <td width="20%" align="center">表名</td>
          <td  align="center">简介</td>
          <td width="12%" align="center">创建时间</td>
          <td width="35%" align="center">管理操作</td>
        </tr>
      </thead>
      <tbody>
        <volist name="data" id="vo">
        <tr>
          <td align="center"><input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x" name="formid[]" value="{$vo.modelid}"></td>
          <td>{$vo.name}<a href=" {:U('Formguide/Index/index',array("formid"=>$vo['modelid'],))}"  target="_blank">[访问前台]</a> ({$vo.items})</td>
          <td align="center"><font color=blue>{$vo['tablename']}</font></td>
          <td align="center">{$vo['description']}</td>
          <td align="center">{$vo['addtime']|date="Y-m-d H:i:s",###}</td>
          <td align="center">
          <a href="{:U('Info/index',array('menuid'=>$_GET['menuid'],'formid'=>$vo['modelid']))}" target="_blank">信息列表</a> | <a href="{:U('Field/add',array('menuid'=>$_GET['menuid'],'formid'=>$vo['modelid']))}"  target="_blank">添加字段</a> | <a href="{:U('Field/index',array('menuid'=>$_GET['menuid'],'formid'=>$vo['modelid']))}"  target="_blank">管理字段</a> | <a href="{:U('Formguide/edit',array('menuid'=>$_GET['menuid'],'formid'=>$vo['modelid']))}">修改</a> | 
          <if condition=" $vo['disabled'] eq 0 ">
          <a href="{:U('Formguide/status',array('disabled'=>0,'menuid'=>$_GET['menuid'],'formid'=>$vo['modelid']))}">禁用</a> | 
          <else />
          <a href="{:U('Formguide/status',array('disabled'=>1,'menuid'=>$_GET['menuid'],'formid'=>$vo['modelid']))}"><font color="#FF0000">启用</font></a> | 
          </if>
          <a href="javascript:confirmurl('{:U('Formguide/delete',array('menuid'=>$_GET['menuid'],'formid'=>$vo['modelid']))}','确认要删除 『 {$vo.name} 』 吗？')">删除</a> | <a href="javascript:call({$vo.modelid})"><font color=blue>调用</font></a>
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
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">删除</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script>
//调用
function call(id) {
	omnipotent("call", GV.DIMAUB+'index.php?a=public_call&m=Formguide&g=Formguide&formid=' + id, "调用方式", 1, '700px', '300px');
}
</script>
</body>
</html>
