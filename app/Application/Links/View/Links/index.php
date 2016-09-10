<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form name="myform" class="J_ajaxForm" action="{:U('Links/delete')}" method="post" >
  <div class="table_list">
  <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td width="20" align="center"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></td>
            <td width="20" align="center">ID</td>
            <td width="90" align="center">Logo</td>
            <td width="180" align="center">名称</td>
            <td >简介</td>
            <td width="120" align="center">分类</td>
            <td width="180" align="center">操作</td>
          </tr>
        </thead>
        <tbody>
        <volist name="data" id="vo">
          <tr>
            <td align="center"><input class="J_check" data-yid="J_check_y" data-xid="J_check_x" name="ids[]" value="{$vo.id}" type="checkbox"></td>
            <td align="center">{$vo.id}</td>
            <td align="center"><if condition=" !empty($vo['image']) "><img src="{$vo.image}"  width="80" height="30" /><else />暂无logo</if></td>
            <td align="center"><a href="{$vo.url}" target="_blank">{$vo.name}</a></td>
            <td  align="left">{$vo.description}</td>
            <td align="center"><if condition=" !empty($vo['termsid']) ">{$Terms[$vo['termsid']]}</if></td>
            <td align="center"><a href="javascript:confirmurl('{:U("Links/delete",array("id"=>$vo['id']))}','确认要删除吗？')">删除</a> | <a href="{:U("Links/edit",array("id"=>$vo['id']))}">编辑</a>
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
</body>
</html>
