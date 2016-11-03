<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap">
  <div id="home_toptip"></div>
  <h2 class="h_a">系统信息</h2>
  <div class="home_info">
    <ul>
      <volist name="server_info" id="vo">
        <li> <em>{$key}</em> <span>{$vo}</span> </li>
      </volist>
    </ul>
  </div>

  <if condition="file_exists(APP_PATH . 'Install')">
    <h5 style="color:red;">* 您还没有删除 Install 模块，出于安全的考虑，我们建议您删除 Install 模块(/app/Application/Install) </h5>
  </if>
  
</div>
<script src="{$config_siteurl}statics/js/common.js"></script> 
<script src="{$config_siteurl}statics/js/artDialog/artDialog.js"></script> 
</body>
</html>
