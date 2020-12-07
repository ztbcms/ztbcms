<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <h3>用户信息</h3>
        <el-row>
            <el-col :sm="16" :md="8" >
                <div class="grid-content ">
                    <el-form ref="form" :model="form" label-width="120px">
                        <el-form-item label="用户ID">
                            {{ form.id }}
                        </el-form-item>
                        <el-form-item label="用户名">
                            {{ form.username }}
                        </el-form-item>
                        <el-form-item label="呢称">
                            <el-input v-model="form.nickname"></el-input>
                        </el-form-item>
                        <el-form-item label="邮箱">
                            <el-input v-model="form.email"></el-input>
                        </el-form-item>
                        <el-form-item label="备注">
                            <el-input v-model="form.remark"></el-input>
                        </el-form-item>

                        <el-form-item>
                            <el-button type="primary" @click="onSubmit">确认</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>
            <el-col :sm="8" :md="16"  >
                <div class="grid-content"></div>
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
                form: {
                    id: '{$user.id}',
                    username: '{$user.username}',
                    nickname: '{$user.nickname}',
                    email: '{$user.email}',
                    remark: '{$user.remark}',
                }
            },
            watch: {},
            filters: {},
            methods: {
                onSubmit: function () {
                    $.ajax({
                        url: "{:api_url('/admin/AdminManager/myBasicsInfo')}",
                        method: 'post',
                        dataType: 'json',
                        data: this.form,
                        success: function (res) {
                            if (!res.status) {
                                layer.msg(res.msg)
                            } else {
                                layer.msg(res.msg)
                            }
                        }
                    })
                },
                onCancel: function () {
                    this.$message.error('已取消');
                },
            },
            mounted: function () {

            },

        })
    })
</script>