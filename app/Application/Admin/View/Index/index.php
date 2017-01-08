<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>系统后台 - {$Config.sitename}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.6 -->
    <link rel="stylesheet" href="//cdn.zhutibang.cn/adminlte/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link href="https://cdn.bootcss.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
    <!-- Ionicons -->
    <!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">-->
    <!-- Theme style -->
    <link rel="stylesheet" href="//cdn.zhutibang.cn/adminlte/dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="//cdn.zhutibang.cn/adminlte/dist/css/skins/_all-skins.min.css">
    <!-- iCheck -->
    <link rel="stylesheet" href="//cdn.zhutibang.cn/adminlte/plugins/iCheck/flat/blue.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="//cdn.zhutibang.cn/adminlte/plugins/morris/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="//cdn.zhutibang.cn/adminlte/plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="//cdn.zhutibang.cn/adminlte/plugins/datepicker/datepicker3.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="//cdn.zhutibang.cn/adminlte/plugins/daterangepicker/daterangepicker-bs3.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="//cdn.zhutibang.cn/adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini fixed" style="height: 100%;">
<div class="wrapper">

    <header class="main-header">
        <!-- Logo -->
        <a href="javascript:void(0);" class="logo">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><b>A</b>LT</span>
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
                    {:tag("view_admin_top_menu")}
                    <li>
                        <a href="{$Config.siteurl}" class="home" target="_blank">前台首页</a>
                    </li>
                    <?php if(\Libs\System\RBAC::authenticate('Admin/Index/cache')){ ?>
                        <li><a href="javascript:;;" id="deletecache" data-url="{:U('Admin/Index/cache')}"  style="color:#FFF">缓存更新</a></li>
                    <?php } ?>
                    <!-- User Account: style can be found in dropdown.less -->
                    <li class="dropdown user user-menu">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <span class="hidden-xs">{$userInfo.nickname}({$userInfo.username})</span>
                        </a>

                        <ul class="dropdown-menu">
                            <li class="footer" style="text-align: center; margin: 6px">
                                <a href="{:U('Admin/Public/logout"')}">注销</a>

                            </li>
                        </ul>
                    </li>
                    <!-- Control Sidebar Toggle Button -->
<!--                    <li>-->
<!--                        <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>-->
<!--                    </li>-->
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
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
                            <a href="{$first_menu['url']}">
                                <i class="fa fa-dashboard"></i>
                                <span>{$first_menu['name']}</span>
<!--                                <i class="fa fa-angle-left pull-right"></i>-->
                            </a>

                            <ul class="treeview-menu">
                                <?php $second_items = $first_menu['items'];?>
                                <volist name="second_items" id="second_menu" key="second_menu_index">
                                    <li class=""><a href="{$second_menu['url']}">{$second_menu['name']}</a></li>
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
        <!-- Main content -->
        <section class="content" style="padding: 0;">
            <div id="B_frame">
                <iframe id="iframe_default" src="{:U('Main/index')}" style="height: 100%; width: 100%;display:none;" data-id="default" frameborder="0" scrolling="auto"></iframe>
            </div>

            <!-- /.row -->
            <!-- Main row -->
            
            <!-- /.row (main row) -->

        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
