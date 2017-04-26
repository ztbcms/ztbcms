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
    <link rel="stylesheet" href="{$config_siteurl}statics/admin/theme/adminlte/dist/css/skins/skin-blue.css">

    <!-- jQuery 2.2.0 -->
    <script src="{$config_siteurl}statics/admin/theme/adminlte/plugins/jQuery/jquery-2.2.3.min.js"></script>

    <!-- Bootstrap 3.3.6 -->
    <script src="{$config_siteurl}statics/admin/theme/adminlte/bootstrap/js/bootstrap.min.js"></script>
</head>
<body class="hold-transition skin-blue sidebar-mini fixed" style="height: 100%;">
<div class="wrapper">

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
    <aside class="main-sidebar" style="height: 300px;overflow: scroll">
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
                        <li class="active treeview">
                            <?php
                                $_href = $first_menu['url'];
                                if(count($first_menu['items']) != 0){
                                    $_href = '#';
                                }
                            ?>
                            <a href="javascript:void(0);" data-url="{$_href}">
                                <i class="fa fa-dashboard"></i>
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
                                    <li class=""><a href="javascript:void(0);" data-url="{$second_menu['url']}">{$second_menu['name']}</a></li>
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
    <div class="content-wrapper">
        <!-- 内容页 -->
        <section class="content" style="padding: 0;">
            <div id="B_frame">
                <?php
                //获取当前登录用户信息
                $userInfo = \Admin\Service\User::getInstance()->getInfo();
                //根据不同的角色，修改不同的默认页面
                $default_page = U('Main/index');
                //非超级管理员，显示首页默认为 其权限下的第一个页面(检索顺序为: 一级菜单 -> 二级菜单 -> 三级菜单)
                if($userInfo['role_id'] != 1){
                    //一级目录
                    foreach ($submenu_config as $first_index => $first_menu){
                        if(!empty($first_menu['items'])){
                            //二级目录
                            foreach ($first_menu['items'] as $second_index => $second_menu){
                                if(!empty($second_menu['items'])){
                                    //三级目录
                                    foreach ($second_menu['items'] as $third_index => $third_menu){
                                        $default_page = $third_menu['url'];
                                        break;
                                    }
                                }else{
                                    $default_page = $second_menu['url'];
                                }
                                break;
                            }
                        }else{
                            $default_page = $first_menu['url'];
                        }
                        break;
                    }
                }
                ?>
                <iframe id="iframe_default" src="{$default_page}" style="height: 100%; width: 100%;display:none;" data-id="default" frameborder="0" scrolling="auto"></iframe>

            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
</div>
<!-- ./wrapper -->

</body>
<literal>
    <script>
        (function ($) {
            //iframe 加载事件
            var iframe_default = document.getElementById('iframe_default');
            $(iframe_default.contentWindow.document).ready(function () {
                setTimeout(function(){
//                    $('#loading').hide();
                },500);
                iframe_height();
                $(iframe_default).show();
            });

            function iframe_height(){

                var def_iframe_height = $(window).height() - $(".navbar.navbar-static-top").height();
                $("#B_frame").height(def_iframe_height);
            }

            //当文档窗口改变大小时触发
            $(window).on('resize', function () {
                setTimeout(function () {
                    iframe_height();
                }, 100);
            });

            //点击顶级导航
            $top_nav = $('nav.top_navbar .pull-left');
            $top_nav.on('click', 'a', function (e) {
                //取消事件的默认动作
                e.preventDefault();
                //终止事件 不再派发事件
                e.stopPropagation();
                //显示左侧菜单
                var id = $(this).data('id');
                $('.sidebar.sidebar-menu').hide();
                $('#sidebar_' + id).show();
            });
            //默认选中第一个
            $('nav.top_navbar .pull-left a:first').trigger('click');


            //判断显示或创建iframe
            function iframeJudge(options) {
                if(options.url == ''){
                    return;
                }
                $('iframe').prop('src', options.url);
            }

            $('.main-sidebar').on('click', 'ul li a', function(e){
                e.preventDefault();
                e.stopPropagation();
                var redirect_url = $(this).data('url');
                if(redirect_url != '' && redirect_url != '#'){
                    iframeJudge({'url' : redirect_url})
                }else{
                    $(this).parent().toggleClass('active');
                }
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
