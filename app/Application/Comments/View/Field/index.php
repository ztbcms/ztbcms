<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="nav">
      <ul class="cc">
        <div class="return"><a href="{$menuReturn.url}">{$menuReturn.name}</a></div>
        <li class="current"><a href="{:U('Field/index')}">字段管理</a></li>
        <li><a href="{:U('Field/add')}">添加字段</a></li>
      </ul>
  </div>
  <div class="table_list">
  <table width="100%" cellspacing="0" >
      <thead>
        <tr>
          <td width="70" align='center'>ID</td>
          <td align='center'>字段名称</td>
          <td width="80" align='center'>存储表</td>
          <td width="100" align='center'>字段标识</td>
          <td width="100" align='center'>字段类型</td>
          <td width="100" align='center'>是否必填</td>
          <td width="100" align='center'>操作</td>
        </tr>
      </thead>
      <tbody class="td-line">
      <volist name="data" id="vo">
        <tr>
          <td align='center'>{$vo.fid}</td>
          <td>{$vo.f}</td>
          <td align='center'><?php echo $vo['issystem']==0?"副表":"主表";?></td>
          <td align='center'>{$vo.fname}</td>
          <td align='center'>{$vo.ftype}</td>
          <td align='center'><?php echo $vo['ismust']==0?"否":"是";?></td>
          <td align='center'><a href="{:U("Comments/Field/edit",array("fid"=>$vo['fid']))}" >修改</a> | <a href="javascript:confirmurl('{:U("Comments/Field/delete",array("fid"=>$vo['fid']))}','确认要删除吗？')" >删除</a></td>
        </tr>
       </volist>
      </tbody>
    </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
