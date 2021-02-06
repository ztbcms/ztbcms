<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <el-form ref="elForm" size="medium" label-width="160px">
                <el-form-item label="模块名称:" >
                    <span>{{moduleInfo['modulename']}}</span>
                </el-form-item>
                <el-form-item label="当前版本:" >
                    <span>{{moduleInfo['version']}}</span>
                </el-form-item>
                <el-form-item label="最低后台版本:" >
                    <span>{{moduleInfo['adaptation']}}</span>
                </el-form-item>
                <el-form-item label="依赖模块:" >
                    <template v-for="item in moduleInfo['depend_list']">
                        <p style="margin: 0;">{{item['module']}} @ {{item['version']}}</p>
                    </template>
                </el-form-item>
                <el-form-item label="简介:">
                    <span>{{moduleInfo['introduce']}}</span>
                </el-form-item>
                <el-form-item label="作者:" >
                    <span>{{moduleInfo['author']}}</span>
                </el-form-item>
                <el-form-item label="联系方式:" >
                    <span>{{moduleInfo['authoremail']}}</span>
                </el-form-item>

                <el-form-item size="large">
                    <template v-if="moduleInfo.install_time === ''">
                        <el-button type="primary" @click="submitForm('install')" size="mini" :disabled="disabled_install">确认安装</el-button>
                    </template>
                    <template v-else>
                        <el-button type="danger" @click="submitForm('uninstall')" size="mini" :disabled="disabled_uninstall">确认卸载</el-button>
                    </template>
                </el-form-item>
            </el-form>
        </el-col>
    </el-card>
</div>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            components: {},
            props: [],
            data:function() {
                return {
                    moduleInfo: {
                        install_time: ''
                    },
                    disabled_install: false,
                    disabled_uninstall: false,
                }
            },
            computed: {},
            watch: {},
            created:function() {
            },
            mounted:function() {
                var module = this.getUrlQuery('module');
                this.getDetail(module)
            },
            methods: {
                submitForm:function(type) {
                    var that = this
                    var url = ''
                    if (type === 'install') {
                        url = "{:api_url('admin/module/doInstallModule')}";
                    } else {
                        url = "{:api_url('admin/module/doUninstallModule')}";
                    }
                    var data = {
                        module: this.getUrlQuery('module')
                    }
                    this.disabled_install = true
                    this.disabled_uninstall = true
                    this.httpPost(url, data, function (res) {
                        that.disabled_install = false
                        that.disabled_uninstall = false
                        layer.msg(res.msg)
                        if (res.status) {
                            if (window !== window.parent) {
                                setTimeout(function () {
                                    window.parent.layer.closeAll()
                                }, 700)
                            }
                        }
                    })
                },
                getDetail: function(module){
                    var that = this
                    var url = "{:api_url('admin/module/install')}"
                    var data = {
                        '_action': 'getDetail',
                        'module': module
                    }
                    this.httpGet(url, data, function (res) {
                        if (res.status) {
                            that.moduleInfo = res.data
                        }
                    })
                }
            }
        });
    });
</script>

<style>

</style>
