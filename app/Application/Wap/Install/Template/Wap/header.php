<?php if (!defined('CMS_VERSION')) exit(); ?>
 <html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><if condition=" isset($SEO['title']) && !empty($SEO['title']) ">{$SEO['title']}</if>{$SEO['site_title']}</title>
<meta name="description" content="{$SEO['description']}" />
<meta name="keywords" content="{$SEO['keyword']}" />
<meta name="viewport" content="width=device-width,initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
<style type="text/css">
*{margin:0; padding:0; list-style:none; }
body{ background:#fff; font:normal 12px/22px 宋体;  }
img{ border:0;  }
a{ text-decoration:none; color:#333;  }
a:hover{ color:#1974A1;  }
.nav_u li{ float:left; padding:0 15px;}
</style>
</head>
<body>
<div  style="width:100%; height:30px; background:#CC3;">
      <ul class="nav_u">
        <li><a href="{:U('Wap/Index/index')}">首页</a></li>
        <content action="category" catid="0"  order="listorder ASC" >
            <volist name="data" id="vo">
          <li> <a href="{:caturl($vo['catid'])}"  title="{$vo.catname} "> {$vo.catname} </a> </li>
            </volist>
            </content>

      </ul>
    </div>
   
