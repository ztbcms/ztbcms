 
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap J_check_wrap">
  <div class="table_list">
    <table width="100%">
      <thead>
        <tr>
          <td>模块介绍</td>
          <td>安装时间</td>
          <td>操作</td>
        </tr>
      </thead>
      <volist name="data" id="vo">
      <tr>
        <td valign="top">
            <h3 style="font-size: 16px;" class="mb5 f16">{$vo.modulename}</h3>
            <div class="mb5"> <span class="mr15">版本：<b>{$vo.version}</b></span> <span>开发者：<if condition=" $vo['author'] "><a target="_blank" href="{$vo.authorsite}">{$vo.author}</a><else />匿名开发者</if></span> <span>适配 ZTBCMS 最低版本：<if condition=" $vo['adaptation'] ">{$vo.adaptation}<else /><font color="#FF0000">没有标注，可能存在兼容风险</font></if></span> </div>
            <div class="gray"><if condition=" $vo['introduce'] ">{$vo.introduce}<else />没有任何介绍</if></div>
            <div> <span class="mr20"><a href="{$vo.address}" target="_blank">{$vo.address}</a></span> </div>
        </td>
        <td><if condition=" isset($vo['installtime']) "><span>{$vo.installtime|date='Y-m-d H:i:s',###}</span><else/>未安装</if></td>
        <td>
          <?php
		  $op = array();
		  if(empty($vo['installtime'])){
			  $op[] = '<a href="'.U('install',array('module'=>$vo['module'])).'" class="btn btn_submit mr5">安装</a>';
		  }else{
			  if($vo['iscore'] == 0){
				  $op[] = '<a href="'.U('uninstall',array('module'=>$vo['module'])).'" class="J_ajax_del btn btn-danger" data-msg="确定要卸载吗？<br/>注意：卸载模块后会删除对应模块目录！" >卸载</a>';
			  }
			  if($vo['disabled']){
				  if($vo['iscore'] == 0){
					 $op[] = '<a href="'.U('disabled',array('module'=>$vo['module'])).'" class="btn mr5 btn-warning">禁用</a>';
				  }
			  }else{
				  $op[] = '<a href="'.U('disabled',array('module'=>$vo['module'])).'" class="btn btn_submit  mr5">启用</a>';
			  }
		  }
		  echo implode('  ',$op);
		  ?>
        </td>
      </tr>
      </volist>
    </table>
  </div>
  <div class="p10">
        <div class="pages">{$Page}</div>
   </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
