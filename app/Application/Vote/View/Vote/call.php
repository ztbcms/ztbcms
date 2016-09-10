<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
<div class="h_a">调用说明</div>
  <div class="prompt_text"> 
    <p>1、根据自身情况选择一种调用方式，然后把调用代码复制粘贴到需要显示的模板再更新相关网页即可。</p>
   </div>
   <div class="table_full">
   <div class="h_a">JS调用代码</div>
   <table cellpadding=0 cellspacing=0 width="100%">
       <tr>
	     <th width="100"> 首页/栏目页调用（PHP动态调用）</th>
	     <td><input name="jscode1" id="jscode1" class="input" value='<script language="javascript" src="{$config.siteurl}index.php?g=Vote&m=Index&a=show&action=js&subjectid={$subjectid}&type=3"></script>' style="width:410px">
    <input type="button" onclick="$('#jscode1').select();document.execCommand('Copy');" value="复制代码至剪贴板" class="btn"></td>
      </tr>
      <th>内容页调用（PHP动态调用）</th>
	     <td> <input name="jscode2" id="jscode2" class="input" value='<script language="javascript" src="{$config.siteurl}index.php?g=Vote&m=Index&a=show&action=js&subjectid={$subjectid}&type=2"></script>' style="width:410px">
    <input type="button" onclick="$('#jscode2').select();document.execCommand('Copy');" value="复制代码至剪贴板" class="btn"></td>
      </tr>
      <th> JS调用代码（JS静态调用）</th>
	     <td><input name="jscode2" id="jscode3" class="input" value='<script language="javascript" src="{$config.siteurl}d/vote_js/vote_{$subjectid}.js"></script>' style="width:410px">
    <input type="button" onclick="$('#jscode3').select();document.execCommand('Copy');" value="复制代码至剪贴板" class="btn"></td>
      </tr>
      </table>
   </div>
</div>
</body>
</html>
