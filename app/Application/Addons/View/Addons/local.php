<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">说明</div>
  <div class="prompt_text">
    <ul>
      <li>只支持ZIP文件上传！</li>
      <li>文件名就是插件名称，只支持英文、数字、下划线！</li>
      <li>压缩包目录结构为插件自身！</li>
    </ul>
  </div>
  <div class="h_a">插件导入</div>
  <form name="myform" action="{:U('Addons/local')}" method="post" class="J_ajaxForm" enctype="multipart/form-data">
    <div class="table_full">
      <table width="100%" class="table_form">
        <tr>
          <th width="120">压缩包文件：</th>
          <td><input type="file" name="file" value="" />
            只支持.zip文件上传</td>
        </tr>
      </table>
    </div>
    <div class="">
      <button type="submit" class="btn btn_submit  mr10">提交</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>