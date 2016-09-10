<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="nav">
    <ul class="cc">
      <li class="current"><a href="{:U('Vote/Vote/userlist')}&subjectid={$subjectid}">会员列表</a></li>
      <li><a href="{:U('Vote/Vote/statistics')}&subjectid={$subjectid}">投票结果</a></li>
    </ul>
  </div>
  <div class="table_list">
  <table width="100%" cellspacing="0">
          <thead>
            <tr>
              <td>用户名</th>
              <td width="155" align="center">投票时间</td>
              <td width="14%" align="center">IP</td>
            </tr>
          </thead>
          <tbody>
          <volist name="data" id="r">
            <tr>
              <td><if condition="$r['username']==''">游客<else />{$r['username']} </if></td>
              <td align="center" width="155">{$r['time']|date="Y-m-d H:i:s",###}</td>
              <td align="center" width="14%">{$r['ip']}</td>
            </tr>
           </volist> 
          </tbody>
        </table>
        <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
  </div>
</div>
</body>
</html>
