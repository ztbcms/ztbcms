<?php if (!defined('CMS_VERSION')) exit(); ?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>提示信息</title>
    <link rel="stylesheet" href="//cdn.bootcss.com/weui/0.4.3/style/weui.min.css">
</head>
<body>

<div class="container" id="container">
    <div class="msg">
        <div class="weui_msg">
            <div class="weui_icon_area"><i class="weui_icon_success weui_icon_msg"></i></div>
            <div class="weui_text_area">
                <h3 class="weui_msg_title">{$message}</h3>
                <!--                <p class="weui_msg_desc">内容详情，可根据实际需要安排</p>-->
            </div>
            <div class="weui_opr_area">
                <p class="weui_btn_area">
                    <a href="{$jumpUrl}" class="weui_btn weui_btn_default">确定</a>
                </p>
            </div>

        </div>
    </div>
</div>

<script language="javascript">
setTimeout(function(){
	location.href = '{$jumpUrl}';
}, parseInt('{$waitSecond}'));
</script>
</body>
</html>
