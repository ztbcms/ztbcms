<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-row>
            <el-col :sm="24" :md="17">
                <div class="grid-content ">
                    <el-form ref="formData" :model="formData" label-width="200px">

                        <el-form-item label="消息标题" >
                            <el-input v-model="formData.title" placeholder="请输入消息标题" clearable :style="{width: '50%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="消息内容">
                            <el-input v-model="formData.content" placeholder="请输入消息内容" clearable :style="{width: '50%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="消息源">
                            <el-input v-model="formData.target" placeholder="请输入消息源" clearable :style="{width: '50%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="消息源类型">
                            <el-input v-model="formData.target_type" placeholder="请输入消息源类型" clearable :style="{width: '50%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="发送者">
                            <el-input v-model="formData.sender" placeholder="请输入发送者" clearable :style="{width: '50%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="发送者类型">
                            <el-input v-model="formData.sender_type" placeholder="请输入发送者类型" clearable :style="{width: '50%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="接收者">
                            <el-input v-model="formData.receiver" placeholder="请输入接收者" clearable :style="{width: '50%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="接收者类型">
                            <el-input v-model="formData.receiver_type" placeholder="请输入接收者类型" clearable :style="{width: '50%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="消息类型">
                            <el-input v-model="formData.type" placeholder="请输入消息类型" clearable :style="{width: '50%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="实例化的类名">
                            <el-input v-model="formData.newClass" placeholder="请输入实例化的类名" clearable :style="{width: '50%'}"></el-input>
                            <p><small>示例： app\common\message\units\SimpleMessage</small></p>
                        </el-form-item>

                        <el-form-item>
                            <el-button type="primary" @click="onSubmit">提交</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>

        </el-row>


    </el-card>
</div>

<style>

</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                formData: {
                    title: '',
                    content: '',
                    target: '',
                    target_type: '',
                    sender: '',
                    sender_type: '',
                    receiver: '',
                    receiver_type: '',
                    type: '',
                    newClass: ''
                }
            },
            methods: {
                onSubmit: function () {
                    var that = this
                    var url = "{:api_url('/common/message.message/addMessage')}?_action=addMessage"
                    $.ajax({
                        url: url,
                        dataType: "json",
                        type: "post",
                        data: that.formData,
                        success: function (res) {
                            if (res.status) {
                                that.$message.success(res.msg);
                                if (window !== window.parent) {
                                    setTimeout(function () {
                                        window.parent.layer.closeAll();
                                    }, 1000);
                                }
                            } else {
                                that.$message.error(res.msg);
                            }
                        }
                    })

                }
            }
        })
    })
</script>