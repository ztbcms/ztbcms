<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>系统后台 - {$Config.sitename}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="{$config_siteurl}statics/admin/theme/adminlte/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link href="{$config_siteurl}statics/admin/theme/adminlte/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- Theme style -->
    <link rel="stylesheet" href="{$config_siteurl}statics/admin/theme/adminlte/dist/css/AdminLTE.min.css">
    <!-- AdminLTE 皮肤. 可以从/statics/admin/theme/adminlte/dist/css/skins/目录中选择其中一个 -->
    <link rel="stylesheet" href="{$config_siteurl}statics/admin/theme/adminlte/dist/css/skins/skin-ztbcms.css">

    <!-- jQuery 2.2.0 -->
    <script src="{$config_siteurl}statics/admin/theme/adminlte/plugins/jQuery/jquery-2.2.3.min.js"></script>

    <!-- Bootstrap 3.3.6 -->
    <script src="{$config_siteurl}statics/admin/theme/adminlte/bootstrap/js/bootstrap.min.js"></script>

    <!-- tab -->
    <link rel="stylesheet" href="{$config_siteurl}statics/css/tab.css">

    <!-- iconfont -->
    <link rel="stylesheet" href="{$config_siteurl}statics/css/iconfont/iconfont.css">

</head>
<body class="hold-transition skin-blue sidebar-mini fixed" style="height: 100%;">
<div class="wrapper" style="position: absolute;">

    <header class="main-header">
        <!-- Logo -->
        <a href="javascript:void(0);" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>Z</b>TB</span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><b>{$Config.sitename}</b></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="top_navbar navbar navbar-static-top">
            <!-- Sidebar toggle button 控制左侧栏关闭、打开-->
<!--            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">-->
<!--                <span class="sr-only">Toggle navigation</span>-->
<!--            </a>-->
            <div class="navbar-collapse pull-left collapse" id="navbar-collapse" aria-expanded="false" style="height: 1px;">
                <ul class="nav navbar-nav">
                    <volist name="submenu_config" id="nav" key="index">
                        <if condition="$index EQ 0">
                            <li class="active"><a href="" data-id="{$nav['id']}">{$nav['name']}</a></li>
                            <else/>
                            <li><a href="" title="{$nav['name']}" data-id="{$nav['id']}">{$nav['name']}</a></li>
                        </if>
                    </volist>

                </ul>
            </div>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <?php
                    //获取当前登录用户信息
                    $userInfo = \Admin\Service\User::getInstance()->getInfo();
                    ?>
                    <if condition="$userInfo['role_id'] EQ 1">
                        {:tag("view_admin_top_menu")}
                        <li>
                            <a href="{$Config.siteurl}" class="home" target="_blank">前台首页</a>
                        </li>
                    </if>
                    <?php if(\Libs\System\RBAC::authenticate('Admin/Index/cache')){ ?>
                        <li><a href="javascript:;;" id="deletecache" data-url="{:U('Admin/Index/cache')}">缓存更新</a></li>
                    <?php } ?>
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="hidden-xs">{$userInfo.nickname}({$userInfo.username})</span>
                        </a>

                        <ul class="dropdown-menu">
                            <li class="footer" style="text-align: center; margin: 6px">
                                <a href="{:U('Admin/Public/logout')}">注销</a>

                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar" style="overflow: auto;">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- search form 搜索框 -->
<!--            <form action="#" method="get" class="sidebar-form">-->
<!--                <div class="input-group">-->
<!--                    <input type="text" name="q" class="form-control" placeholder="Search...">-->
<!--                    <span class="input-group-btn">-->
<!--                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>-->
<!--                </button>-->
<!--              </span>-->
<!--                </div>-->
<!--            </form>-->
            <!-- /.search form -->
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <volist name="submenu_config" id="top_menu" key="top_menu_index">
                <?php //var_dump($top_menu);?>
                <!-- 只显示一级菜单和二级菜单  -->
                <ul class="sidebar sidebar-menu" id="sidebar_{$top_menu['id']}" style="display: none">
                    <!-- 一级菜单   -->
                    <?php $first_items = $top_menu['items'];?>
                    <volist name="first_items" id="first_menu" key="first_menu_index">
                        <li class="treeview">
                            <?php
                                $_href = $first_menu['url'];
                                if(count($first_menu['items']) != 0){
                                    $_href = '#';
                                }
                            ?>
                            <a href="javascript:void(0);" data-url="{$_href}" data-id="{$first_menu['id']}" data-title="{$first_menu['name']}">
                                <i class="fa <?php echo $first_menu['icon'] ? 'iconfont '.$first_menu['icon'] : 'fa-dashboard';?>"></i>
                                <span>{$first_menu['name']}</span>
                                <if condition="count($first_menu['items']) == 0">
                                        <!-- 没有子项则直接当该一级栏目是一个页面 -->
                                        <i class="fa fa-angle-right pull-right" style="right: 3px;"></i>
                                    <else/>
                                        <!-- 有子项则展开 -->
                                        <i class="fa fa-angle-left pull-right"></i>
                                </if>

                            </a>

                            <ul class="treeview-menu">
                                <?php $second_items = $first_menu['items'];?>
                                <volist name="second_items" id="second_menu" key="second_menu_index">
                                    <li class="treeview"><a href="javascript:void(0);" data-url="{$second_menu['url']}" data-id="{$second_menu['id']}" data-title="{$second_menu['name']}">{$second_menu['name']}</a></li>
                                </volist>
                            </ul>
                        </li>
                    </volist>
                </ul>
            </volist>
        </section>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="position: fixed;left: 0;right: 0;">


        <!-- Tab栏  -->
        <div class="tab_container">
            <ul class="tab_lists" id="tab_lists">
            </ul>
        </div>

        <!-- 内容页 -->
        <section class="content" style="padding: 0;">
            <div id="B_frame" style="height: 100%">

            </div>
        </section>
        <input type="hidden" id="srcData" name="srcData" value="{:U('Admin/SelfInfo/index')}">
        <!-- /.content -->
    </div>

    <!-- /.content-wrapper -->