<!--    <footer class="main-footer">-->
<!--        <div class="pull-right hidden-xs">-->
<!--            <b>Version</b> 2.3.3-->
<!--        </div>-->
<!--        <strong>Copyright &copy; 2014-2015 <a href="http://almsaeedstudio.com">Almsaeed Studio</a>.</strong> All rights-->
<!--        reserved.-->
<!--    </footer>-->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Create the tabs -->
        <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
            <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
            <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
        </ul>
        <!-- Tab panes -->
        <div class="tab-content">
            <!-- Home tab content -->
            <div class="tab-pane" id="control-sidebar-home-tab">
                <h3 class="control-sidebar-heading">Recent Activity</h3>
                <ul class="control-sidebar-menu">
                    <li>
                        <a href="javascript:void(0)">
                            <i class="menu-icon fa fa-birthday-cake bg-red"></i>

                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                                <p>Will be 23 on April 24th</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <i class="menu-icon fa fa-user bg-yellow"></i>

                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>

                                <p>New phone +1(800)555-1234</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>

                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>

                                <p>nora@example.com</p>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <i class="menu-icon fa fa-file-code-o bg-green"></i>

                            <div class="menu-info">
                                <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>

                                <p>Execution time 5 seconds</p>
                            </div>
                        </a>
                    </li>
                </ul>
                <!-- /.control-sidebar-menu -->

                <h3 class="control-sidebar-heading">Tasks Progress</h3>
                <ul class="control-sidebar-menu">
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="control-sidebar-subheading">
                                Custom Template Design
                                <span class="label label-danger pull-right">70%</span>
                            </h4>

                            <div class="progress progress-xxs">
                                <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="control-sidebar-subheading">
                                Update Resume
                                <span class="label label-success pull-right">95%</span>
                            </h4>

                            <div class="progress progress-xxs">
                                <div class="progress-bar progress-bar-success" style="width: 95%"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="control-sidebar-subheading">
                                Laravel Integration
                                <span class="label label-warning pull-right">50%</span>
                            </h4>

                            <div class="progress progress-xxs">
                                <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="javascript:void(0)">
                            <h4 class="control-sidebar-subheading">
                                Back End Framework
                                <span class="label label-primary pull-right">68%</span>
                            </h4>

                            <div class="progress progress-xxs">
                                <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
                            </div>
                        </a>
                    </li>
                </ul>
                <!-- /.control-sidebar-menu -->

            </div>
            <!-- /.tab-pane -->
            <!-- Stats tab content -->
            <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
            <!-- /.tab-pane -->
            <!-- Settings tab content -->
            <div class="tab-pane" id="control-sidebar-settings-tab">
                <form method="post">
                    <h3 class="control-sidebar-heading">General Settings</h3>

                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Report panel usage
                            <input type="checkbox" class="pull-right" checked>
                        </label>

                        <p>
                            Some information about this general settings option
                        </p>
                    </div>
                    <!-- /.form-group -->

                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Allow mail redirect
                            <input type="checkbox" class="pull-right" checked>
                        </label>

                        <p>
                            Other sets of options are available
                        </p>
                    </div>
                    <!-- /.form-group -->

                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Expose author name in posts
                            <input type="checkbox" class="pull-right" checked>
                        </label>

                        <p>
                            Allow the user to show his name in blog posts
                        </p>
                    </div>
                    <!-- /.form-group -->

                    <h3 class="control-sidebar-heading">Chat Settings</h3>

                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Show me as online
                            <input type="checkbox" class="pull-right" checked>
                        </label>
                    </div>
                    <!-- /.form-group -->

                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Turn off notifications
                            <input type="checkbox" class="pull-right">
                        </label>
                    </div>
                    <!-- /.form-group -->

                    <div class="form-group">
                        <label class="control-sidebar-subheading">
                            Delete chat history
                            <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a>
                        </label>
                    </div>
                    <!-- /.form-group -->
                </form>
            </div>
            <!-- /.tab-pane -->
        </div>
    </aside>
    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
<!--    <div class="control-sidebar-bg"></div>-->
</div>
<!-- ./wrapper -->

<!-- jQuery 2.2.0 -->
<script src="//cdn.zhutibang.cn/adminlte/plugins/jQuery/jQuery-2.2.0.min.js"></script>

<!-- Bootstrap 3.3.6 -->
<script src="//cdn.zhutibang.cn/adminlte/bootstrap/js/bootstrap.min.js"></script>
<!-- FastClick -->
<script src="//cdn.zhutibang.cn/adminlte/plugins/fastclick/fastclick.js"></script>
<!-- AdminLTE App -->

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

                var def_iframe_height = document.body.clientWidth - $(".navbar.navbar-static-top").height();
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
                iframeJudge({'url' : $(this).prop('href')})
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
        })(jQuery);
    </script>
</literal>
</html>
