<!DOCTYPE html public>
<html >
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$site_config['sitename']}</title>
    <link rel="stylesheet" type="text/css" href="/statics/modules/home/css/normalize.css">
    <link rel="stylesheet" type="text/css" href="/statics/modules/home/css/component.css">
</head>
<body>

<div class="container demo-1">
    <div class="content">
        <div id="large-header" class="large-header">
            <canvas id="demo-canvas" width="1590" height="711"></canvas>
            <h2 class="main-title">{$site_config['sitename']}<br>
                <br/>
                <span style="font-size: 16px">{$site_config['siteinfo']}</span><br/>
                <br/>
                <span  style="font-size: 16px">
                    {if $enable_install }
                        <a title="立即安装框架" href="{:api_url('/install/index/index')}" style="color: white;text-decoration: none;">立即安装框架</a>
                    {/if}
                </span>
            </h2>
        </div>
    </div>
    <div class="footer" style="
    position: fixed;
    bottom: 20px;

    color: white;
    z-index: 100;
	width: 100%;
">
        <p style="width: 100%;display: block;text-align: center;">
            Copyright 2015-<?php echo date("Y"); ?>

            {if !$enable_install }
            <span>|</span>
            <a title="管理后台" href="{:api_url('/admin/login/index')}" style="color: white;text-decoration: none;">管理后台</a>
            {/if}
        </p>
    </div>
</div>
<script src="/statics/modules/home/js/TweenLite.min.js"></script>
<script src="/statics/modules/home/js/EasePack.min.js"></script>
<script src="/statics/modules/home/js/main.js"></script>
</body>
</html>
