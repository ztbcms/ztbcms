<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="table_list">
  <table width="100%" cellspacing="0" >
      <thead>
        <tr>
          <td width="20">ID</td>
          <td>表名称</td>
          <td align='center' width="80">信息量</td>
        </tr>
      </thead>
      <tbody class="td-line">
      <volist name="data" id="vo">
        <tr>
          <td>{$vo.id}</td>
          <td>{$vo.tablename}</td>
          <td align='center'>{$vo.count}</td>
        </tr>
       </volist>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
