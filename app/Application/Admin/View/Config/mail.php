<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="24" :md="18" >

                <template>
                    <div>
                        <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="180px">
                            <el-form-item label="SMTP 函数发送" prop="mail_type">
                                <el-radio-group v-model="formData.mail_type" size="medium">
                                    <el-radio v-for="(item, index) in mail_typeOptions" :key="index" :label="item.value" :disabled="item.disabled">{{item.label}}</el-radio>
                                </el-radio-group>
                            </el-form-item>
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
                                              :disabled="item.disabled">{{item.label}}</el-radio>
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
                    </div>
                </template>
            </el-col>
        </el-card>
    </div>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                // 插入export default里面的内容
                components: {},
                props: [],
                data() {
                    return {
                        formData: {
                            mail_type: "{$Site.mail_type}",
                            mail_server: "{$Site.mail_server}",
                            mail_port: "{$Site.mail_port}",
                            mail_from: "{$Site.mail_from}",
                            mail_fname: "{$Site.mail_fname}",
                            mail_auth: "{$Site.mail_auth}",
                            mail_user: "{$Site.mail_user}",
                            mail_password: "{$Site.mail_password}",
                        },
                        rules: {
                            mail_type: [{
                                required: true,
                                message: 'SMTP 函数发送不能为空',
                                trigger: 'change'
                            }],
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
                            mail_user: [{
                                required: true,
                                message: '请输入验证用户名',
                                trigger: 'blur'
                            }],
                            mail_password: [{
                                required: true,
                                message: '请输入验证密码',
                                trigger: 'blur'
                            }],
                        },
                        mail_typeOptions: [{
                            "label": "SMTP 函数发送",
                            "value": '1'
                        }],
                        mail_authOptions: [{
                            "label": "开启",
                            "value": '1'
                        }, {
                            "label": "关闭",
                            "value": '0'
                        }],
                    }
                },
                computed: {},
                watch: {},
                created() {},
                mounted() {},
                methods: {
                    submitForm() {
                        this.$refs['elForm'].validate(valid => {
                            if (!valid) return
                            // TODO 提交表单

                            $.ajax({
                                url: "{:U('Config/mail')}",
                                method: 'post',
                                dataType: 'json',
                                data: this.formData,
                                success: function (res) {
                                    if (!res.status) {
                                        layer.msg(res.info)
                                    } else {
                                        layer.msg(res.info)
                                    }
                                }
                            });
                        })
                    }
                }
            });
        });
    </script>

    <style>

    </style>

</block>
