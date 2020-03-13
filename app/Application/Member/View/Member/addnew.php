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
            <h3>添加会员</h3>
            <el-row>
                <el-col :span="9">
                    <div class="grid-content ">
                        <el-form ref="form" :model="form" label-width="80px">
                            <el-form-item label="用户名">
                                <el-input v-model="form.username"></el-input>
                            </el-form-item>
                            <el-form-item label="是否审核">
                                <el-radio-group v-model="form.checked">
                                    <el-radio label="1">审核通过</el-radio>
                                    <el-radio label="0">待审核</el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="密码">
                                <el-input type="password" v-model="form.password"></el-input>
                            </el-form-item>
                            <el-form-item label="确认密码">
                                <el-input type="password" v-model="form.pwdconfirm"></el-input>
                            </el-form-item>
                            <el-form-item label="昵称">
                                <el-input v-model="form.nickname"></el-input>
                            </el-form-item>
                            <el-form-item label="邮箱">
                                <el-input v-model="form.email"></el-input>
                            </el-form-item>

                            <el-form-item label="会员组">
                                <template>
                                    <el-select v-model="form.groupid" clearable placeholder="请选择">
                                        <el-option
                                            v-for="item in groupCache"
                                            :key="item.id"
                                            :label="item.label"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                </template>
                            </el-form-item>
                            <el-form-item label="积分点数">
                                <el-input v-model="form.point" style="width:70px"></el-input>
                                &nbsp;请输入积分点数，积分点数将影响会员用户组
                            </el-form-item>
                            <el-form-item label="会员模型">
                                <template v-if="groupsModel != '' ">
                                    <el-select v-model="form.modelid" clearable placeholder="请选择">
                                        <el-option
                                            v-for="item in groupsModel"
                                            :key="item.id"
                                            :label="item.label"
                                            :value="item.id">
                                        </el-option>
                                    </el-select>
                                </template>
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
                        checked: '1',
                        email: '',
                        nickname: '',
                        groupid: '',
                        modelid: '',
                        point: '0',
                    },
                    role_list:[],
                    groupsModel:[
                        {$groupsModel}
                    ],
                    groupCache:{$groupCache}

                },
                watch: {},
                filters: {},
                methods: {
                    onSubmit: function(){
                        var that = this
                        $.ajax({
                            url:"{:U('add')}",
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

                    //获取信息
                    // getManagerByid:function (id) {
                    //     var that = this
                    //     $.ajax({
                    //         url:"{:U('Admin/Management/getManagerByid')}",
                    //         type:"post",
                    //         dataType:"json",
                    //         data:{
                    //             id:id
                    //         },
                    //         success(res){
                    //             that.form = res.data
                    //         }
                    //     })
                    // }
                },
                mounted: function () {
                    // this.getroleList()
                    // if(this.form.id){
                    //     this.getManagerByid(this.form.id)
                    // }
                },

            })
        })
    </script>
</block>
