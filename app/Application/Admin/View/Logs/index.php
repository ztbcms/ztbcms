 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">搜索</div>
  <form method="post" action="{:U('index')}">
  <div class="search_type cc mb10">
    <div class="mb10"> <span class="mr20">
    搜索类型：
    <select class="select_2" name="status" style="width:70px;">
        <option value='' <if condition="$_GET['status'] eq ''">selected</if>>不限</option>
                <option value="0" <if condition="$_GET['status'] eq '0'">selected</if>>error</option>
                <option value="1" <if condition="$_GET['status'] eq '1'">selected</if>>success</option>
      </select>
      用户ID：<input type="text" class="input length_2" name="uid" size='10' value="{$_GET.uid}" placeholder="用户ID">
      IP：<input type="text" class="input length_2" name="ip" size='20' value="{$_GET.ip}" placeholder="IP">
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
            <td align="center" width="80" >用户ID</td>
            <td align="center" width="80" >状态</td>
            <td>说明</td>
            <td>GET</td>
            <td align="center" width="150">时间</td>
            <td align="center" width="120">IP</td>
          </tr>
        </thead>
        <tbody>
          <volist name="logs" id="vo">
            <tr>
              <td align="center">{$vo.id}</td>
              <td align="center">{$vo.uid}</td>
              <td align="center"><if condition="$vo['status'] eq '1'">success<else/>error</if></td>
              <td>{$vo.info}</td>
              <td>{$vo.get}</td>
              <td align="center">{$vo.time|date="Y-m-d H:i:s",###}</td>
              <td align="center">{$vo.ip}</td>
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
