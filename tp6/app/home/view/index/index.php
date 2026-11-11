<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$site_config['sitename']}</title>
    <link rel="stylesheet" type="text/css" href="/statics/modules/home/css/normalize.css">
    <link rel="stylesheet" type="text/css" href="/statics/modules/home/css/component.css">
</head>
<body>

<div id="large-header" class="large-header">
    <canvas id="header-canvas"></canvas>
    <h2 class="main-title">
        <span class="site-name">{$site_config['sitename']}</span>
        <span class="site-info">{$site_config['siteinfo']}</span>
        {if $enable_install}
        <a class="link" title="立即安装框架" href="{:api_url('/install/index/index')}">立即安装框架</a>
        {/if}
    </h2>
</div>
<footer class="page-footer">
    <p>
        Copyright 2015-{:date('Y')}
        {if !$enable_install}
        <span>|</span>
        <a class="link" title="管理后台" href="{:api_url('/admin/login/index')}">管理后台</a>
        {/if}
    </p>
</footer>

<script src="/statics/modules/home/js/main.js"></script>
</body>
</html>
