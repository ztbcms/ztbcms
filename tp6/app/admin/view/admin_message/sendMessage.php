<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18" >

            <template>
                <div>
                    <el-form ref="elForm" :model="formData"  size="medium" label-width="160px">
                        <el-form-item label="消息标题" prop="title">
                            <el-input v-model="formData.title" placeholder="请输入消息标题" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="消息内容" prop="content">
                            <el-input v-model="formData.content"
                                      type="textarea"
                                      :autosize="{minRows: 4, maxRows: 4}"
                                      placeholder="请输入消息内容" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="消息源" prop="target">
                            <el-input v-model="formData.target" placeholder="请输入消息源" clearable :style="{width: '100%'}">
                            </el-input>
                            <small style="color: #858689;">通常使用id或order_sn</small>
                        </el-form-item>

                        <el-form-item label="消息源类型" prop="target_type">
                            <el-input v-model="formData.target_type" placeholder="消息源类型" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>

                        <el-form-item label="发送者" prop="sender">
                            <el-input v-model="formData.sender" placeholder="请输入发送者" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>

                        <el-form-item label="发送者类型" prop="sender_type">
                            <el-input v-model="formData.sender_type" placeholder="发送者类型" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>

                        <el-form-item label="接收者" prop="receiver">
                            <el-input v-model="formData.receiver" placeholder="请输入发送者" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>

                        <el-form-item label="接收者类型" prop="receiver_type">
                            <el-input v-model="formData.receiver_type" placeholder="接收者类型" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>

                        <el-form-item label="类型" prop="type">
                            <el-input v-model="formData.type" placeholder="请输入类型" clearable :style="{width: '100%'}">
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
            components: {},
            props: [],
            data: function() {
                return {
                    formData: {
                        title: "系统通知",
                        content: "系统提醒，您定时清理缓存",
                        target: "",
                        target_type: "system",
                        sender: "1",
                        sender_type: "admin",
                        receiver: "1",
                        receiver_type: "admin",
                        type: "notice",
                        _action : "createMessage"
                    }
                }
            },
            computed: {},
            watch: {},
            created: function() {},
            mounted: function() {},
            methods: {
                // 表单提交
                submitForm: function() {
                    var that = this;
                    that.httpPost("{:api_url('/admin/AdminMessage/sendMessage')}", that.formData, function(res){
                        layer.msg(res.msg)
                    })
                }
            }
        });
    });
</script>

<style>

</style>
