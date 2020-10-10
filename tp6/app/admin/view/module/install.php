<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="160px">
                <el-form-item label="模块名称:" prop="sitename">
                    <span>{$config['modulename']}</>
                </el-form-item>
                <el-form-item label="版本:" prop="siteurl">
                    <span>{$config['version']}</>
                </el-form-item>
                <el-form-item label="最低后台版本:" prop="sitefileurl">
                    <span>{$config['adaptation']}</>
                </el-form-item>
                <el-form-item label="简介:" prop="siteemail">
                    <span>{$config['introduce']}</>
                </el-form-item>
                <el-form-item label="作者:" prop="sitekeywords">
                    <span>{$config['author']}</>
                </el-form-item>
                <el-form-item label="联系方式:" prop="siteinfo">
                    <span>{$config['authoremail']}</>
                </el-form-item>

                <el-form-item size="large">
                    <template v-if="install_time === ''">
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
            data() {
                return {
                    formData: {
                        'module': "{$config['module']}"
                    },
                    install_time: "{$config['install_time']}",
                    disabled_install: false,
                    disabled_uninstall: false,
                }
            },
            computed: {},
            watch: {},
            created() {
            },
            mounted() {

            },
            methods: {
                submitForm(type) {
                    var that = this
                    var url = ''
                    if (type === 'install') {
                        url = "{:api_url('admin/module/doInstallModule')}";
                    } else {
                        url = "{:api_url('admin/module/doUninstallModule')}";
                    }
                    this.disabled_install = true
                    this.disabled_uninstall = true
                    this.httpPost(url, this.formData, function (res) {
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
                }
            }
        });
    });
</script>

<style>

</style>
