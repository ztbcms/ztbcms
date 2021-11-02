<div>
    <div id="app" style="padding: 8px;" v-cloak>
        <el-row>
            <el-col>
                <el-form ref="form" :model="form" ref="form" label-width="150px">
                    <el-form-item label="操作日志" prop="remarks">
                        <el-switch
                                v-model="form.switch"
                                active-text="开"
                                active-value="1"
                                inactive-text="关"
                                inactive-value="0">
                        </el-switch>
                    </el-form-item>

                    <el-form-item>
                        <el-button type="primary" @click="onSubmit">提交</el-button>
                    </el-form-item>
                </el-form>
            </el-col>

        </el-row>
    </div>
</div>
<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                form: {
                    switch: "{$switch}",
                }
            },
            filters: {},
            methods: {
                onSubmit: function () {
                    var that = this;
                    that.httpPost("{:api_url('/admin/Logs/adminOperationLogList')}", {
                        _action: 'updateAdminOperationConfig',
                        admin_operation_switch: that.form.switch
                    }, function(res){
                        if (res.status) {
                            that.$message.success(res.msg)
                        } else {
                            that.$message.error(res.msg)
                        }
                    })
                }
            },
            mounted: function () {}
        })
    })
</script>