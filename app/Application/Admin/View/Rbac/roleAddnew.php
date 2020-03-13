<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>添加角色</h3>
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
                        id:"{:I('get.id')}",
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
                        var that = this
                        $.ajax({
                            url:"{:U('roleadd')}",
                            dataType:"json",
                            type:"post",
                            data:  that.form,
                            success(res){
                                if(res.status){
                                    layer.alert(res.info, { icon: 1, closeBtn: 0 }, function (index) {
                                        //关闭弹窗
                                        layer.close(index);
                                        parent.layer.closeAll()
                                    });
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })

                    },
                    onCancel: function(){
                        this.$message.error('已取消');
                    },
                    getRoleInfo(id){
                        var that = this
                        $.ajax({
                            url:"{:U('getRoleInfo')}",
                            dataType:"json",
                            type:"get",
                            data: {id:id},
                            success(res){
                                if(res.status){
                                    that.form = res.data;
                                }
                            }
                        })
                    },
                    //获取所有角色
                    getroleList:function () {
                        var that = this
                        $.ajax({
                            url:"<?php echo U('Admin/Rbac/getrolemanage');?>",
                            type:"get",
                            dataType:"json",
                            success(res){
                                if(res.status){
                                    that.roleList = res.data
                                }
                            }
                        })
                    },
                },
                mounted: function () {
                    this.getroleList()
                    if(this.form.id){
                        this.getRoleInfo(this.form.id);
                    }
                },

            })
        })
    </script>
</block>
