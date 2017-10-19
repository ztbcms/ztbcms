 
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<title>系统后台 - {$Config.sitename} - by ZTBCMS</title>
<Admintemplate file="Admin/Common/Cssjs"/>

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
</head>
