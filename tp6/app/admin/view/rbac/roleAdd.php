<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <h3>新增角色</h3>
        <el-row>
            <el-col :span="8">
                <div class="grid-content ">
                    <el-form ref="form" :model="form" label-width="80px">
                        <el-form-item label="父角色">
                            <el-select v-model="form.parentid" placeholder="请选择">
                                <el-option
                                        v-for="item in roleList"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="角色名称">
                            <el-input v-model="form.name"></el-input>
                        </el-form-item>
                        <el-form-item label="角色描述">
                            <el-input type="textarea" v-model="form.remark" rows="3"></el-input>
                        </el-form-item>
                        <el-form-item label="是否启用">
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
                    id:"",
                    name: '',
                    remark: '',
                    status: '1'
                },
                roleList:[]
            },
            watch: {},
            filters: {},
            methods: {
                onSubmit: function(){
                    var that = this;
                    $.ajax({
                        url:"{:api_url('/admin/Rbac/roleAddEdit')}",
                        dataType:"json",
                        type:"post",
                        data:  that.form,
                        success:function(res){
                            if(res.status){
                                that.$message.success(res.msg);
                                if (window !== window.parent) {
                                    setTimeout(function () {
                                        window.parent.layer.closeAll();
                                    }, 1000);
                                }
                            }else{
                                that.$message.error(res.msg);
                            }
                        }
                    })

                },
                //获取所有角色
                getroleList:function () {
                    var that = this;
                    $.ajax({
                        url:"{:api_url('/admin/Rbac/getrolemanage')}",
                        type:"get",
                        dataType:"json",
                        success:function(res){
                            if(res.status){
                                that.roleList = res.data
                            }
                        }
                    })
                },
            },
            mounted: function () {
                this.getroleList();
            }
        })
    })
</script>