<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <h3>用户信息</h3>
        <el-row>
            <el-col :sm="16" :md="8">
                <div class="grid-content ">
                    <el-form ref="form" :model="form" label-width="120px">
                        <el-form-item label="用户名">
                            {{ form.username }}
                        </el-form-item>
                        <el-form-item label="旧密码">
                            <el-input v-model="form.password" type="password"></el-input>
                        </el-form-item>
                        <el-form-item label="新密码">
                            <el-input v-model="form.new_password" type="password"></el-input>
                        </el-form-item>
                        <el-form-item label="重复新密码">
                            <el-input v-model="form.new_pwdconfirm" type="password"></el-input>
                        </el-form-item>

                        <el-form-item>
                            <el-button type="primary" @click="onSubmit">确认</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>
            <el-col :sm="8" :md="16">
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
                    username: '{$user.username}',
                    password: '',
                    new_password: '',
                    new_pwdconfirm: '',
                }
            },
            watch: {},
            filters: {},
            methods: {
                onSubmit: function () {
                    $.ajax({
                        url: "{:api_url('/admin/AdminManager/changePassword')}",
                        method: 'post',
                        dataType: 'json',
                        data: this.form,
                        success: function (res) {
                            layer.msg(res.msg)
                            if (res.status) {
                                if (res.data.rediret_url) {
                                    //跳转到登录页
                                    setTimeout(function () {
                                        window.location.href = res.data.rediret_url
                                    }, 1000)
                                }
                            }
                        }
                    })
                },
            },
            mounted: function () {

            },

        })
    })
</script>
