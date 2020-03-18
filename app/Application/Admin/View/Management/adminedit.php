<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/3/6
 * Time: 12:42
 */
?>
<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-row v-loading="loading">
                <el-col :span="24">
                    <div class="grid-content ">
                        <el-form ref="form" :model="form" label-width="80px">
                            <el-form-item label="用户名">
                                <el-input v-model="form.username"></el-input>
                            </el-form-item>
                            <el-form-item label="昵称">
                                <el-input v-model="form.nickname"></el-input>
                            </el-form-item>
                            <el-form-item label="密码">
                                <el-input type="password" v-model="form.password"></el-input>
                            </el-form-item>
                            <el-form-item label="确认密码">
                                <el-input type="password" v-model="form.pwdconfirm"></el-input>
                            </el-form-item>
                            <el-form-item label="E-mail">
                                <el-input v-model="form.email"></el-input>
                            </el-form-item>

                            <el-form-item label="所属角色">
                                <template>
                                    <el-select v-model="form.role_id" clearable placeholder="请选择">
                                        <el-option
                                            v-for="item in role_list"
                                            :key="item.id"
                                            :label="item.name"
                                            :value="item.id">
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
                        id:'{$_GET["id"]}',
                        username: '',
                        password: '',
                        pwdconfirm: '',
                        email: '',
                        nickname: '',
                        remark: '',
                        role_id: '',
                        status: '1',
                    },
                    role_list:[],
                    loading:false
                },
                watch: {},
                filters: {},
                methods: {
                    onSubmit: function(){
                        var that = this
                        $.ajax({
                            url:"{:U('edit')}",
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
                                console.log(res)
                            }
                        })
                    },
                    onCancel: function(){
                        this.$message.error('已取消');
                    },
                    //获取所有角色
                    getroleList:function () {
                        var that = this
                        $.ajax({
                            url:"{:U('Admin/Rbac/getrolemanage')}",
                            type:"get",
                            dataType:"json",
                            success(res){
                                if(res.status){
                                    that.role_list = res.data.items
                                }

                            }
                        })
                    },

                    //获取信息
                    getManagerByid:function (id) {
                        var that = this
                        $.ajax({
                            url:"{:U('Admin/Management/getManagerByid')}",
                            type:"post",
                            dataType:"json",
                            data:{
                                id:id
                            },
                            success(res){
                                if(res.status){
                                    that.form = res.data
                                    that.loading = false
                                }
                            }
                        })
                    }
                },
                mounted: function () {
                    this.getroleList()
                    console.log(this.form)
                    if(this.form.id){
                        this.loading = true
                        this.getManagerByid(this.form.id)
                    }
                },

            })
        })
    </script>
</block>
