<div id="app" style="padding: 8px;" v-cloak>
    <el-card v-loading="loading">
        <el-row>
            <el-col :xs="24" :md="8">
                <div class="grid-content ">
                    <el-form ref="form" :model="form" label-width="80px">
                        <el-form-item label="用户名" required>
                            <el-input v-model="form.username"></el-input>
                        </el-form-item>
                        <el-form-item label="昵称" required>
                            <el-input v-model="form.nickname"></el-input>
                        </el-form-item>
                        <el-form-item label="密码">
                            <el-input type="password" v-model="form.password"></el-input>
                            <span v-show="isEditing">* 不填写则不修改</span>
                        </el-form-item>
                        <el-form-item label="重复密码">
                            <el-input type="password" v-model="form.pwdconfirm"></el-input>
                        </el-form-item>
                        <el-form-item label="E-mail" required>
                            <el-input v-model="form.email"></el-input>
                        </el-form-item>

                        <el-form-item label="所属角色" required>
                            <template>
                                <el-select v-model="form.role_id" clearable placeholder="请选择">
                                    <el-option
                                        v-for="item in role_list"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                            <template v-for="i in item.level * 2"><span>&nbsp;</span></template>
                                            <template v-if="item.level > 0"><span> ∟</span></template>
                                            <span>{{ item.name }}</span>
                                    </el-option>

                                </el-select>
                            </template>
                        </el-form-item>
                        <el-form-item label="备注">
                            <el-input type="textarea" v-model="form.remark" rows="5"></el-input>
                        </el-form-item>
                        <el-form-item label="状态">
                            <el-radio-group v-model="form.status">
                                <el-radio label="1">开启</el-radio>
                                <el-radio label="0">关闭</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="onSubmit">保存</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>
            <el-col :span="16"><div class="grid-content "></div></el-col>
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
                    id: "{$id|default=''}",
                    username: '',
                    password: '',
                    pwdconfirm: '',
                    email: '',
                    nickname: '',
                    remark: '',
                    role_id: '',
                    status: '1',
                },
                loading: false,
                role_list:[]
            },
            watch: {},
            filters: {},
            computed: {
                // 是否处于编辑中
                isEditing: function () {
                    return !!this.form.id
                }
            },
            methods: {
                onSubmit: function() {
                    var that = this;
                    var url = "{:api_url('/admin/AdminManager/managerAdd')}"
                    if(this.form.id){
                        url = "{:api_url('/admin/AdminManager/managerEdit')}"
                    }
                    this.httpPost(url, that.form, function (res){
                        if(res.status){
                            that.$message.success(res.msg);
                            if (window !== window.parent) {
                                setTimeout(function () {
                                    window.parent.layer.closeAll()
                                }, 1000);
                            }
                        }else{
                            that.$message.error(res.msg);
                        }
                    })
                },
                //获取所有角色
                getRoleList:function () {
                    var that = this;
                    this.httpGet("{:api_url('/admin/Role/getRoleList')}", {status: 1}, function (res){
                        that.role_list = res.data
                    })
                },
                //获取管理员信息
                getManagerById:function (id) {
                    var that = this
                    this.loading = true
                    this.httpGet("{:api_url('/admin/AdminManager/managerEdit')}", {'_action': 'getDetail', id: id}, function(res){
                        if(res.status){
                            that.form = res.data
                        }
                        that.loading = false
                    })
                }
            },
            mounted: function () {
                this.getRoleList()
                if(this.form.id){
                    this.getManagerById(this.form.id)
                }
            }
        })
    })
</script>