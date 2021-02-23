<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="12">
            <el-form ref="elForm" :model="formData"  label-width="100px">
                <el-form-item label="接收邮箱" required>
                    <el-input v-model="formData.to_email" placeholder="请输入接收邮箱" clearable :style="{width: '100%'}">
                    </el-input>
                </el-form-item>
                <el-form-item label="标题" required>
                    <el-input v-model="formData.subject" placeholder="请输入标题" clearable :style="{width: '100%'}">
                    </el-input>
                </el-form-item>

                <el-form-item label="内容" required>
                    <el-input v-model="formData.content" type="textarea"  :rows="4" placeholder="请输入内容" clearable :style="{width: '100%'}">
                    </el-input>
                </el-form-item>

                <el-form-item >
                    <el-button type="primary" @click="submitForm">发送</el-button>
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
                    request_url: "{:api_url('/common/email.Email/sendEmail')}",
                    formData: {
                        to_email: "",
                        subject: "",
                        content: ""
                    }
                }
            },
            computed: {},
            watch: {},
            created: function() {},
            mounted: function() {},
            methods: {
                submitForm: function() {
                    var that = this
                    var form = that.formData
                    form['_action'] = 'doSendEmail'
                    that.httpPost(that.request_url, form, function(res){
                        layer.msg(res.msg)
                    })
                }
            }
        });
    });
</script>

<style>

</style>
