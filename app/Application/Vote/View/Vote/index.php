<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form name="myform" action="{:U('Vote/delete')}" method="post" class="J_ajaxForm">
  <div class="table_list">
  <table width="100%">
      <thead>
        <tr>
          <td width="3%" align="center"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></td>
          <td width="22%" align="left">标题</td>
          <td width="11%" align="center">投票数</td>
          <td width="13%" align="center">开始时间</td>
          <td width="12%" align="center">结束时间</td>
          <td width="16%" align="center">发表时间</td>
          <td width="23%" align="center">管理操作</td>
        </tr>
      </thead>
      <tbody>
        <volist name="data" id="vo">
        <tr>
          <td align="center"><input type="checkbox" name="subjectid[]" class="J_check" data-yid="J_check_y" data-xid="J_check_x" value="{$vo['subjectid']}"></td>
          <td><a href=" {:U('Vote/Index/show',array("show_type"=>1,"subjectid"=>$vo['subjectid']))}" title="查看投票" target="_blank">{$vo.subject}</a> <font color=red></font></td>
          <td align="center"><font color=blue>{$vo['votenumber']}</font></td>
          <td align="center">{$vo['fromdate']}</td>
          <td align="center">{$vo['todate']}</td>
          <td align="center">{$vo.addtime|date="Y-m-d H:i:s",###}</td>
          <td align="center">
          <a href="javascript:confirmurl('{:U("Vote/clearvote","subjectid=$vo[subjectid]")}','确认要该投票的投票信息吗？');" >清除投票数</a> | <a href='javascript:;;' onclick="statistics({$vo.subjectid}, '{$vo.subject}')"> 统计</a> | <a href="{:U('Vote/edit',array('subjectid'=>$vo['subjectid']))}"  title="修改">修改</a> | <a href="javascript:call({$vo.subjectid}, '{$vo.subject}');">调用JS代码</a> | <a href="javascript:confirmurl('{:U("Vote/delete","subjectid=$vo[subjectid]")}','确认要删除吗？')" >删除</a>
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
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script>
//调用
function call(id) {
	omnipotent("call", GV.DIMAUB+'index.php?a=public_call&m=Vote&g=Vote&subjectid=' + id, "调用方式", 1, '700px', '500px');
}
//统计
function statistics(id, name) {
	omnipotent("statistics", GV.DIMAUB+'index.php?a=statistics&m=Vote&g=Vote&subjectid=' + id, '统计 ' + name, 1, '660px', '500px');
}
</script>
</body>
</html>
