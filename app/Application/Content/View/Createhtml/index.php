<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap ">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <p>最好是有选择性的刷新，如果全部刷新，当信息量比较大的时候生成会比较久！</p>
  </div>
  <div class="h_a">刷新任务</div>
  <div class="table_full">
  <table width="100%" cellspacing="0">
      <form action="{:U("Content/Createhtml/index")}" method="post" name="myform">
        <tbody  height="200" class="nHover td-line">
          <tr>
            <th>重新生成首页
              <input type="button" name="dosubmit1" value=" 开始更新 " class="btn" onClick="myform.submit();"></th>
          </tr>
        </tbody>
      </form>
    </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
