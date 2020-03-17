<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h4>修改会员组</h4>
            <el-row>
                <el-col :span="24">
                    <div class="grid-content ">
                        <el-form ref="form" :model="form" label-width="140px">
                            <p class="center_p">基本信息</p>
                            <el-form-item label="会员组名称">
                                <el-input style="width:200px" v-model="form.name"></el-input>
                            </el-form-item>

                            <el-form-item label="积分小于">
                                <el-input style="width:100px" v-model="form.point"></el-input>
                            </el-form-item>
                            <el-form-item label="星星数">
                                <el-input style="width:100px"v-model="form.starnum"></el-input>
                            </el-form-item>

                            <p class="center_p">详细信息</p>
                            <el-form-item label="用户权限" class="userAuth">
                                <el-form-item label="">
                                    <el-checkbox v-model="form.allowpost" :true-label="'1'" :false-label="'0'">允许投稿</el-checkbox>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-checkbox v-model="form.allowpostverify" :true-label="'1'" :false-label="'0'">投稿不需审核</el-checkbox>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-checkbox v-model="form.allowupgrade" :true-label="'1'" :false-label="'0'">允许自助升级</el-checkbox>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-checkbox v-model="form.allowsendmessage" :true-label="'1'" :false-label="'0'">允许发短消息</el-checkbox>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-checkbox v-model="form.allowattachment" :true-label="'1'" :false-label="'0'">允许上传附件</el-checkbox>
                                </el-form-item>
                                <el-form-item label="">
                                    <el-checkbox v-model="form.allowsearch" :true-label="'1'" :false-label="0" checked>搜索权限</el-checkbox>
                                </el-form-item>
                            </el-form-item>
                            <el-form-item label="最大短消息数">
                                <el-input style="width:100px"v-model="form.allowmessage"></el-input>
                            </el-form-item>
                            <el-form-item label="日最大投稿数">
                                <el-input style="width:100px"v-model="form.allowpostnum"></el-input>&nbsp;0为不限制
                            </el-form-item>
                            <el-form-item label="用户名颜色">
                                <span style="float: left">
                                  <el-color-picker v-model="form.usernamecolor"></el-color-picker>
                                </span>
                                <el-input style="width:100px;margin-left: 10px;"v-model="form.usernamecolor"></el-input>
                            </el-form-item>
                            <el-form-item label="用户名图标" >
                                <el-input style="width:400px;float:left;" v-model="form.icon"></el-input>
                                <div style="float:left;">
                                    <el-upload
                                            ref="uploadRef"
                                            :limit="uploadConfig.max_upload"
                                            :action="uploadConfig.uploadUrl"
                                            :accept="uploadConfig.accept"
                                            :on-success="handleUploadSuccess"
                                            :show-file-list="false"
                                            multiple
                                            class="thumb-uploader">

                                        <el-button type="primary" >上传文件</el-button>
                                    </el-upload>
                                </div>
                            </el-form-item>
                            <el-form-item label="简洁描述">
                                <el-input style="width:400px" v-model="form.description"></el-input>
                            </el-form-item>
                            <el-form-item label="可以上传照片总数">
                                <el-input style="width:100px"v-model="form.expand.upphotomax"></el-input>&nbsp;0为不允许上传
                            </el-form-item>
                            <el-form-item label="是否可以发送短信息">
                                <el-checkbox v-model="form.expand.ismsg" :true-label="'1'" :false-label="'0'"></el-checkbox>
                            </el-form-item>
                            <el-form-item label="是否可以留言">
                                <el-checkbox v-model="form.expand.iswall" :true-label="'1'" :false-label="'0'"></el-checkbox>
                            </el-form-item>
                            <el-form-item label="是否可以关注用户">
                                <el-checkbox v-model="form.expand.isrelatio" :true-label="'1'" :false-label="'0'"></el-checkbox>
                            </el-form-item>
                            <el-form-item label="是否可以添加收藏">
                                <el-checkbox v-model="form.expand.isfavorite" :true-label="'1'" :false-label="'0'"></el-checkbox>
                            </el-form-item>
                            <el-form-item label="是否可以发表微博">
                                <el-checkbox v-model="form.expand.isweibo" :true-label="'1'" :false-label="'0'"></el-checkbox>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-col>
                <el-col :span="16"><div class="grid-content "></div></el-col>
            </el-row>
            <el-row>
                <div style="margin-top: 30px;">
                    <template >
                        <el-button type="primary" size="" @click="onSubmit()">
                            提交
                        </el-button>
                    </template>
                </div>
            </el-row>

        </el-card>
    </div>

    <style>
        .center_p{
            background: #ddd;
            padding: 7px 7px 7px 20px;
        }
        .el-upload-list__item is-success{
            display: none;
        }
        .userAuth .el-form-item {
            float: left;margin-right: 10px
        }
    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    form: {
                        groupid:"{:I('get.groupid')}",
                        name:'',
                        point:'',
                        starnum:'',
                        allowmessage:0,
                        allowpostnum:0,
                        usernamecolor:'',
                        description:'',
                        icon:'',
                        expand: {
                            upphotomax: '200',
                            ismsg: 0,
                            iswall: 0,
                            isrelatio: 0,
                            isfavorite: 0,
                            isweibo: 0,
                        },
                        allowpost:0,
                        allowpostverify:0,
                        allowupgrade:0,
                        allowsendmessage:0,
                        allowattachment:0,
                        allowsearch:0,
                    },
                    uploadConfig: {
                        uploadUrl: "{:U('Upload/UploadAdminApi/uploadFile')}",
                        max_upload: 1,//同时上传文件数
                        accept: 'image/*' //接收的文件类型，请看：https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-accept
                    },
                    uploadedLocalFileList: [], //本地上传的文件
                },
                watch: {},
                filters: {},
                methods: {
                    //获取会员组信息
                    getGroupinfo(groupid){
                        var that = this;
                        $.ajax({
                            url:"{:U('getGroupinfo')}",
                            dataType:"json",
                            type:"get",
                            data: {
                                "groupid": groupid,
                            },
                            success(res){
                                console.log(res)
                                if(res.status){
                                    that.form = res.data;
                                    console.log(that.form)
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })
                    },
                    onSubmit: function(){
                        var that = this;
                        $.ajax({
                            url:"{:U('edit')}",
                            dataType:"json",
                            type:"post",
                            data: that.form,
                            success(res){
                                if(res.status){
                                    layer.alert(res.info, { icon: 1, closeBtn: 0 }, function (index) {
                                        layer.close(index);
                                        Ztbcms.openNewIframeByUrl('会员组列表', '/index.php?g=Member&m=Group&a=index')
                                    });
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })
                    },
                    handleUploadSuccess: function (response, file, fileList) {
                        this.form.icon = response.data.url
                    },
                },
                mounted: function () {
                    if(this.form.groupid){
                        this.getGroupinfo(this.form.groupid)
                    }
                },

            })
        })
    </script>
</block>
