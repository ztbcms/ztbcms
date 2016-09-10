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
      <form action="{:U("Content/Createhtml/update_urls")}" method="post" name="myform">
        <input type="hidden" name="dosubmit" value="1">
        <input type="hidden" name="type" value="lastinput">
        <thead>
          <tr>
            <th align="center" width="150">按照模型更新</th>
            <th align="center" width="386">选择栏目范围</th>
            <th align="center">选择操作内容</th>
          </tr>
        </thead>
        <tbody  height="200" class="nHover td-line">
          <tr>
            <th align="center" rowspan="6"><?php
            foreach($models as $_k=>$_v) {
				$model_datas[$_v['modelid']] = $_v['name'];
			}
			echo \Form::select($model_datas,$modelid,'name="modelid" size="2" style="height:200px; width:99%" onclick="change_model(this.value)"','不限制模型');
            ?></th>
          </tr>
          <tr>
            <th align="center" rowspan="6">
            <select name='catids[]' id='catids'  multiple="multiple"  style="height:200px; width:99%" title="按住“Ctrl”或“Shift”键可以多选，按住“Ctrl”可取消选择">
                <option value='0' selected>不限栏目</option>
                  {$string}
              </select></th>
            <th><font color="red">每轮更新
              <input type="text" name="pagesize" value="10" class="input" size="4">
              条信息</font></th>
          </tr>
          <tr>
            <th>更新所有信息
              <input type="button" name="dosubmit1" value=" 开始更新 " class="btn" onClick="myform.type.value='all';myform.submit();"></th>
          </tr>
          <if condition=" $modelid ">
          <tr>
            <th>更新最新发布的
              <input type="text" name="number" value="100" size="5" class="input">
              条信息
              <input type="button" class="btn" name="dosubmit2" value=" 开始更新 " onClick="myform.type.value='lastinput';myform.submit();"></th>
          </tr>
          <tr>
            <th>更新发布时间从
              <input type="text" name="fromdate" id="fromdate" value="" class="input J_date" size="8" />
              &nbsp; 到
              <input type="text" name="todate" id="todate" value="" size="10" class="input J_date" >
              &nbsp;的信息
              <input type="button" name="dosubmit3" value=" 开始更新 " class="btn" onClick="myform.type.value='date';myform.submit();"></th>
          </tr>
          <tr>
            <th>更新ID从
              <input type="text" name="fromid" value="0" class="input" size="8">
              到
              <input type="text" name="toid" size="8" class="input">
              的信息
              <input type="button" class="btn" name="dosubmit4" value=" 开始更新 " onClick="myform.type.value='id';myform.submit();"></th>
          </tr>
          </if>
        </tbody>
      </form>
    </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script language="JavaScript">
	function change_model(modelid) {
		window.location.href=GV.DIMAUB+'index.php?a=update_urls&m=Createhtml&g=Content&modelid='+modelid;
	}
</script>
</body>
</html>
