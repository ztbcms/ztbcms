 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <p>1、请在添加、修改栏目全部完成后，<a href="{:U("Category/public_cache")}">更新栏目缓存</a>，否则可能出现未知错误！</p>
    <p>2、栏目<font color="blue">ID</font>为<font color="blue">蓝色</font>才可以添加内容。可以使用“终极属性转换”进行转换！</p>
  </div>
  <form name="myform" action="{:U("Category/index")}" method="post" class="J_ajaxForm">
  <div class="table_list">
    <table width="100%">
        <thead>
          <tr>
            <td align='center'>排序</td>
            <td align='center'>栏目ID</td>
            <td>栏目名称</td>
            <td align='center'>栏目类型</td>
            <td>所属模型</td>
            <td align='center'>访问</td>
            <td align='center'>域名绑定须知</td>
            <td align='center'>管理操作</td>
          </tr>
        </thead>
        {$categorys}
      </table>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">排序</button>
      </div>
    </div>
  </div>
  </div>
</form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
