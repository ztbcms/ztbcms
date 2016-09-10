<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
<div class="h_a">调用说明</div>
  <div class="prompt_text"> 
    <p>1、根据自身情况选择一种调用方式，然后把调用代码复制粘贴到需要显示的模板再更新相关网页即可。</p>
   </div>
   <div class="table_full">
   <div class="h_a">调用</div>
   <table cellpadding=0 cellspacing=0 width="100%">
       <tr>
	     <th width="40">JS调用</th>
	     <td><input name="jscode1" id="jscode1" class="input" value='<script language="javascript" src="{$config_siteurl}index.php?a=index&m=Index&g=Formguide&formid={$formid}&action=js"></script>' style="width:410px">
    <input type="button" onclick="$('#jscode1').select();document.execCommand('Copy');" value="复制代码至剪贴板" class="button" style="width:114px"></td>
      </tr>
      </table>
   </div>
</div>
</body>
</html>
