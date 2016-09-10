<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">域名绑定</div>
  <form name="myform" action="{:U("Domains/Domains/add")}" method="post" class="J_ajaxForm">
  <div class="table_full">
  <table width="100%">
        <tbody>
        <tr>
          <td  width="160">模块：</td>
          <td><?php 
          foreach($Module as $r){
              $array_module[$r['module']] = $r['name'];
          }
          unset($array_module['Domains'],$array_module['Attachment'],$array_module['Content'],$array_module['Models'],$array_module['Template']);
          echo \Form::select($array_module,0,'name="module" id="module"','请选择需要绑定的模块');
          ?></td>
        </tr>
        <tr>
          <td>绑定域名（不要带http://）：</td>
          <td><input type="text" name="domain" value="" class="input" id="domain" size="30" style=" width:300px;"> 例如：user.ztbcms.com（多个用“|”隔开）</td>
        </tr>
        <tr>
          <td>是否开启：</td>
          <td><?php echo Form::radio(array("关闭","开启"),1,'name="status"','50');?></td>
        </tr>
      </tbody>
      </table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
