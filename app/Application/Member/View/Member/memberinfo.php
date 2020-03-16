<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-row>
                <el-col :span="8">
                    <div class="grid-content ">
                        <el-form ref="form" :model="form" label-width="100px">
                            <input type="hidden" v-model="form.userinfo.userid">
                            <el-form-item label="用户名">
                                <span  style="margin-left: 10px;">{{form.userinfo.username}}</span>
                            </el-form-item>
                            <el-form-item label="头像" >
                                <img class="form_img" style="margin-left: 10px;" :src="form.userinfo.userpic" v-if="form.userinfo.userpic != '' ">
                                <img class="form_img" src="{$config_siteurl}statics/images/member/nophoto.gif" alt="" v-else>
                            </el-form-item>
                            <el-form-item label="是否审核">
                                <span  style="margin-left: 10px;" v-if="form.userinfo.checked == 1">审核通过</span><span  style="margin-left: 10px;" v-else>待审核 <i class="el-icon-info" style="color: blue;"></i></span>
                            </el-form-item>
                            <el-form-item label="是否锁定" v-if="form.userinfo.islock == 1">
                                <span  style="margin-left: 10px;" >锁定 <i class="el-icon-lock" style="color: red;"></i></span>
                            </el-form-item>
                            <el-form-item label="昵称">
                                <span  style="margin-left: 10px;">{{form.userinfo.nickname}}</span>
                            </el-form-item>
                            <el-form-item label="邮箱">
                                <span  style="margin-left: 10px;">{{form.userinfo.email}}</span>
                            </el-form-item>
                            <el-form-item label="会员组">
                                <span  style="margin-left: 10px;">{{groupCache[form.userinfo.groupid]}}</span>
                            </el-form-item>
                            <el-form-item label="积分点数">
                                <span  style="margin-left: 10px;">{{form.userinfo.point}}</span>
                            </el-form-item>
                            <el-form-item label="钱金总额">
                                <span  style="margin-left: 10px;">{{form.userinfo.amount}}</span>
                            </el-form-item>
                            <el-form-item label="会员模型">
                                <span  style="margin-left: 10px;">{{groupsModel[form.userinfo.modelid]}}</span>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-col>
                <el-col :span="16"><div class="grid-content "></div></el-col>
            </el-row>
            <el-row>
                <h3>详细信息</h3>
                <div v-for="(item,index) in Model_field" >
                    <table width="100%" class="table_form" >
                        <th width="80">{{item.name}}:</th>
                        <td>{{output_data[item.field]}}</td>
                    </table>
                </div>
            </el-row>
            <el-row>
                <div style="margin-top: 30px;">
                    <template >
                        <el-button  size="" @click="getIdSh(id)">
                            审核
                        </el-button>
                        <el-button  size="" @click="getIdNoSh(id)">
                            取消审核
                        </el-button>
                        <el-button  size="" @click="getIdSd(id)">
                            锁定
                        </el-button>
                        <el-button  size="" @click="getIdJs(id)">
                            解锁
                        </el-button>
                        <el-button  size="" @click="getIdDel(id)">
                            删除
                        </el-button>
                    </template>
                </div>
            </el-row>

        </el-card>
    </div>

    <style>
        .form_img{
            width: 90px;
            height: 90px;
        }
        .el-form-item{
            margin-bottom: 0;
        }
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    id:"{:I('get.userid')}",
                    form: {
                        id:"{:I('get.userid')}",
                        userinfo:[]
                    },
                    groupsModel:{$groupsModel},
                    groupCache:{$groupCache},
                    Model_field:{$Model_field},
                    output_data:{$output_data},

                },
                watch: {},
                filters: {},
                methods: {
                    //获取用户信息
                    getUserinfo(userid){
                        var that = this;
                        console.log(this.Model_field)
                        $.ajax({
                            url:"{:U('memberinfoApi')}",
                            dataType:"json",
                            type:"get",
                            data: {
                                "userid": userid,
                            },
                            success(res){
                                if(res.state){
                                    that.form.userinfo = res.data.userinfo;
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })
                    },
                    onSubmit: function(){
                        console.log(this.form)
                        this.$message.success('提交成功');
                    },
                    onCancel: function(){
                        this.$message.error('已取消');
                    },
                    //审核
                    getIdSh(userid){
                        var that = this;
                        $.ajax({
                            url:"{:U('userverify')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": userid,
                            },
                            success(res){
                                if(res.state){
                                    that.getUserinfo(userid)
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })
                    },
                    //取消审核
                    getIdNoSh(userid){
                        var that = this;
                        $.ajax({
                            url:"{:U('userunverify')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": userid,
                            },
                            success(res){
                                if(res.state){
                                    that.getUserinfo(userid)
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })
                    },
                    //锁定
                    getIdSd(userid){
                        var that = this;
                        $.ajax({
                            url:"{:U('lock')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": userid,
                            },
                            success(res){
                                if(res.state){
                                    that.getUserinfo(userid)
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })
                    },
                    //解锁
                    getIdJs(userid){
                        var that = this;
                       
                        $.ajax({
                            url:"{:U('unlock')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": userid,
                            },
                            success(res){
                                if(res.state){
                                    that.getUserinfo(userid)
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })
                    },
                    //删除
                    getIdDel(userid){
                        var that = this;
                        $.ajax({
                            url:"{:U('delete')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": userid,
                            },
                            success(res){
                                if(res.state){
                                    that.getUserinfo(userid)
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })
                    },

                },
                mounted: function () {
                    if(this.id){
                        this.getUserinfo(this.id)
                    }
                },

            })
        })
    </script>
</block>
