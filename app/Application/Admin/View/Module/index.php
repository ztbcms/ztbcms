<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">说明</div>
  <div class="prompt_text">
    <ul>
      <li>模块管理可以很好的扩展网站运营中所需功能！</li>
    </ul>
  </div>
  <div class="table_list">
    <table width="100%">
      <thead>
        <tr>
          <td align="center">应用图标</td>
          <td>应用介绍</td>
          <td align="center">安装时间</td>
          <td align="center">操作</td>
        </tr>
      </thead>
      <volist name="data" id="vo">
      <tr>
        <td>
            <div class="app_icon">
            <if condition=" $vo['icon'] ">
            <img src="{$vo.icon}" alt="{$vo.modulename}" width="80" height="80">
            <else/>
            <img src="{$config_siteurl}statics/images/modul.png" alt="{$vo.modulename}" width="80" height="80">
            </if>
            </div>
        </td>
        <td valign="top">
            <h3 class="mb5 f12"><if condition=" $vo['address'] "><a target="_blank" href="{$vo.address}">{$vo.modulename}</a><else />{$vo.modulename}</if></h3>
            <div class="mb5"> <span class="mr15">版本：<b>{$vo.version}</b></span> <span>开发者：<if condition=" $vo['author'] "><a target="_blank" href="{$vo.authorsite}">{$vo.author}</a><else />匿名开发者</if></span> <span>适配 ZtbCMS 最低版本：<if condition=" $vo['adaptation'] ">{$vo.adaptation}<else /><font color="#FF0000">没有标注，可能存在兼容风险</font></if></span> </div>
            <div class="gray"><if condition=" $vo['introduce'] ">{$vo.introduce}<else />没有任何介绍</if></div>
            <div> <span class="mr20"><a href="{$vo.authorsite}" target="_blank">{$vo.authorsite}</a></span> </div>
        </td>
        <td align="center"><if condition=" $vo['installtime'] "><span>{$vo.installtime|date='Y-m-d H:i:s',###}</span><else/>/</if></td>
        <td align="center">
          <?php
		  $op = array();
		  if(empty($vo['installtime'])){
			  $op[] = '<a href="'.U('install',array('module'=>$vo['module'])).'" class="btn btn_submit mr5">安装</a>';
		  }else{
			  if($vo['iscore'] == 0){
				  $op[] = '<a href="'.U('uninstall',array('module'=>$vo['module'])).'" class="J_ajax_del btn" data-msg="确定要卸载吗？<br/>注意：卸载模块后会删除对应模块目录！">卸载</a>';
			  }
			  if($vo['disabled']){
				  if($vo['iscore'] == 0){
					 $op[] = '<a href="'.U('disabled',array('module'=>$vo['module'])).'" class="btn mr5">禁用</a>'; 
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
