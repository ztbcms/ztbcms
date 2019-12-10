 
<script type="text/javascript">
//全局变量
var GV = {
    DIMAUB: "{$config_siteurl}",
	JS_ROOT: "{$config_siteurl}statics/js/"
};
</script>
<script src="{$config_siteurl}statics/js/wind.js"></script>
<script src="{$config_siteurl}statics/js/jquery.js"></script>

<!-- layer.js -->
<script src="{$config_siteurl}statics/admin/layer/layer.js"></script>
<!--  ztbcms工具类(必须在vue-commonn 前加载)  -->
<script src="{$config_siteurl}statics/js/ztbcms/ztbcms.js"></script>

<!--cryptojs 请务必再ztbcms.js后加在-->
<script src="{$config_siteurl}statics/admin/cryptojs/crypto-js.js"></script>
<script src="{$config_siteurl}statics/js/ztbcms/ztbcms_crypt.js"></script>
<!--cryptojs-->

<!-- vue.js -->
<script src="{$config_siteurl}statics/js/vue/vue.js"></script>
<script src="{$config_siteurl}statics/js/vue/vue-common.js"></script>
<script>
    (function (vue) {
        vue.mixin(window.__vueCommon);
    })(window.Vue);
</script>