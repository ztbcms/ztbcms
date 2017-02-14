 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form class="J_ajaxForm" action="{:U('Menu/index')}" method="post">
    <div class="table_list">
      <table width="100%">
        <thead>
          <tr>
            <td align="center">排序</td>
            <td align="center">ID</td>
            <td >菜单英文名称</td>
            <td align="center">状态</td>
            <td align="center">管理操作</td>
          </tr>
        </thead>
        {$categorys}
      </table>
      <div class="p10"><div class="pages"> {$Page} </div> </div>
     
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">排序</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
