<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">平台配置</div>
  <form action="{:U('Index/addOperator')}" method="post">
  <div class="table_full">
  <table class="table_form" width="100%" cellspacing="0">
  <tbody>
    <tr>
        <th>平台名称</th>
        <th>表名</th>
        <th>描述</th>
        <th>状态</th>
        <th>操作</th>
    </tr>
	<volist name="operators" id="item">
        <tr>
            <td>{$item['name']}</td>
            <td>ztb_sms_{$item['tablename']}</td>
            <td>{$item['remark']}</td>
            <td><if condition="$item['enable'] eq '1'">启用中<else/>未启用</if></td>
            <td>
                <if condition="$item['enable'] eq '0'"><a href="{:U('Index/choose')}&operator={$item['tablename']}">启用</a> | </if>
                <a href="{:U('Index/conf')}&operator={$item['tablename']}">参数设置</a> | 
                <a href="{:U('Index/model')}&operator={$item['tablename']}">模型管理</a> | 
                <a href="{:U('Index/addField')}&operator={$item['tablename']}">添加字段</a> |
                <a href="javascript:del('{$item['tablename']}')">删除平台</a>
            </td>
        </tr>
    </volist>
	</tbody>
    </table>
  </div>
  </form>
</div>

<script>
    function del(table){
        var r = confirm("确定删除此平台吗？");
        if(r){
            window.location.href = "{:U('Index/delOperator')}&operator=" + table;
        }
    }
</script>
</body>
</html>
