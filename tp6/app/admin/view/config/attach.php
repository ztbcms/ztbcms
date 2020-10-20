<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">

            <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="180px">
                <el-form-item label="网站存储方案" prop="attachment_driver">
                    <el-select v-model="formData.attachment_driver" placeholder="请选择网站存储方案" clearable
                               :style="{width: '100%'}">
                            <el-option label="本地存储驱动" value="Local"></el-option>
                            <el-option label="阿里云OSS上传驱动【暂不支持水印】" value="Aliyun"></el-option>
                    </el-select>
                </el-form-item>
                <template v-if="formData.attachment_driver == 'Ftp'">
                    <el-form-item label="FTP服务器地址" prop="ftphost">
                        <el-input v-model="formData.ftphost" placeholder="请输入FTP服务器地址" clearable
                                  :style="{width: '100%'}">
                        </el-input>
                    </el-form-item>
                    <el-form-item label="FTP服务器端口" prop="ftpport">
                        <el-input v-model="formData.ftpport" placeholder="请输入FTP服务器端口" clearable
                                  :style="{width: '100%'}">
                        </el-input>
                    </el-form-item>
                    <el-form-item label="FTP上传目录" prop="ftpuppat">
                        <el-input v-model="formData.ftpuppat" placeholder="请输入FTP上传目录" clearable
                                  :style="{width: '100%'}">
                            <template slot="append">"/"表示上传到FTP根目录</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="FTP用户名" prop="ftpuser">
                        <el-input v-model="formData.ftpuser" placeholder="请输入FTP用户名" clearable
                                  :style="{width: '100%'}">
                        </el-input>
                    </el-form-item>
                    <el-form-item label="FTP密码" prop="ftppassword">
                        <el-input v-model="formData.ftppassword" placeholder="请输入FTP密码" clearable
                                  :style="{width: '100%'}">
                        </el-input>
                    </el-form-item>
                    <el-form-item label="FTP是否开启被动模式" prop="ftppasv">
                        <el-radio-group v-model="formData.ftppasv" size="medium">
                            <el-radio v-for="(item, index) in ftppasvOptions" :key="index"
                                      :label="item.value"
                                      :disabled="item.disabled">{{item.label}}
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="FTP是否使用SSL连接" prop="ftpssl">
                        <el-radio-group v-model="formData.ftpssl" size="medium">
                            <el-radio v-for="(item, index) in ftpsslOptions" :key="index"
                                      :label="item.value"
                                      :disabled="item.disabled">{{item.label}}
                            </el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="FTP超时时间" prop="ftptimeout">
                        <el-input v-model="formData.ftptimeout" placeholder="请输入FTP超时时间" clearable
                                  :style="{width: '100%'}">
                        </el-input>
                    </el-form-item>
                </template>
                <template v-if="formData.attachment_driver == 'Aliyun'">
                    <el-form-item label="oss-keyId"
                                  prop="attachment_aliyun_key_id">
                        <el-input v-model="formData.attachment_aliyun_key_id"
                                  placeholder="请输入OSS-accessKeyId"></el-input>
                    </el-form-item>
                    <el-form-item label="oss-keySecret"
                                  prop="attachment_aliyun_key_secret">
                        <el-input v-model="formData.attachment_aliyun_key_secret"
                                  placeholder="请输入OSS-accessKeySecret"></el-input>
                    </el-form-item>
                    <el-form-item label="oss-Endpoint"
                                  prop="attachment_aliyun_endpoint">
                        <el-input v-model="formData.attachment_aliyun_endpoint"
                                  placeholder="请输入OSS-Endpoint(同一地域可以使用内网)"></el-input>
                    </el-form-item>
                    <el-form-item label="oss-bucket"
                                  prop="attachment_aliyun_bucket">
                        <el-input v-model="formData.attachment_aliyun_bucket"
                                  placeholder="请输入OSS-bucket"></el-input>
                    </el-form-item>
                    <el-form-item label="oss-外网域名"
                                  prop="attachment_aliyun_domain">
                        <el-input v-model="formData.attachment_aliyun_domain"
                                  placeholder="请输入OSS-外网域名"></el-input>
                    </el-form-item>
                </template>
                <el-form-item label="允许上传附件大小" prop="uploadmaxsize">
                    <el-input v-model="formData.uploadmaxsize" placeholder="请输入允许上传附件大小" clearable
                              :style="{width: '100%'}">
                        <template slot="append">KB</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="允许上传附件类型" prop="uploadallowext">
                    <el-input v-model="formData.uploadallowext" placeholder="请输入允许上传附件类型" clearable
                              :style="{width: '100%'}">
                        <template slot="append">多个用"|"隔开</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="前台允许上传附件大小" prop="qtuploadmaxsize">
                    <el-input v-model="formData.qtuploadmaxsize" placeholder="请输入前台允许上传附件大小" clearable
                              :style="{width: '100%'}">
                        <template slot="append">KB</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="前台允许上传附件类型" prop="qtuploadallowext">
                    <el-input v-model="formData.qtuploadallowext" placeholder="请输入前台允许上传附件类型" clearable
                              :style="{width: '100%'}">
                        <template slot="append">多个用"|"隔开</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="保存远程图片过滤域名" prop="fileexclude">
                    <el-input v-model="formData.fileexclude" placeholder="请输入保存远程图片过滤域名" clearable
                              :style="{width: '100%'}">
                        <template slot="append">多个用"|"隔开，域名以"/"结尾，例如：http://www.ztbcms.com/</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="是否开启图片水印" prop="watermarkenable">
                    <el-radio-group v-model="formData.watermarkenable" size="medium">
                        <el-radio v-for="(item, index) in watermarkenableOptions" :key="index"
                                  :label="item.value"
                                  :disabled="item.disabled">{{item.label}}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="水印添加条件（宽）" prop="watermarkminwidth">
                    <el-input v-model="formData.watermarkminwidth" placeholder="请输入水印添加条件（宽）" clearable
                              :style="{width: '100%'}">
                        <template slot="append">PX</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="水印添加条件（高）" prop="watermarkminheight">
                    <el-input v-model="formData.watermarkminheight" placeholder="请输入水印添加条件（高）" clearable
                              :style="{width: '100%'}">
                        <template slot="append">PX</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="水印图片" prop="watermarkimg">
                    <el-input v-model="formData.watermarkimg" placeholder="请输入水印图片" clearable
                              :style="{width: '100%'}">
                        <template slot="append">水印存放路径从网站根目录起</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="水印透明度" prop="watermarkpct">
                    <el-input v-model="formData.watermarkpct" placeholder="请输入水印透明度" clearable
                              :style="{width: '100%'}">
                        <template slot="append">请设置为0-100之间的数字，0代表完全透明，100代表不透明</template>
                    </el-input>
                </el-form-item>
                <el-form-item label="JPEG 水印质量" prop="watermarkquality">
                    <el-input v-model="formData.watermarkquality" placeholder="请输入JPEG 水印质量" clearable
                              :style="{width: '100%'}">
                        <template slot="append">水印质量请设置为0-100之间的数字,决定 jpg 格式图片的质量</template>
                    </el-input>
                </el-form-item>

                <el-form-item label="JPEG 水印质量" prop="watermarkquality">
                    <div class="locate">
                        <ul class="cc" id="J_locate_list">

                            <li :class="{'current': formData.watermarkpos == 1}"><a href="javascript:void(0)" @click="formData.watermarkpos = 1" data-value="1">左上</a></li>
                            <li :class="{'current': formData.watermarkpos == 2}"><a href="javascript:void(0)" @click="formData.watermarkpos = 2" data-value="2">中上</a></li>
                            <li :class="{'current': formData.watermarkpos == 3}"><a href="javascript:void(0)" @click="formData.watermarkpos = 3" data-value="3">右上</a></li>
                            <li :class="{'current': formData.watermarkpos == 4}"><a href="javascript:void(0)" @click="formData.watermarkpos = 4" data-value="4">左中</a></li>
                            <li :class="{'current': formData.watermarkpos == 5}"><a href="javascript:void(0)" @click="formData.watermarkpos = 5" data-value="5">中心</a></li>
                            <li :class="{'current': formData.watermarkpos == 6}"><a href="javascript:void(0)" @click="formData.watermarkpos = 6" data-value="6">右中</a></li>
                            <li :class="{'current': formData.watermarkpos == 7}"><a href="javascript:void(0)" @click="formData.watermarkpos = 7" data-value="7">左下</a></li>
                            <li :class="{'current': formData.watermarkpos == 8}"><a href="javascript:void(0)" @click="formData.watermarkpos = 8" data-value="8">中下</a></li>
                            <li :class="{'current': formData.watermarkpos == 9}"><a href="javascript:void(0)" @click="formData.watermarkpos = 9" data-value="9">右下</a></li>

                        </ul>
                        <input v-model="formData.watermarkpos" id="J_locate_input" name="watermarkpos"
                               type="hidden" >
                    </div>
                </el-form-item>


                <el-form-item size="large">
                    <el-button type="primary" @click="submitForm">保存</el-button>
                </el-form-item>
            </el-form>

        </el-col>
    </el-card>
