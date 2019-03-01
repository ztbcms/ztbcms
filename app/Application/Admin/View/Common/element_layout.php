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
        <script src="{$config_siteurl}statics/admin/theme/elementui/momentjs/2.24.0.js"></script>

        <!--  Element UI START  -->
        <!-- 引入样式 -->
        <link rel="stylesheet" href="{$config_siteurl}statics/admin/theme/elementui/elementui_2.5.4/index.css">
        <!-- 引入组件库 -->
        <script src="{$config_siteurl}statics/admin/theme/elementui/elementui_2.5.4/index.js"></script>
        <!--  Element UI END  -->

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
                    //注册 ajax加载时 显示加载框
                    $(document).ajaxStart(function () {
                        if (ELEMENT) {
                            //显示时间
                            window.__GLOBAL_ELEMENT_LOADING_INSTANCE_show_time = Date.now();
                            //load实例
                            window.__GLOBAL_ELEMENT_LOADING_INSTANCE = window.ELEMENT.Loading.service({
                                lock: true,
                                text: '',
                                // spinner: 'el-icon-loading',
                                // background: 'rgba(0, 0, 0, 0.7)'
                            });
                        }
                    });
                    $(document).ajaxComplete(function () {
                        if (window.__GLOBAL_ELEMENT_LOADING_INSTANCE) {
                            //保证Loading显示至少1秒
                            var timeout = 0;
                            if(Date.now() - window.__GLOBAL_ELEMENT_LOADING_INSTANCE_show_time < 1000){
                                timeout = 1000 - (Date.now() - window.__GLOBAL_ELEMENT_LOADING_INSTANCE_show_time);
                            }

                            setTimeout(function(){
                                window.__GLOBAL_ELEMENT_LOADING_INSTANCE.close()
                            }, timeout)

                        }
                    });
                    $(document).ajaxError(function () {
                        if (window.__GLOBAL_ELEMENT_LOADING_INSTANCE) {
                            //保证Loading显示至少1秒
                            var timeout = 0;
                            if(Date.now() - window.__GLOBAL_ELEMENT_LOADING_INSTANCE_show_time < 1000){
                                timeout = 1000 - (Date.now() - window.__GLOBAL_ELEMENT_LOADING_INSTANCE_show_time);
                            }

                            setTimeout(function(){
                                window.__GLOBAL_ELEMENT_LOADING_INSTANCE.close()
                            }, timeout)

                        }
                    })
                });

            })(jQuery);
        </script>
    </block>

    <block name="header"></block>

</head>
<body style="height: 100%;background-color: #F8F8F8">

<block name="content">

</block>

<block name="footer"></block>


</body>

</html>
