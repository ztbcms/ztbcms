<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <block name="title">
        <title>系统后台 - {$Config.sitename}</title>
    </block>

    <block name="header-main">
        <!-- Bootstrap 3.x -->
        <link rel="stylesheet" href="{$config_siteurl}statics/admin/theme/adminlte/bootstrap/css/bootstrap.min.css">
        <!-- Font Awesome -->
        <link href="{$config_siteurl}statics/admin/theme/adminlte/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <!-- Theme style -->
        <link rel="stylesheet" href="{$config_siteurl}statics/admin/theme/adminlte/dist/css/AdminLTE.min.css">
        <!-- AdminLTE 皮肤. 可以从/statics/admin/theme/adminlte/dist/css/skins/目录中选择其中一个 -->
        <link rel="stylesheet" href="{$config_siteurl}statics/admin/theme/adminlte/dist/css/skins/skin-blue.css">

        <!-- jQuery 2.x -->
        <script src="{$config_siteurl}statics/admin/theme/adminlte/plugins/jQuery/jquery-2.2.3.min.js"></script>

        <!-- Bootstrap 3.3.6 -->
        <script src="{$config_siteurl}statics/admin/theme/adminlte/bootstrap/js/bootstrap.min.js"></script>

        <!-- layer.js -->
        <script src="{$config_siteurl}statics/admin/layer/layer.js"></script>

        <!-- vue.js -->
        <script src="{$config_siteurl}statics/js/vue/vue.js"></script>

        <script>
            /**
             * js资源加载完后进行全局初始化
             */
            ;(function () {

                $(document).ready(function () {
                    //注册 ajax加载时 显示加载框
                    $(document).ajaxStart(function () {
                        if (layer) {
                            window.__layer_loading_index = layer.load(1);
                        }
                    });
                    $(document).ajaxComplete(function () {
                        if (layer) {
                            layer.close(window.__layer_loading_index);
                        }
                    });
                    $(document).ajaxError(function () {
                        if (layer) {
                            layer.msg('网络繁忙，请稍后再试..');
                        }
                    })

                });

            })(jQuery);
        </script>
    </block>

    <block name="header"></block>

</head>
<body class="hold-transition skin-blue sidebar-mini fixed" style="height: 100%;">

    <block name="content">

    </block>

    <block name="footer"></block>

</body>

</html>
