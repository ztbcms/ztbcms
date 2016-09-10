<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">搜索</div>
  <form method="post" action="{:U('loginlog')}">
  <div class="search_type cc mb10">
    <div class="mb10"> <span class="mr20">
    搜索类型：
    <select class="select_2" name="status" style="width:70px;">
        <option value='' <if condition="$_GET['status'] eq ''">selected</if>>不限</option>
        <option value="1" <if condition="$_GET['status'] eq '1'">selected</if>>登录成功</option>
         <option value="0" <if condition="$_GET['status'] eq '0'">selected</if>>登录失败</option>
      </select>
      用户名：<input type="text" class="input length_2" name="username" size='10' value="{$_GET.username}" placeholder="用户名">
      IP：<input type="text" class="input length_2" name="loginip" size='20' value="{$_GET.loginip}" placeholder="IP">
      时间：
      <input type="text" name="start_time" class="input length_2 J_date" value="{$_GET.start_time}" style="width:80px;">
      -
      <input type="text" class="input length_2 J_date" name="end_time" value="{$_GET.end_time}" style="width:80px;">
      <button class="btn">搜索</button>
      </span> </div>
  </div>
    <div class="table_list">
      <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td align="center" width="80">ID</td>
            <td width="80">用户名</td>
            <td>密码</td>
            <td align="center">状态</td>
            <td align="center">其他说明</td>
            <td align="center" width="120">时间</td>
            <td align="center" width="120">IP</td>
          </tr>
        </thead>
        <tbody>
          <volist name="data" id="vo">
          <tr>
            <td align="center">{$vo.id}</td>
            <td>{$vo.username}</td>
            <td>{$vo.password}</td>
            <td align="center"><if condition="$vo['status'] eq 1">登录成功<else /><font color="#FF0000">登录失败</font></if></td>
            <td align="center">{$vo.info}</td>
            <td align="center">{$vo.logintime|date="Y-m-d H:i:s",###}</td>
            <td align="center">{$vo.loginip}</td>
          </tr>
         </volist>
        </tbody>
      </table>
      <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
    </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>