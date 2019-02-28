<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <block name="title">
        <title>系统后台</title>
    </block>

    <block name="header-main">

        <!-- jQuery 2.x -->
        <script src="{$config_siteurl}statics/admin/theme/adminlte/plugins/jQuery/jquery-2.2.3.min.js"></script>
        <!-- vue.js -->
        <script src="{$config_siteurl}statics/js/vue/vue.js"></script>

        <!-- 时间格式化工具  -->
        <script src="https://unpkg.com/moment@2.24.0/moment.js"></script>
        <!-- 引入样式 -->
        <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
        <!-- 引入组件库 -->
        <script src="https://unpkg.com/element-ui/lib/index.js"></script>

        <style>
            /* vue相关  */
            [v-cloak] {
                display: none;
            }
        </style>
        <script>
            /**
             * js资源加载完后进行全局初始化
             */
            ;(function () {

                $(document).ready(function () {
                    // //注册 ajax加载时 显示加载框
                    // $(document).ajaxStart(function () {
                    //     if (layer) {
                    //         window.__layer_loading_index = layer.load(1);
                    //     }
                    // });
                    // $(document).ajaxComplete(function () {
                    //     if (layer) {
                    //         layer.close(window.__layer_loading_index);
                    //     }
                    // });
                    // $(document).ajaxError(function () {
                    //     if (layer) {
                    //         layer.msg('网络繁忙，请稍后再试..');
                    //     }
                    // })
                });

            })(jQuery);
        </script>
    </block>

    <block name="header"></block>

</head>
<body class="hold-transition skin-blue sidebar-mini fixed" style="height: 100%;background-color: #F8F8F8">

<block name="content">

</block>

<block name="footer"></block>


</body>

</html>
