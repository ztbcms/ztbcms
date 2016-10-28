<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="nav">
  	<div class="return"><a href="{:U('Model/index','menuid=93')}">返回模型管理</a></div>
    <ul class="cc">
      <li class="current"><a href="{:U('Field/index',array('modelid'=>$modelid)  )}">管理模型字段</a></li>
      <li ><a href="{:U('Field/add',array('modelid'=>$modelid)  )}">添加字段</a></li>
    </ul>
  </div>
  <form name="myform" action="{:U("Field/listorder")}" method="post" class="J_ajaxForm">
  <div class="table_list">
  <table width="100%" cellspacing="0" >
        <thead>
          <tr>
            <td width="70" align="center">排序</td>
            <td width="180">字段名</td>
            <td width="180">别名</td>
            <td width="100" align="center">类型</td>
            <td width="50" align="center">必填</td>
            <td width="50" align="center">搜索</td>
            <td width="50" align="center">排序</td>
            <td align="left">管理操作</td>
          </tr>
        </thead>
        <tbody class="td-line">
        <volist name="data" id="vo">
          <tr>
            <td align='center' width='70'><input name='listorders[{$vo.fieldid}]' type='text' size='3' value='{$vo.listorder}' class='input'></td>
            <td width='180'>{$vo.field}</td>
            <td width="180">{$vo.name}</td>
            <td width="100" align='center'>{$vo.formtype}</td>
            <td width="50" align='center'><font color="red"><if condition="$vo['minlength'] eq 1">√<else /> ╳</if></font></td>
            <td width="50" align='center'><font color="red"><if condition="$vo['issearch'] eq 1">√<else /> ╳</if></font></td>
            <td width="50" align='center'><font color="blue"><if condition="$vo['isorder'] eq 1">√<else /> ╳</if></font></td>
            <td align='left'>
                <a href="{:U("Field/edit",array("fieldid"=>$vo['fieldid'],"modelid"=>$vo['modelid']))}">修改</a> |
                <if condition=" in_array($vo['field'],$forbid_fields) ">
                    <font color="#BEBEBE"> 禁用 </font>|
                <else />
                     <if condition=" $vo['disabled'] eq 0 ">
                        <a href="{:U("Field/disabled",array("fieldid"=>$vo['fieldid'],"modelid"=>$vo['modelid'],"disabled"=>0))}">禁用</a> |
                     <else />
                        <a href="{:U("Field/disabled",array("fieldid"=>$vo['fieldid'],"modelid"=>$vo['modelid'],"disabled"=>1))}"><font color="#FF0000">启用</font></a> |
                     </if>
                </if>
                <if condition=" in_array($vo['field'],$forbid_delete) ">
                    <font color="#BEBEBE"> 删除</font>
                <else />
                    <a href="javascript:confirmurl('{:U("Field/delete",array("fieldid"=>$vo['fieldid'],"modelid"=>$vo['modelid']))}','确认要删除 『 {$vo.name} 』 吗？')">删除</a>
                </if>
            </td>
          </tr>
        </volist>
        </tbody>
      </table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">排序</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
