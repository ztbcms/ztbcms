<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>分享资讯 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$config_siteurl}statics/js/wind.js"></script>
<script type="text/javascript">
    var catid = "{$catid}";
	var listenMsg = {start:function(){}};
	var nav = {userMenu:function(){},init:function(){}};
</script>
<link href="/favicon.ico" rel="shortcut icon">
<link type="text/css" href="{$model_extresdir}css/core.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" media="all"/>
<link type="text/css" href="{$model_extresdir}css/user.css" rel="stylesheet" media="all" />
<link type="text/css" href="{$config_siteurl}statics/css/admin_style.css" rel="stylesheet" />
<link href="{$config_siteurl}statics/js/artDialog/skins/default.css" rel="stylesheet" />
<style type="text/css">
.medal li em,li b{background: no-repeat;}
.bk10 {height: 10px;}
.blue{
	color:#266aae !important;
}
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
<body>
<template file="Member/Public/homeHeader.php"/>
<div class="user">
  <div class="center">
    <div class="main_nav">
      <div class="title"></div>
      <ul>
        <li class="share"><a href="{:U('Share/index')}">我分享的</a></li>
        <li class="deleting"><a href="{:U('Share/index',array('type'=>'check'))}">已审核的</a></li>
        <li class="audit"><a href="{:U('Share/index',array('type'=>'checking'))}">审核中的</a></li>
      </ul>
      <div class="action"><a class="on" target="_self" href="{:U('Share/add')}">发布分享</a></div>
      <div class="return"><a title="个人中心" target="_blank" href="{:U('Index/home')}">个人中心</a></div>
    </div>
    <div class="main"></div>
    <form method="post" action="{:U('Share/add')}" id="myform" name="myform" class="J_ajaxForms" enctype="multipart/form-data">
    <div class="minHeight500" >
      <div class="danceAddMain" id="danceAddMain">
        
        <ul class="danceAddMain_left">
          <li>
            <div class="dA_title">资讯分类：</div>
            <div class="dA_input">
              <select onchange="location.href = this.value">
                <option value="{:U('Share/add',array('step'=>2))}">请先选择分享栏目</option>
                {$categoryselect}
              </select> 请先选择栏目(阴影的表示没有权限)，选择栏目后页面会刷新。
            </div>
          </li>
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
          <li>
            <div class="dA_title"><if condition=" $info['star'] "><font color="red">*</font></if> {$info['name']}：</div>
            <div class="dA_input">
              {$info['form']}
              <if condition=" $info['tips'] ">{$info['tips']}</if>
            </div>
          </li>
          <?php
					     }
					}
		  ?>
          <if condition=" $catid ">
          <li>
            <div class="dA_title"></div>
            <div id="btnUploadBox" class="dA_input">
              <input name="dosubmit" type="submit" id="dosubmit" value="提交" class="button"/>
            </div>
          </li>
          </if>
        </ul>
      </div>
    </div>
    </form>
  </div>
<template file="Member/Public/homeFooter.php"/>
</div>
<script type="text/javascript" src="{$config_siteurl}statics/js/common.js"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>
<if condition=" $catid ">
<script language="javascript" type="text/javascript">	
$(function () {
    Wind.use('validate', 'ajaxForm','artDialog', function () {
		//javascript
        {$formJavascript}
        var form = $('form.J_ajaxForms');
        //ie处理placeholder提交问题
        if ($.browser.msie) {
            form.find('[placeholder]').each(function () {
                var input = $(this);
                if (input.val() == input.attr('placeholder')) {
                    input.val('');
                }
            });
        }
        //表单验证开始
        form.validate({
			//是否在获取焦点时验证
			onfocusout:false,
			//是否在敲击键盘时验证
			onkeyup:false,
			//当鼠标掉级时验证
			onclick: false,
            //验证错误
            showErrors: function (errorMap, errorArr) {
				//errorMap {'name':'错误信息'}
				//errorArr [{'message':'错误信息',element:({})}]
				try{
					$(errorArr[0].element).focus();
					art.dialog({
						id:'error',
						icon: 'error',
						lock: true,
						fixed: true,
						background:"#CCCCCC",
						opacity:0,
						content: errorArr[0].message,
						cancelVal: '确定',
						cancel: function(){
							$(errorArr[0].element).focus();
						}
					});
				}catch(err){
				}
            },
            //验证规则
            rules: {$formValidateRules},
            //验证未通过提示消息
            messages: {$formValidateMessages},
            //给未通过验证的元素加效果,闪烁等
            highlight: false,
            //是否在获取焦点时验证
            onfocusout: false,
            //验证通过，提交表单
            submitHandler: function (forms) {
                forms.submit();
                return true;
            }
        });
    });
});
</script>
</if>
</body>
</html>