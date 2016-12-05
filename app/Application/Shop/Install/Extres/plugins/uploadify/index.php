<?php	
exit;
require_once(dirname(__FILE__).'/../../inc/config.inc.php');

/*
**************************
(C)2010-2013 phpMyWind.com
update: 2012-9-15 12:14:40
person: Feng
**************************
*/


//初始化参数
$size  = isset($size)  ? $size  : '';
$num   = isset($num)   ? $num   : '';
$input = isset($input) ? $input : '';
$area  = isset($area)  ? $area  : '';
$frame = isset($frame) ? $frame : '';
$title = isset($title) ? $title : '';
$type  = isset($type)  ? $type  : '';


//获取上传文件类型
function GetUpType($type='')
{

	global $cfg_upload_img_type,
		   $cfg_upload_soft_type,
		   $cfg_upload_media_type;


	if($type == 'image')
	{
		$uptype = explode('|',$cfg_upload_img_type);
		$upstr  = '';
		foreach($uptype as $v)
		{
			if(!empty($v))
			{
				$upstr .= '*.'.$v.';';
			}
		}
		return $upstr;
	}

	else if($type == 'soft')
	{
		$uptype = explode('|',$cfg_upload_soft_type);
		$upstr  = '';
		foreach($uptype as $v)
		{
			if(!empty($v))
			{
				$upstr .= '*.'.$v.';';
			}
		}
		return $upstr;
	}

	else if($type == 'media')
	{
		$uptype = explode('|',$cfg_upload_media_type);
		$upstr  = '';
		foreach($uptype as $v)
		{
			if(!empty($v))
			{
				$upstr .= '*.'.$v.';';
			}
		}
		return $upstr;
	}

	else if($type == 'all')
	{
		$alltype = $cfg_upload_img_type.'|'.$cfg_upload_soft_type.'|'.$cfg_upload_media_type;
		$uptype  = explode('|',$alltype);
		$upstr   = '';
		foreach($uptype as $v)
		{
			if(!empty($v))
			{
				$upstr .= '*.'.$v.';';
			}
		}
		return $upstr;
	}

	else
	{
		return $type;
	}
}


//获取上传文件描述
function GetUpDesc($desc)
{
	if($desc == 'image')      return '图像类型:';

	else if($desc == 'soft')  return '软件类型:';

	else if($desc == 'media') return '媒体类型:';

	else if($desc == 'all')   return '所有类型:';

	else return $desc;
}


//引入水印配置文件
require_once(PHPMYWIND_DATA.'/watermark/watermark.inc.php');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Uploadify</title>
<link rel="stylesheet" type="text/css" href="uploadify.css">
</head>

<body>
<script type="text/javascript" src="jquery.min.js"></script>
<script type="text/javascript" src="jquery.uploadify.js"></script>
<script type="text/javascript">

//设置上传后样式
function SetImgContent(data)
{
	if(data == '')
	{
		alert('您上传的文件可能超过php.ini设置的最大内容提交大小[<?php echo get_cfg_var('post_max_size'); ?>]！');
		return;
	}
	else
	{
		var resjson = data.split(",");
	
		if(resjson[0] == 0)
		{
			alert(resjson[1]);
			return;
		}
		else
		{
			var sLi = "";
			sLi += '<li class="img">';
			sLi += '<img src="../../../' + resjson[2] + '" width="100" height="100" onerror="this.src=\'nopic.png\'">';
			sLi += '<input type="hidden" name="fileurl_tmp[]" value="' + resjson[2] + '">';
			sLi += '<a href="javascript:void(0);">删除</a>';
			sLi += '</li>';
			return sLi;
		}
	}
}


//删除上传元素DOM并清除目录文件
function SetUploadFile()
{
	$("ul li").each(function(l_i){
		$(this).attr("id", "li_" + l_i);
	})
	$("ul li a").each(function(a_i){
		$(this).attr("rel", "li_" + a_i);
	}).click(function(){
		$.get(
			'uploadify.php',
			{action:"del", filename:$(this).prev().val()},
			function(){}
		);
		$("#" + this.rel).remove();
	})
}


