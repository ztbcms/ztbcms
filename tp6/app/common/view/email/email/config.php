<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <el-alert type="success" :closable="false">
                <template slot="title">
                    <span>推荐使用阿里云邮件推送服务，每日免费额度200封，<a style="color: #67C23A;" target="_blank" href="https://www.aliyun.com/product/directmail?source=5176.11533457&userCode=cb42l4yn" >点击了解</a></span>
                </template>
            </el-alert>
            <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="180px" style="margin-top: 6px">
                        <el-form-item label="邮件服务器" prop="mail_server">
                            <el-input v-model="formData.mail_server" placeholder="请输入邮件服务器" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="邮件发送端口" prop="mail_port">
                            <el-input v-model="formData.mail_port" placeholder="请输入邮件发送端口" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="发件人地址" prop="mail_from">
                            <el-input v-model="formData.mail_from" placeholder="请输入发件人地址" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="发件人名称" prop="mail_fname">
                            <el-input v-model="formData.mail_fname" placeholder="请输入发件人名称" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="密码验证" prop="mail_auth">
                            <el-radio-group v-model="formData.mail_auth" size="medium">
                                <el-radio v-for="(item, index) in mail_authOptions" :key="index" :label="item.value"
                                          :disabled="item.disabled">{{item.label}}
                                </el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="验证用户名" prop="mail_user">
                            <el-input v-model="formData.mail_user" placeholder="请输入验证用户名" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="验证密码" prop="mail_password">
                            <el-input v-model="formData.mail_password" placeholder="请输入验证密码" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item size="large">
                            <el-button type="primary" @click="submitForm">保存</el-button>
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
            data: function() {
                return {
                    request_url: "{:api_url('/common/email.Email/config')}",
                    formData: {
                        mail_server: "",
                        mail_port: "",
                        mail_from: "",
                        mail_fname: "",
                        mail_auth: "",
                        mail_user: "",
                        mail_password: "",
                    },
                    rules: {
                        mail_server: [{
                            required: true,
                            message: '请输入邮件服务器',
                            trigger: 'blur'
                        }],
                        mail_port: [{
                            required: true,
                            message: '请输入邮件发送端口',
                            trigger: 'blur'
                        }],
                        mail_from: [{
                            required: true,
                            message: '请输入发件人地址',
                            trigger: 'blur'
                        }],
                        mail_fname: [{
                            required: true,
                            message: '请输入发件人名称',
                            trigger: 'blur'
                        }],
                        mail_auth: [{
                            required: true,
                            message: '密码验证不能为空',
                            trigger: 'change'
                        }],
                    },
                    mail_authOptions: [{
                        "label": "开启",
                        "value": '1'
                    }, {
                        "label": "关闭",
                        "value": '0'
                    }]
                }
            },
            computed: {},
            watch: {},
            created: function() {
            },
            mounted: function() {
                this.getDetail()
            },
            methods: {
                submitForm: function() {
                    var that = this
                    this.$refs['elForm'].validate(function(valid){
                        if (!valid) return
                        that.httpPost(that.request_url, that.formData, function(res){
                            layer.msg(res.msg)
                        })
                    })
                },
                // 获取详情
                getDetail: function() {
                    var that = this
                    var formData = {}
                    formData['_action'] = 'getDetail'
                    that.httpGet(that.request_url, formData, function(res){
                        that.formData = res.data
                    })
                }
            }
        });
    });
</script>

<style>

</style>
