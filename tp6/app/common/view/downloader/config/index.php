<div id="app" style="padding: 8px;" v-cloak>
    <el-card>

        <div slot="header" class="clearfix">
            <span>下载配置</span>
        </div>

        <el-col :sm="24" :md="18">
            <el-form ref="elForm"
                     :model="formData"
                     :rules="rules" size="medium"
                     label-width="180px"
                     style="margin-top: 6px">
                <el-form-item label="访问域名" prop="downloader_domain">
                    <el-input type="text" v-model="formData.downloader_domain" placeholder="" clearable :style="{width: '100%'}">
                    </el-input>
                    <small>* 填写示例：http://ztbcms.com 请勿以 / 结尾</small>
                </el-form-item>
                <el-form-item label="任务超时时间（秒）" prop="downloader_retry_num">
                    <el-input type="number" v-model="formData.downloader_timeout" placeholder="" clearable :style="{width: '100%'}">
                    </el-input>
                    <small>* 执行超过X秒视为执行失败</small>
                </el-form-item>

                <el-form-item label="任务失败时" prop="downloader_retry_switch">
                    <el-radio-group v-model="formData.downloader_retry_switch" size="medium">
                        <el-radio v-for="(item, index) in downloaderRetrySwitchOptions" :key="index"
                                  :label="item.value"
                                  :disabled="item.disabled">
                            {{item.label}}
                        </el-radio>
                    </el-radio-group>
                </el-form-item>

                <el-form-item label="重启的次数" prop="downloader_retry_num">
                    <el-input type="number" v-model="formData.downloader_retry_num" placeholder="请输入需要重启的次数" clearable :style="{width: '100%'}">
                    </el-input>
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
        new Vue({
            el: '#app',
            components: {},
            props: [],
            data: function() {
                return {
                    request_url: "{:api_url('/common/downloader.Config/index')}",
                    formData: {
                        downloader_retry_switch: "",
                        downloader_retry_num: "",
                        downloader_timeout: "",
                        downloader_domain: "",
                    },
                    rules: {
                        downloader_retry_switch: [{
                            required: true,
                            message: '请输入任务启动失败是否重启',
                            trigger: 'blur'
                        }],
                        downloader_retry_num: [{
                            required: true,
                            message: '请输入重启的次数',
                            trigger: 'blur'
                        }],
                    },
                    downloaderRetrySwitchOptions: [{
                        "label": "重启",
                        "value": '1'
                    }, {
                        "label": "不重启",
                        "value": '0'
                    }]
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
                    this.$refs['elForm'].validate(function(valid){
                        if (!valid) return
                        that.httpPost(that.request_url, that.formData, function(res){
                            layer.msg(res.msg)
                        })
                    })
                },
                // 获取详情
                getDetail: function() {
                    var that = this
                    var formData = {}
                    formData['_action'] = 'getDetail'
                    that.httpGet(that.request_url, formData, function(res){
                        that.formData = res.data
                    })
                }
            }
        });
    });
</script>

<style>

</style>
