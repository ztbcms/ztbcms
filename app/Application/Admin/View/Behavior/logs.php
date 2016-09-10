<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">搜索</div>
  <form method="post" action="{:U('logs')}">
  <div class="search_type cc mb10">
    <div class="mb10"> <span class="mr20"> 搜索类型：
      <select class="select_2" name="type" style="width:70px;">
        <option value="ruleid" <if condition="$type eq 'ruleid'">selected</if>>行为ID</option>
        <option value="guid" <if condition="$type eq 'guid'">selected</if>>标识</option>
      </select>
      关键字：
      <input type="text" class="input length_5" name="keyword" size='10' value="{$keyword}" placeholder="关键字">
      <button class="btn">搜索</button>
      <?php
	  if(D('Admin/Access')->isCompetence('deletelog')){
	  ?>
      <input type="button" class="btn" name="del_log_4" value="删除一月前数据" onClick="location='{:U("Logs/deletelog")}'"  />
      <?php
	  }
	  ?>
      </span> </div>
  </div>
    <div class="table_list">
      <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td align="center" width="30">ID</td>
            <td align="center" width="50" >行为ID</td>
            <td align="center">标识</td>
            <td align="center" width="150">时间</td>
          </tr>
        </thead>
        <tbody>
          <volist name="data" id="vo">
            <tr>
              <td align="center">{$vo.id}</td>
              <td align="center">{$vo.ruleid}</td>
              <td align="">{$vo.guid}</td>
              <td align="center">{$vo.create_time|date="Y-m-d H:i:s",###}</td>
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