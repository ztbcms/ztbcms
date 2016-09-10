<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap ">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <p>最好是有选择性的重建，如果全部重建，当信息量比较大的时候会比较久！此操作不可逆！</p>
  </div>
  <div class="h_a">索引重建</div>
  <div class="table_full">
  <table width="100%" cellspacing="0">
      <form action="{:U("Search/create")}" method="post" name="myform">
        <thead>
          <tr>
            <th align="center" width="200">按照模型更新</th>
            <th align="center"></th>
          </tr>
        </thead>
        <tbody  height="200" class="nHover td-line">
          <tr>
            <th align="center" rowspan="6"><?php
            foreach($models as $_k=>$_v) {
				if (in_array($_v['modelid'], $config['modelid'])) {
					$model_datas[$_v['modelid']] = $_v['name'];
				}
			}
			echo Form::select($model_datas,$modelid,'name="modelid" size="2" style="height:200px; width:99%" onclick="change_model(this.value)"','不限制模型');
            ?></th>
          </tr>
          <tr>
            <th><font color="red">每轮更新
              <input type="text" name="pagesize" value="10" class="input" size="4">
              条信息</font></th>
          </tr>
          <tr>
            <th>
              <input type="button" name="dosubmit1" value=" 开始更新 " class="btn" onClick="myform.submit();"></th>
          </tr>
        </tbody>
      </form>
    </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
