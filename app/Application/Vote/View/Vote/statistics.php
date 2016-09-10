<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="nav">
    <ul class="cc">
      <li><a href="{:U('Vote/Vote/userlist')}&subjectid={$subjectid}">会员列表</a></li>
      <li class="current"><a href="{:U('Vote/Vote/statistics')}&subjectid={$subjectid}">投票结果</a></li>
    </ul>
  </div>
  <div class="table_list">
  <table width="100%" cellspacing="0" class="table-list">
        <thead>
          <tr>
            <td>投票选项</td>
            <td width="10%" align="center">投票数</td>
            <td width='30%' align="center">百分比</td>
          </tr>
        </thead>
        <tbody>
        <volist name="options" id="r">
        <if condition="$vote_data['total']=='0'">
            <php>$per=0;</php>
            <else />
            <php>$per=intval($r['stat']/$vote_data['total']*100);</php>
       	</if>
          <tr>
            <td>{$i},{$r['option']} </td>
            <td align="center">{$r.stat}</td>
            <td align="center">{$per} % </td>
          </tr>
        </volist>
        </tbody>
      </table>
  </div>
  <div class="h_a">投票总数 <strong><font color="#006600">{$vote_data['total']}</font></strong></div>
</div>
</body>
</html>
