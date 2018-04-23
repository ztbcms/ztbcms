 
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
<!-- vue.js -->
<script src="{$config_siteurl}statics/js/vue/vue.js"></script>
<script src="{$config_siteurl}statics/js/vue/vue-common.js"></script>
<script>
    (function (vue) {
        vue.mixin(window.__vueCommon);
    })(window.Vue);
</script>