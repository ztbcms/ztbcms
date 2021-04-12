<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>{$_Config['sitename']?$_Config['sitename'].'-':''}系统后台</title>
    <!-- ztbcms 默认 iconfont -->
    <link rel="stylesheet" href="/statics/admin/ztbcms/iconfont/iconfont.css">
    <!-- jQuery 2.x -->
    <script src="/statics/admin/jquery/jquery-2.2.3.min.js"></script>
    <!-- layer.js -->
    <script src="/statics/admin/layer/layer.js"></script>
    <!--  ztbcms工具类(必须在vue-common 前加载)  -->
    <script src="/statics/admin/ztbcms/ztbcms.js"></script>
    <!-- vue.js -->
    <script src="/statics/admin/vue/vue.js"></script>
    <script src="/statics/admin/vue/vue-common.js"></script>
    <script>
        (function (vue) {
            //引入vue mixin
            vue.mixin(window.__vueCommon);
        })(window.Vue);
    </script>

    <!-- 时间格式化工具  -->
    <script src="/statics/admin/momentjs/2.24.0.js"></script>

    <!--  Element UI START  -->
    <!-- 引入样式 -->
    <link rel="stylesheet" href="/statics/admin/theme/elementui/elementui_2.13.2/index.css">
    <!-- 引入组件库 -->
    <script src="/statics/admin/theme/elementui/elementui_2.13.2/index.js"></script>
    <!--  Element UI END  -->
    <style>
        /* vue相关  */
        [v-cloak] {
            display: none;
        }
        * {
            font-family: "Helvetica Neue", Helvetica, "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", "微软雅黑", Arial, sans-serif;
        }
    </style>
    <script>
        /**
         * js资源加载完后进行全局初始化
         */
        (function () {
            $(document).ready(function () {
                //是否启用 loading
                window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = true;
                //注册 ajax加载时 显示加载框
                $(document).ajaxStart(function () {
                    if (ELEMENT && window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE) {
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
                        setTimeout(function () {
                            window.__GLOBAL_ELEMENT_LOADING_INSTANCE.close()
                        }, 0)

                    }
                });
                $(document).ajaxError(function () {
                    if (window.__GLOBAL_ELEMENT_LOADING_INSTANCE) {
                        setTimeout(function () {
                            window.__GLOBAL_ELEMENT_LOADING_INSTANCE.close()
                        }, 0)

                    }
                });
                if (!!window.ActiveXObject || "ActiveXObject" in window) {
                    alert('建议使用IE11及以上的浏览器');
                }
            });
        })(jQuery);
    </script>
</head>
<body style="height: 100%;background-color: #F8F8F8">
<!--内容-->
{__CONTENT__}
<!--内容 END-->

</body>

</html>
