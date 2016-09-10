<?php if (!defined('CMS_VERSION')) exit(); ?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title>添加新文章 - 系统后台 - {$Config.sitename} - by ZtbFCMS</title>
<Admintemplate file="Admin/Common/Cssjs"/>
<style type="text/css">
.col-auto {
	overflow: hidden;
	_zoom: 1;
	_float: left;
	border: 1px solid #c2d1d8;
}
.col-right {
	float: right;
	width: 210px;
	overflow: hidden;
	margin-left: 6px;
	border: 1px solid #c2d1d8;
}

body fieldset {
	border: 1px solid #D8D8D8;
	padding: 10px;
	background-color: #FFF;
}
body fieldset legend {
    background-color: #F9F9F9;
    border: 1px solid #D8D8D8;
    font-weight: 700;
    padding: 3px 8px;
}
.list-dot{ padding-bottom:10px}
.list-dot li,.list-dot-othors li{padding:5px 0; border-bottom:1px dotted #c6dde0; font-family:"宋体"; color:#bbb; position:relative;_height:22px}
.list-dot li span,.list-dot-othors li span{color:#004499}
.list-dot li a.close span,.list-dot-othors li a.close span{display:none}
.list-dot li a.close,.list-dot-othors li a.close{ background: url("{$config_siteurl}statics/images/cross.png") no-repeat left 3px; display:block; width:16px; height:16px;position: absolute;outline:none;right:5px; bottom:5px}
.list-dot li a.close:hover,.list-dot-othors li a.close:hover{background-position: left -46px}
.list-dot-othors li{float:left;width:24%;overflow:hidden;}
</style>
</head>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="nav">
    <ul class="cc">
      <li class="current"><a href="http://www.abc.com/index.php?m=Content&a=classlist&catid=2">网站推荐列表</a></li>
    </ul>
  </div>
  <div class="col-right">
    <div class="table_full">
      <table width="100%">
<?php
if(is_array($forminfos['senior'])) {
 foreach($forminfos['senior'] as $field=>$info) {
	if($info['isomnipotent']) continue;
	if($info['formtype']=='omnipotent') {
		foreach($forminfos['base'] as $_fm=>$_fm_value) {
			if($_fm_value['isomnipotent']) {
				$info['form'] = str_replace('{'.$_fm.'}',$_fm_value['form'],$info['form']);
			}
		}
		foreach($forminfos['senior'] as $_fm=>$_fm_value) {
			if($_fm_value['isomnipotent']) {
				$info['form'] = str_replace('{'.$_fm.'}',$_fm_value['form'],$info['form']);
			}
		}
	}
 ?>
         <tr>
          <td><b><?php echo $info['name']?></b><?php if($info['star']){ ?><font color="red">*</font><?php } ?></td>
        </tr>
        <tr>
          <td><?php echo $info['form'];?><?php echo $info['tips'];?></td>
        </tr>
        <h6>

<?php
   }
}
?>
         <tr>
          <td><b>状态</b></td>
        </tr>
        <tr>
          <td>
          <span class="switch_list cc">
			<label><input type="radio" name="info[status]" value="99" checked><span>审核通过</span></label>
			<label><input type="radio" name="info[status]" value="1"  ><span>待审核</span></label>
		 </span></td>
        </tr>
      </table>
    </div>
  </div>
  <div class="col-auto">
    <div class="h_a">文章内容</div>
    <div class="table_full">
      <table width="100%">
            <?php
if(is_array($forminfos['base'])) {
 foreach($forminfos['base'] as $field=>$info) {
	 if($info['isomnipotent']) continue;
	 if($info['formtype']=='omnipotent') {
		foreach($forminfos['base'] as $_fm=>$_fm_value) {
			if($_fm_value['isomnipotent']) {
				$info['form'] = str_replace('{'.$_fm.'}',$_fm_value['form'],$info['form']);
			}
		}
		foreach($forminfos['senior'] as $_fm=>$_fm_value) {
			if($_fm_value['isomnipotent']) {
				$info['form'] = str_replace('{'.$_fm.'}',$_fm_value['form'],$info['form']);
			}
		}
	}
 ?>
            <tr>
              <th width="80">
                <?php echo $info['name'];?> 
               </th>
              <td><?php if($info['star']){ ?><span class="must_red">*</span><?php } ?><?php echo $info['form'];?> <?php echo $info['tips'];?></td>
            </tr>
            <?php
} }
?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<script type="text/javascript" src="{$config_siteurl}statics/js/common.js"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>
</body>
</html>
