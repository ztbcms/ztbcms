<?php if (!defined('CMS_VERSION')) exit(); ?>
<script type="text/javascript">
//全局变量
var GV = {
    DIMAUB: "{$config_siteurl}",
    JS_ROOT: "{$config_siteurl}statics/js/"
};
var domIsReady = false,
    domReadyList = [],
    domReadyObject = [],
    $call = function (callback, obj) {
        if (typeof obj !== 'object') {
            obj = document
        }
        if (domIsReady) {
            callback.call(obj)
        } else {
            domReadyList.push(callback);
            domReadyObject.push(obj)
        }
    };
var _config = {};
//会员中心地址
_config['domainSite'] = GV.DIMAUB;
//网站地址
_config['domainMainSite'] = '{$Config.siteurl}';
//当前模块静态文件目录
_config['domainStatic'] = '{$model_extresdir}';
</script>
<script type="text/javascript" src="{$config_siteurl}statics/js/jquery.js"></script>