</div>

<script>
    $(document).ready(function () {
        window.app = new Vue({
            el: '#app',
            // 插入export default里面的内容
            components: {},
            props: [],
            data: function() {
                return {
                    formData: {
                        attachment_driver: "",
                        attachment_aliyun_key_id: "",
                        attachment_aliyun_key_secret: "",
                        attachment_aliyun_endpoint: "",
                        attachment_aliyun_bucket: "",
                        attachment_aliyun_domain: "",
                        uploadmaxsize: "",
                        uploadallowext: "",
                        qtuploadmaxsize: "",
                        qtuploadallowext: "",
                        fileexclude: "",
                        ftphost: "",
                        ftpport: "",
                        ftpuppat: "",
                        ftpuser: "",
                        ftppassword: "",
                        ftppasv: "",
                        ftpssl: "",
                        ftptimeout: "",
                        watermarkenable: "",
                        watermarkminwidth: "",
                        watermarkminheight: "",
                        watermarkimg: "",
                        watermarkpct: "",
                        watermarkquality: "",
                        watermarkpos: "1",
                    },
                    rules: {
                        attachment_driver: [{
                            required: true,
                            message: '请选择网站存储方案',
                            trigger: 'change'
                        }],
                        uploadmaxsize: [{
                            required: true,
                            message: '请输入允许上传附件大小',
                            trigger: 'blur'
                        }],
                        uploadallowext: [{
                            required: true,
                            message: '请输入允许上传附件类型',
                            trigger: 'blur'
                        }],
                        qtuploadmaxsize: [{
                            required: true,
                            message: '请输入前台允许上传附件大小',
                            trigger: 'blur'
                        }],
                        qtuploadallowext: [{
                            required: true,
                            message: '请输入前台允许上传附件类型',
                            trigger: 'blur'
                        }],
                        watermarkenable: [{
                            required: true,
                            message: '是否开启图片水印不能为空',
                            trigger: 'change'
                        }],
                        watermarkminwidth: [{
                            required: true,
                            message: '请输入水印添加条件（宽）',
                            trigger: 'blur'
                        }],
                        watermarkminheight: [{
                            required: true,
                            message: '请输入水印添加条件（高）',
                            trigger: 'blur'
                        }],
                        watermarkimg: [{
                            required: true,
                            message: '请输入水印图片',
                            trigger: 'blur'
                        }],
                        watermarkpct: [{
                            required: true,
                            message: '请输入水印透明度',
                            trigger: 'blur'
                        }],
                        watermarkquality: [{
                            required: true,
                            message: '请输入JPEG 水印质量',
                            trigger: 'blur'
                        }],
                    },
                    attachment_driverOptions: [{
                        "label": "选项一",
                        "value": '1'
                    }, {
                        "label": "选项二",
                        "value": '2'
                    }],
                    ftppasvOptions: [{
                        "label": "开启",
                        "value": '1'
                    }, {
                        "label": "关闭",
                        "value": '0'
                    }],
                    ftpsslOptions: [{
                        "label": "开启",
                        "value": '1'
                    }, {
                        "label": "关闭",
                        "value": '0'
                    }],
                    watermarkenableOptions: [{
                        "label": "开启",
                        "value": '1'
                    }, {
                        "label": "关闭",
                        "value": '0'
                    }],
                }
            },
            computed: {},
            watch: {},
            created: function() {
            },
            mounted: function() {
                this.getDetail()
            },
            methods: {
                submitForm: function() {
                    var that = this
                    this.$refs['elForm'].validate(function (valid) {
                        if (!valid) return
                        that.httpPost("{:api_url('/admin/Config/attach')}", that.formData, function(res){
                            layer.msg(res.msg)
                        })
                    })
                },
                // 获取详情
                getDetail: function() {
                    var that = this
                    var formData = {}
                    formData['_action'] = 'getDetail'
                    that.httpGet("{:api_url('/admin/Config/attach')}", formData, function(res){
                        that.formData = res.data.config
                    })
                }
            }
        });
    });


    // $(function () {
    //     //水印位置
    //     $('#J_locate_list > li > a').click(function (e) {
    //         e.preventDefault();
    //         var $this = $(this);
    //         $this.parents('li').addClass('current').siblings('.current').removeClass('current');
    //         $('#J_locate_input').val($this.data('value'));
    //
    //         window.app.formData.watermarkpos = $this.data('value');
    //     });
    // });

</script>


<style>
    .locate {
        border: 1px solid #ccc;
        background: #fff;
        width: 209px;
        height: 123px;
        margin: 0;
        padding: 0;
    }

    .locate ul {
        margin-top: -1px;
        margin-left: -1px;
        padding: 0;
    }

    .locate li {
        border-left: 1px solid #ccc;
        border-top: 1px solid #ccc;
        float: left;
        width: 69px;
        height: 40px;
        text-align: center;
        list-style: none;
    }

    .locate li a {
        color: #333;
        display: block;
        border: 1px solid #fff;
        line-height: 38px;
        text-decoration: none;
    }

    .locate li a:hover {
        text-decoration: none;
        background: #e4e4e4;
    }

    .locate li.current a {
        background: #266aae;
        color: #fff;
    }
    .cc:after {
        content: '\20';
        display: block;
        height: 0;
        clear: both;
        visibility: hidden;
    }
</style>