$(function() {
	$('#uploadify').uploadify({
		'formData'        : {
			'sessionid'   : '<?php echo session_id(); ?>',
			'timestamp'   : '<?php echo time();?>',
			'token'       : '<?php echo md5('unique_salt'.time()); ?>'
		},
		'queueSizeLimit'  : '<?php echo @$num; ?>',
		'fileSizeLimit'   : '<?php echo @$size; ?>',
		'fileTypeExts'    : '<?php echo @GetUpType($type); ?>',
		'fileTypeDesc'    : '<?php echo @GetUpDesc($desc); ?>',
		'buttonImage'     : 'select.png',
		'queueID'         : 'fileQueue',
		'onUploadStart'   : function(file){
			$('#uploadify').uploadify('settings', 'formData', {'iswatermark':$("#iswatermark").attr("checked")});
		},
		'onUploadSuccess' : function(file, data, response){
			$(".fileWarp ul").append(SetImgContent(data));
			SetUploadFile();
		}
	});


	//移动代码开始
	var _move = false;
	var ObjT = ".MainTit";
	var ObjW = ".Wrap";


	//鼠标离控件左上角的相对位置
	var _x,_y,_top,_left;


	//初始化窗口位置
	_top  = parseInt($(window.parent.window).height()/2)-208 + $(window.parent.document).scrollTop();
	_left = parseInt($(window.parent.window).width()/2)-245;
	$(ObjW).css({"top":_top,"left":_left});


	//浏览器窗口发生变化时窗口位置
	$(window).resize(function(){
		_top  = parseInt($(window.parent.window).height()/2)-208 + $(window.parent.document).scrollTop();
		_left = parseInt($(window.parent.window).width()/2)-245;
		$(ObjW).css({"top":_top,"left":_left});
	});


	//鼠标按下时允许进行移动操作
	$(ObjT).mousedown(function(e){
		_move = true;
		_x = e.pageX - parseInt($(ObjW).css("left"));
		_y = e.pageY - parseInt($(ObjW).css("top"));
	});


	$(document).mousemove(function(e){
		if(_move){

			//移动时根据鼠标位置计算控件左上角的绝对位置
			var x = e.pageX - _x;
			var y = e.pageY - _y;
	
			//控件新位置
			$(ObjW).css({top:y,left:x});
		}
	}).mouseup(function(){
		_move = false;
	});
	
	
	/*点击保存按钮时
	 *判断允许上传数，检测是单一文件上传还是组文件上传
	 *如果是单一文件，上传结束后将地址存入$input元素
	 *如果是组文件上传，则创建input样式，添加到$input后面
	 *隐藏父框架，清空列队，移除已上传文件样式*/
	$("#SaveBtn").click(function(){

		if(<?php echo $num ;?> > 1)
		{
			var fileurl_tmp = "";

			$("input[name^='fileurl_tmp']").each(function(){
				fileurl_tmp += '<li rel="'+ this.value +'"><input type="text" name="<?php echo $input; ?>[]" value="'+ this.value +'" /><a href="javascript:void(0);" onclick="ClearPicArr(\''+ this.value +'\')">删除</a></li>';
			});

			$(window.parent.document).find("#<?php echo $area ;?>").append(fileurl_tmp);
		}
		else
		{
			$(window.parent.document).find("#<?php echo $input ;?>").val($("input[name^='fileurl_tmp']").val());
		}

		$(window.parent.document).find("#<?php echo $frame ;?>").remove();

	});


	/*点击关闭或取消按钮时
	**隐藏父框架，清空列队，移除已上传文件样式*/
	$(".Close, #CancelBtn").click(function(){
		$("#<?php echo $frame ;?>", window.parent.document).remove();
		//$('#uploadify').uploadifyClearQueue();
		//$(".fileWarp ul li").remove();
	});
});
</script>
<div class="W">
	<div class="Bg">
	</div>
	<div class="Wrap">
		<div class="Title">
			<h3 class="MainTit"><?php echo $title; ?></h3>
			<a href="javascript:;" title="关闭" class="Close"> </a>
		</div>
		<div class="Cont">
			<p class="Note">最多上传<strong><?php echo $num; ?></strong>个附件,单文件最大<strong><?php echo GetRealSize($size); ?></strong>,类型<strong><?php echo GetUpType($type); ?></strong></p>
			<div class="flashWrap">
				<input name="uploadify" id="uploadify" type="file" multiple="true" />
				<span><input type="checkbox" name="iswatermark" id="iswatermark"  <?php if($cfg_markswitch=='Y') echo 'checked="checked"' ?> /><label>是否添加水印</label></span>
			</div>
			<div class="fileWarp">
				<fieldset>
					<legend>列表</legend>
					<ul>
					</ul>
					<div id="fileQueue">
					</div>
				</fieldset>
			</div>
			<div class="btnBox">
				<button class="btn" id="SaveBtn">保存</button>
				&nbsp;
				<button class="btn" id="CancelBtn">取消</button>
			</div>
		</div>
		<!--[if IE 6]>
		<iframe frameborder="0" style="width:100%;height:100px;background-color:transparent;position:absolute;top:0;left:0;z-index:-1;"></iframe>
		<![endif]-->
	</div>
</div>
</body>
</html>