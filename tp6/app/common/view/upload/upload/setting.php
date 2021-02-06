<div>
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="24" :md="18">
                <template>
                    <div>
                        <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="180px">
                            <el-form-item label="存储方式" prop="attachment_driver">
                                <el-select v-model="formData.attachment_driver" placeholder="请选择网站存储方案" clearable
                                           :style="{width: '100%'}">
                                    <?php foreach ($dirverList as $key => $value): ?>
                                        <el-option label="{$value}" value="{$key}"></el-option>
                                    <?php endforeach; ?>
                                </el-select>
                            </el-form-item>
                            <!--阿里云-->
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
                                <el-form-item label="oss-读写权限"
                                              prop="attachment_aliyun_privilege">
                                    <el-radio v-model="formData.attachment_aliyun_privilege" label="1">公共读</el-radio>
                                    <el-radio v-model="formData.attachment_aliyun_privilege" label="2">私有</el-radio>
                                </el-form-item>
                                <el-form-item v-if="formData.attachment_aliyun_privilege==2" label="oss-临时访问链接过期时间"
                                              prop="attachment_aliyun_expire_time">
                                    <el-input type="number" v-model="formData.attachment_aliyun_expire_time"
                                              placeholder="请输入临时链接过期时间">
                                        <template slot="append">秒</template>
                                    </el-input>
                                </el-form-item>
                            </template>
                            <!--本地-->
                            <template v-if="formData.attachment_driver == 'Local'">
                                <el-form-item label="附件域名"
                                              prop="attachment_local_domain">
                                    <el-input v-model="formData.attachment_local_domain"
                                              placeholder="请输入附件域名，示例：http://ztbcms.com"></el-input>
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
                            <template v-if="formData.attachment_driver == 'Local'">
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
                                            <li :class="{'current':formData.watermarkpos==1}">
                                                <a href="" data-value="1">左上</a>
                                            </li>
                                            <li :class="{'current':formData.watermarkpos==2}">
                                                <a href="" data-value="2">中上</a>
                                            </li>
                                            <li :class="{'current':formData.watermarkpos==3}">
                                                <a href="" data-value="3">右上</a>
                                            </li>
                                            <li :class="{'current':formData.watermarkpos==4}">
                                                <a href="" data-value="4">左中</a>
                                            </li>
                                            <li :class="{'current':formData.watermarkpos==5}">
                                                <a href="" data-value="5">中心</a>
                                            </li>
                                            <li :class="{'current':formData.watermarkpos==6}">
                                                <a href="" data-value="6">右中</a>
                                            </li>
                                            <li :class="{'current':formData.watermarkpos==7}">
                                                <a href="" data-value="7">左下</a>
                                            </li>
                                            <li :class="{'current':formData.watermarkpos==8}">
                                                <a href="" data-value="8">中下</a>
                                            </li>
                                            <li :class="{'current':formData.watermarkpos==9}">
                                                <a href="" data-value="9">右下</a>
                                            </li>
                                        </ul>
                                        <input v-model="formData.watermarkpos" id="J_locate_input" name="watermarkpos"
                                               type="hidden" value="{$config.watermarkpos}">
                                    </div>
                                </el-form-item>
                            </template>
                            <el-form-item size="large">
                                <el-button type="primary" @click="submitForm">保存</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </template>
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
                            attachment_driver: "{$config.attachment_driver}",
                            attachment_aliyun_key_id: "{$config.attachment_aliyun_key_id}",
                            attachment_aliyun_key_secret: "{$config.attachment_aliyun_key_secret}",
                            attachment_aliyun_endpoint: "{$config.attachment_aliyun_endpoint}",
                            attachment_aliyun_bucket: "{$config.attachment_aliyun_bucket}",
                            attachment_aliyun_domain: "{$config.attachment_aliyun_domain}",
                            attachment_aliyun_privilege: "{$config.attachment_aliyun_privilege}",
                            attachment_aliyun_expire_time: "{$config.attachment_aliyun_expire_time}",
                            uploadmaxsize: "{$config.uploadmaxsize}",
                            uploadallowext: "{$config.uploadallowext}",
                            qtuploadmaxsize: "{$config.qtuploadmaxsize}",
                            qtuploadallowext: "{$config.qtuploadallowext}",
                            fileexclude: "{$config.fileexclude|default=''}",
                            watermarkenable: "{$config.watermarkenable|default=''}",
                            watermarkminwidth: "{$config.watermarkminwidth|default=''}",
                            watermarkminheight: "{$config.watermarkminheight|default=''}",
                            watermarkimg: "{$config.watermarkimg|default=''}",
                            watermarkpct: "{$config.watermarkpct|default=''}",
                            watermarkquality: "{$config.watermarkquality|default=''}",
                            watermarkpos: "{$config.watermarkpos|default=''}",
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
                        },
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
                },
                methods: {
                    submitForm: function() {
                        var that = this
                        this.$refs['elForm'].validate(function(valid) {
                            if (!valid) return;
                            $.ajax({
                                url: "{:api_url('common/upload.upload/setting')}",
                                method: 'post',
                                dataType: 'json',
                                data: that.formData,
                                success: function (res) {
                                    if (!res.status) {
                                        layer.msg(res.msg)
                                    } else {
                                        layer.msg(res.msg)
                                    }
                                }
                            });
                        })
                    }
                }
            });
        });


        $(function () {
            //水印位置
            $('#J_locate_list > li > a').click(function (e) {
                e.preventDefault();
                var $this = $(this);
                $this.parents('li').addClass('current').siblings('.current').removeClass('current');
                $('#J_locate_input').val($this.data('value'));

                window.app.formData.watermarkpos = $this.data('value');
            });
        });

    </script>

    <style>
        html, body,
        div, dl, dt, dd, ul, p, th, td,
        h1, h2, h3, h4, h5, h6,
        pre, code, form,
        fieldset, legend {
            margin: 0;
            padding: 0;
        }

        cite,
        em,
        th {
            font-style: inherit;
            font-weight: inherit;
        }

        strong {
            font-weight: 700;
        }

        td,
        th,
        div {
            word-break: break-all;
            word-wrap: break-word;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        th {
            text-align: left;
            font-weight: 100;
        }

        ol li {
            list-style: decimal outside;
        }

        ol {
            padding: 0 0 0 18px;
            margin: 0;
        }

        li {
            list-style: none;
        }

        img {
            border: 0;
        }

        html {
            -webkit-text-size-adjust: none;
        }

        .locate {
            border: 1px solid #ccc;
            background: #fff;
            width: 209px;
        }

        .locate ul {
            margin-top: -1px;
            margin-left: -1px;
        }

        .locate li {
            border-left: 1px solid #ccc;
            border-top: 1px solid #ccc;
            float: left;
            width: 69px;
            height: 40px;
            text-align: center;
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

        .cc {
            zoom: 1;
        }

        .cc:after {
            content: '\20';
            display: block;
            height: 0;
            clear: both;
            visibility: hidden;
        }

    </style>
</div>