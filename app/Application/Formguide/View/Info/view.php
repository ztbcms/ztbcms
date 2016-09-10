<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="h_a">详细信息</div>
  <div class="table_full">
    <table width="100%">
      <tr>
        <td width="15%" align="center">名称</td>
        <td align="left">内容</td>
      </tr>
      <?php
if(is_array($fields)){
	foreach($fields as $key => $rs){
?>  
	<tr>
		<th width="15%" align="right"><?php echo $rs['name']?>:</th>
		<th align="left"><?php echo $forminfos[$rs['field']]?></th>
	</tr>
<?php 
	}
}
?>
    </table>
  </div>
</div>
</body>
</html>
