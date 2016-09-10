<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <p>1、3G版本想不同栏目用不同的模板 请在下面进行修改</p>
    <p>2、请在修改栏目全部完成后，<a href="{:U("public_cache")}">清除缓存</a>，否则可能出现未知错误！</p>
    <p>3、模板请放在 app\Template\模板主题\Wap 下</p>
    <p><a href="{:U("Index/index")}" target="_blank">访问手机版</a></p>
  </div>
  <form name="myform" action="{:U("index")}" method="post" class="J_ajaxForm">
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