</div>
<!-- ./wrapper -->

<!--隐藏的模板-->
<!--tab项模板-->
<template style="display: none;" id="template_tab_item">
    <li class="tab_item" data-id="___ID___" title="___TITLE___">
        <div class="title" data-id="___ID___" data-url="___HREF__" data-title="___TITLE___">
            <p>___TITLE___</p>
        </div>
        <div data-id="___ID___" class="iconfont_container"><i class="iconfont icon-close"></i></div>
    </li>
</template>
<!--内容部分iframe-->
<template style="display: none;" id="template_iframe">
    <iframe id="iframe-___ID___" src="___HREF___" style="height: 100%; width: 100%;" data-id="___ID___" data-title="___TITLE___" data-url="___HREF___" frameborder="0" scrolling="auto"></iframe>
</template>

</body>
<literal>
    <script>
        var open_new_iframe;

        (function ($) {
            open_new_iframe = function(data){
                iframeJudge(data)
                updateTab(data)
            };

            //更新设置内容的高度
            update_content_height()

            //当文档窗口改变大小时触发
            $(window).on('resize', function () {
                setTimeout(function () {
                    update_content_height();
                }, 100);
            });

            //更新设置内容的高度
            function update_content_height(){
                var def_iframe_height = $(window).height() - $(".navbar.navbar-static-top").height() - $('.tab_container').height();
                $(".content-wrapper .content").height(def_iframe_height);
            }


            //点击顶级导航
            var $top_nav = $('nav.top_navbar .pull-left');
            $top_nav.on('click', 'a', function (e) {
                //取消事件的默认动作
                e.preventDefault();
                //终止事件 不再派发事件
                e.stopPropagation();

                //设置高亮
                var $btn = $('nav.top_navbar .pull-left a')
                $btn.removeClass('current')
                $(this).addClass('current')


                //显示左侧菜单
                var id = $(this).data('id');
                $('.sidebar.sidebar-menu').hide();
                $('#sidebar_' + id).show();

                //打开左侧菜单栏第一个可以展示的页面
                var $sidebar_items = $('#sidebar_' + id + ' .treeview a')
                if($sidebar_items.length > 0){
                    for(var i=0; i<$sidebar_items.length; i++){
                        var $target = $($sidebar_items[i])
                        var redirect_url = $target.data('url');
                        var id = $target.data('id');
                        var title = $target.data('title')
                        var data = {
                            'url' : redirect_url,
                            'id': id,
                            'title': title
                        }
                        if(redirect_url != '#' && redirect_url != ''){
                            iframeJudge(data)
                            updateTab(data)
                            return
                        }
                    }
                }

            });
            //默认选中第一个
            $('nav.top_navbar .pull-left a:first').trigger('click');

            //显示iframe
            function activeIframe(iframe_options){
                $('#B_frame iframe').hide();
                $('iframe#iframe-'+iframe_options.id).show();
            }

            //删除iframe
            function removeIframe(iframe_options){
                $('iframe#iframe-'+iframe_options.id).remove();
            }

            //判断显示或创建iframe
            function iframeJudge(options) {
                if(options.url == ''){
                    return;
                }
                var $target_iframe = $('iframe#iframe-'+options.id)

                if($target_iframe.length > 0){
                    //存在该iframe
                    activeIframe(options)
                    $target_iframe.prop('src', options.url);
                }else{
                    //不存在该iframe
                    //创建一个并加以标识
                    var html = $('#template_iframe').html();
                    var result_html = html.replace(/___ID___/g, options.id).replace(/___TITLE___/g, options.title).replace(/___HREF___/g, options.url)

                    $(result_html).appendTo('#B_frame');
                    activeIframe(options)
                }
                // $('iframe').prop('src', options.url);
            }

            //选中高亮某个tab
            function activeTab(tab_options){
                $('.tab_lists .tab_item').removeClass('active');
                $('#tab_lists .tab_item[data-id="' + tab_options.id +'"]').addClass('active');
            }

            function removeTab(tab_options){
                $('#tab_lists .tab_item[data-id="' + tab_options.id +'"]').remove();
            }

            function updateTab(options){
                var $tab = $('.tab_item[data-id="' + options.id +'"]');
                if($tab.length > 0){
                    //存在该Tab
                    activeTab(options)
                }else{
                    //不存在

                    //创建一个并加以标识
                    var html = $('#template_tab_item').html();
                    var result_html = html.replace(/___ID___/g, options.id).replace(/___TITLE___/g, options.title).replace(/___HREF___/g, options.url)
                    $(result_html).appendTo('#tab_lists');

                    activeTab(options)

                    changeTabWidth();
                }
            }

            //修改tab栏宽度
            function changeTabWidth(){
                var obj = $('.tab_container .tab_lists .tab_item');
                var width = obj.width();
                var length = obj.length;
                var max_width = $('#tab_lists').width();

                var new_width = 120;
                if(width * length >= max_width){
                    new_width = (max_width / length) < 45 ? 45 : (max_width / length);
                }else{
                    new_width = (max_width / length) > 120 ? 120 : (max_width / length);
                }
                obj.width(new_width);

                var title = $('.tab_container .tab_lists .tab_item .title');
                var title_width = new_width - 20;
                title.css('width', title_width);
            }

            //点击左侧菜单栏
            $('.main-sidebar').on('click', 'ul li a', function(e){
                e.preventDefault();
                e.stopPropagation();
                $('.treeview').removeClass('active');
                var redirect_url = $(this).data('url');
                var id = $(this).data('id');
                var title = $(this).data('title')
                var data = {
                    'url' : redirect_url,
                    'id': id,
                    'title': title
                }
                console.log(data)
                if(redirect_url != '' && redirect_url != '#'){

                    iframeJudge(data);
                    updateTab(data)
                    $(this).parents('li').addClass('active');
                }else{
                    $(this).parent().toggleClass('active');
                }
            });

            //点击tab
            $('.tab_container').on('click', '.tab_lists .tab_item .title', function(e){
                e.preventDefault();
                e.stopPropagation();

                // alert('点击tab')
                var redirect_url = $(this).data('url');
                var id = $(this).data('id');
                var title = $(this).data('title')
                var data = {
                    'url' : redirect_url,
                    'id': id,
                    'title': title
                }
                activeTab(data)
                activeIframe(data)

                activeMenu(data.id);

            });

            //菜单显示
            function activeMenu(id){
                $('.sidebar li').removeClass('active');
                $('.sidebar li').has('a[data-id='+id+']').addClass('active');

                $('.sidebar').hide();
                $('.sidebar').has('li.treeview.active').show();

                var tmp = $('.sidebar ul').has('li.treeview.active').attr('id');
                var menu_id = tmp.substr(8);
                $('.nav a').removeClass('current');
                $('.nav a[data-id='+menu_id+']').addClass('current');
            }

            //点击tab 关闭按钮
            var timer;
            $('.tab_container').on('click', '.tab_lists .iconfont_container', function(e){
                e.preventDefault();
                e.stopPropagation();
                var redirect_url = $(this).data('url');
                var id = $(this).data('id');
                var title = $(this).data('title')
                var data = {
                    'url' : redirect_url,
                    'id': id,
                    'title': title
                }

                removeTab(data)
                removeIframe(data)

                clearTimeout(timer);
                timer = setTimeout(function(){
                    changeTabWidth();
                }, 500);

            });

            //点击登陆用户
            $('.dropdown.user.user-menu').on('click', function(){
                $menu = $('.dropdown-menu');
                if($menu.css('display') == '' || $menu.css('display') == 'none'){
                    $menu.show();
                }else{
                    $menu.hide();
                }
            });

            //点击 缓存更新
            $('#deletecache').on('click', function(){
                iframeJudge({url: $(this).data('url')});
            });

            //点击 缓存更新
            $('#edit').on('click', function(){
                iframeJudge({url: $(this).data('url')});
            });

            //用于维持在线
            function online(){
                $.get('<?php echo U("Admin/Index/index");?>');
            }
            //维持在线
            setInterval(function(){
                online();
            }, 60 * 1000);
        })(jQuery);
    </script>
</literal>
</html>
