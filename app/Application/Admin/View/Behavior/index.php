<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">功能说明</div>
  <div class="prompt_text">
    <ul>
      <li><font color="#FF0000">行为是系统中非常重要的一项功能，如果行为设置错误会导致系统崩溃或者不稳定的情况。</font></li>
      <li>行为标签都是程序开发中，内置在程序业务逻辑流程中！</li>
      <li>行为的增加，会<font color="#FF0000">严重影响</font>程序性能，请合理使用！</li>
    </ul>
    <p><b>行为来源：</b></p>
    <p>&nbsp;&nbsp;<font color="#FF0000">行为都是在程序开发中，在程序逻辑处理中添加的一个行为定义，由站长/开发人员自行扩展。此处的行为管理，并不能添加没有在程序中定义的行为标签！了解有那些行为定义，请进入官网查看！</font></p>
  </div>
  <div class="h_a">搜索</div>
  <div class="search_type  mb10">
    <form action="{$config_siteurl}index.php" method="get">
      <input type="hidden" value="Admin" name="g">
      <input type="hidden" value="Behavior" name="m">
      <input type="hidden" value="index" name="a">
      <div class="mb10">
        <div class="mb10"> <span class="mr20"> 行为标识：
          <input type="text" class="input length_2" name="keyword" style="width:200px;" value="{$keyword}" placeholder="请输入标识关键字...">
          <button class="btn">搜索</button>
          </span> </div>
      </div>
    </form>
  </div>
    <div class="table_list">
      <table width="100%">
        <thead>
          <tr>
            <td align="center">编号</td>
            <td>行为标识</td>
            <td>行为名称</td>
            <td align="center">规则说明</td>
            <td align="center">类型</td>
            <td align="center">状态</td>
            <td align="center">操作</td>
          </tr>
        </thead>
        <volist name="data" id="vo">
          <tr>
            <td align="center">{$vo.id}</td>
            <td>{$vo.name}</td>
            <td>{$vo.title}</td>
            <td>{$vo.remark}</td>
            <td align="center"><if condition="$vo['type'] eq 1">控制器<elseif condition="$vo['type'] eq 2"/>视图</if></td>
            <td align="center"><if condition="$vo['status']">正常<else /><font color="#FF0000">禁用</font></if></td>
            <td align="center"><a href="{:U('Behavior/edit',array('id'=>$vo['id']))}">编辑</a> | <if condition="$vo['status']"><a href="{:U('Behavior/status',array('id'=>$vo['id']))}">禁用</a><else /><font color="#FF0000"><a href="{:U('Behavior/status',array('id'=>$vo['id']))}">启用</a></font></if> | <a href="{:U('Behavior/delete',array('id'=>$vo['id']))}" class="J_ajax_del">删除</a></td>
          </tr>
        </volist>
      </table>
      <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
    </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
