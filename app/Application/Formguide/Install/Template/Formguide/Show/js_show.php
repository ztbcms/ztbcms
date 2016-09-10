<?php if (!defined('CMS_VERSION')) exit(); ?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title>{$name}- 反馈表单</title>
<link href="{$config_siteurl}statics/css/admin_style.css" rel="stylesheet" />
<link href="{$config_siteurl}statics/js/artDialog/skins/default.css" rel="stylesheet" />
<script type="text/javascript">
//全局变量
var GV = {
    DIMAUB: "{$config_siteurl}",
    JS_ROOT: "statics/js/"
};
</script>
<script src="{$config_siteurl}statics/js/wind.js"></script>
<script src="{$config_siteurl}statics/js/jquery.js"></script>
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
.list-dot {
	padding-bottom: 10px
}
.list-dot li, .list-dot-othors li {
	padding: 5px 0;
	border-bottom: 1px dotted #c6dde0;
	font-family: "宋体";
	color: #bbb;
	position: relative;
	_height: 22px
}
.list-dot li span, .list-dot-othors li span {
	color: #004499
}
.list-dot li a.close span, .list-dot-othors li a.close span {
	display: none
}
.list-dot li a.close, .list-dot-othors li a.close {
	background: url("{$config_siteurl}statics/images/cross.png") no-repeat left 3px;
	display: block;
	width: 16px;
	height: 16px;
	position: absolute;
	outline: none;
	right: 5px;
	bottom: 5px
}
.list-dot li a.close:hover, .list-dot-othors li a.close:hover {
	background-position: left -46px
}
.list-dot-othors li {
	float: left;
	width: 24%;
	overflow: hidden;
}
</style>
</head>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
	<div class="h_a">简介</div>
<div class="prompt_text">
    <p>{$description}</p>
  </div>
  <form name="myform" id="myform" action="{:U('Index/post')}" method="post" class="J_ajaxForms" enctype="multipart/form-data">
  	<input type="hidden" name="formid" value="{$formid}"/>
    <div class="col-auto">
      <div class="h_a">表单内容</div>
      <div class="table_full">
        <table width="100%">
          <?php
					 if(is_array($forminfos)) {
					     foreach($forminfos as $field=>$info) {
						     if($info['isomnipotent']) continue;
							 if($info['formtype']=='omnipotent') {
							     foreach($forminfos as $_fm=>$_fm_value) {
								     if($_fm_value['isomnipotent']) {
									     $info['form'] = str_replace('{'.$_fm.'}',$_fm_value['form'],$info['form']);
									 }
								 }
							 }
					?>
          <tr>
            <th width="80"> <?php echo $info['name'];?> </th>
            <td><?php if($info['star']){ ?>
              <span class="must_red">*</span>
              <?php } ?>
              <?php echo $info['form'];?> <?php echo $info['tips'];?></td>
          </tr>
          <?php
} }
?>
<?php
//是否开启验证码
if($setting['isverify']){
?>
<tr>
            <th width="80"> 验证码 </th>
            <td><span class="must_red">*</span><input type="text" name="verify" id="int" value="" class="input">
            <img id="authCode" align="absmiddle" title="看不清？点击更换" src="{:U("Api/Checkcode/index","type=formguide&code_len=4&font_size=14&width=80&height=24&font_color=&background=")}">
            </td>
          </tr>
<?php	
}
?>
            </tbody>
        </table>
      </div>
    </div>
    <div class="btn_wrap" style="z-index:999;text-align: center;">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit"type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script type="text/javascript" src="{$config_siteurl}statics/js/common.js"></script> 
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script>
$(function(){
	$('#authCode').click(function(){
		var num = new Date().getTime();
        var rand = Math.round(Math.random() * 10000);
        var num = num + rand;
        $("#authCode").attr('src', $("#authCode").attr('src') + "&t=" + num);
	});
});
</script>
</body>
</html>
