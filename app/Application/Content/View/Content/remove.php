<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="body_none" style="width:600px;">
<div class="wrap_pop"> 
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <ol>
      <li>信息移动只支持同模型之间进行移动，不支持夸模型移动！ </li>
    </ol>
  </div>
  <form class="J_ajaxForm" action="{:U('Content/remove','catid='.$catid)}" method="post">
    <div class="pop_cont pop_table" style="overflow-x:hidden;">
      <table width="100%">
        <thead>
          <tr>
            <td align="center">指定来源</td>
            <td align="center">目标栏目</td>
          </tr>
        </thead>
        <tr>
          <td align="center" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tr>
              <td align="center" valign="middle">
                 <ul class="switch_list cc J_radio_change" id="fromtype">
					<li><label><input type="radio" data-arr="ids" name="fromtype" value="0"  checked><span>从指定ID</span></label></li>
					<li><label><input type="radio" data-arr="catid_list" name="fromtype" value="1" ><span>从指定栏目</span></label></li>
				  </ul>
               </td>
            </tr>
            <tr>
              <td>
              <div id="ids" class="J_radio_tbody"><textarea class="length_4" name="ids" style=" height:240px;">{$ids}</textarea></div>
              <div id="catid_list" class="J_radio_tbody">
              <select name="fromid[]" id="fromid" size="2" multiple  class="select_4" style=" height:240px;">
                 <option value='0' style="background:#F1F3F5;color:blue;">从指定栏目</option>
                  {$source_string}
               </select>
              </div>
              </td>
            </tr>
          </table></td>
          <td align="center" valign="top">
             <select name="tocatid" id="tocatid"  size="2" class="select_4" style=" height:300px;">
                <option value='0' style="background:#F1F3F5;color:blue;">目标栏目</option>
                {$string}
              </select>
          </td>
        </tr>
      </table>
    </div>
    <div class="pop_bottom">
      <button class="btn fr" id="J_dialog_close" type="button">取消</button>
      <button type="submit" class="btn btn_submit J_ajax_submit_btn fr mr10">提交</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
