 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <p>最好是有选择性的刷新，如果全部刷新，当信息量比较大的时候生成会比较久！</p>
  </div>
  <div class="h_a">刷新任务</div>
  <div class="table_full">
  <table width="100%" cellspacing="0">
      <form action="{:U("Content/Createhtml/category")}" method="post" name="myform">
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
            <th>栏目生成
              <input type="button" name="dosubmit1" value=" 开始更新 " class="btn" onClick="myform.type.value='all';myform.submit();"></th>
          </tr>
        </tbody>
      </form>
    </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script language="JavaScript">
	function change_model(modelid) {
		window.location.href=GV.DIMAUB+'index.php?a=category&m=Createhtml&g=Content&modelid='+modelid;
	}
</script>
</body>
</html>
