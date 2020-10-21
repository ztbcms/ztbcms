<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18" >

            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="160px">
                        <el-form-item label="站点名称" prop="sitename">
                            <el-input v-model="formData.sitename" placeholder="请输入站点名称" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="网站访问地址" prop="siteurl">
                            <el-input v-model="formData.siteurl" placeholder="请输入网站访问地址" clearable :style="{width: '100%'}">
                            </el-input>
                            <small style="color: #858689;">请以"/"结尾</small>
                        </el-form-item>
                        <el-form-item label="附件访问地址" prop="sitefileurl">
                            <el-input v-model="formData.sitefileurl" placeholder="请输入附件访问地址" clearable :style="{width: '100%'}">
                            </el-input>
                            <small style="color: #858689;">请以"/"结尾</small>
                        </el-form-item>
                        <el-form-item label="联系邮箱" prop="siteemail">
                            <el-input v-model="formData.siteemail" placeholder="请输入联系邮箱" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="网站关键字" prop="sitekeywords">
                            <el-input v-model="formData.sitekeywords" placeholder="请输入网站关键字" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="网站简介" prop="siteinfo">
                            <el-input v-model="formData.siteinfo" type="textarea" placeholder="请输入网站简介"
                                      :autosize="{minRows: 4, maxRows: 4}" :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="验证码类型" prop="checkcode_type">
                            <el-select v-model="formData.checkcode_type" placeholder="请选择验证码类型" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in checkcode_typeOptions" :key="index" :label="item.label"
                                           :value="item.value" :disabled="item.disabled"></el-option>
                            </el-select>
                        </el-form-item>
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
        new Vue({
            el: '#app',
            components: {},
            props: [],
            data: function() {
                return {
                    formData: {
                        sitename: "",
                        siteurl: "",
                        sitefileurl: "",
                        siteemail: "",
                        sitekeywords: "",
                        siteinfo: "",
                        checkcode_type: "",
                    },
                    rules: {
                        sitename: [{
                            required: true,
                            message: '请输入站点名称',
                            trigger: 'blur'
                        }],
                        siteurl: [{
                            required: true,
                            message: '请输入网站访问地址',
                            trigger: 'blur'
                        }],
                        sitefileurl: [{
                            required: true,
                            message: '请输入附件访问地址',
                            trigger: 'blur'
                        }],
                        siteemail: [{
                            required: true,
                            message: '请输入联系邮箱',
                            trigger: 'blur'
                        }],
                        sitekeywords: [{
                            required: true,
                            message: '请输入网站关键字',
                            trigger: 'blur'
                        }],
                        siteinfo: [{
                            required: true,
                            message: '请输入网站简介',
                            trigger: 'blur'
                        }],
                        checkcode_type: [{
                            required: true,
                            message: '请选择验证码类型',
                            trigger: 'change'
                        }],
                    },
                    domainaccessOptions: [{
                        "label": "开启指定域名访问",
                        "value": '1'
                    }, {
                        "label": "关闭指定域名访问",
                        "value": '0'
                    }],
                    generateOptions: [{
                        "label": "生成静态",
                        "value": '1'
                    }, {
                        "label": "不生成静态",
                        "value": '0'
                    }],
                    tagurlOptions: [{
                        "label": "选项一",
                        "value": 1
                    }, {
                        "label": "选项二",
                        "value": 2
                    }],
                    checkcode_typeOptions: [{
                        "label": "数字字母混合",
                        "value": '0'
                    }, {
                        "label": "纯数字",
                        "value": '1'
                    }, {
                        "label": "纯字母",
                        "value": '2'
                    }],
                }
            },
            computed: {},
            watch: {},
            created: function() {},
            mounted: function() {
                this.getDetail()
            },
            methods: {
                // 表单提交
                submitForm: function() {
                    var that = this
                    this.$refs['elForm'].validate(function(valid) {
                        if (!valid) return;
                        //  提交表单
                        that.httpPost("{:api_url('/admin/Config/index')}", that.formData, function(res){
                            layer.msg(res.msg)
                        })
                    })
                },
                // 获取详情
                getDetail: function() {
                    var that = this
                    var formData = {}
                    formData['_action'] = 'getDetail'
                    that.httpGet("{:api_url('/admin/Config/index')}", formData, function(res){
                        that.formData = res.data
                    })
                }
            }
        });
    });
</script>

<style>

</style>
