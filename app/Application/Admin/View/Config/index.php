<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="18" :md="18" >
                <!--                插入template 文件-->
                <template>
                    <div>
                        <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="200px"
                                 label-position="left">
                            <el-form-item label="站点名称" prop="sitename">
                                <el-input v-model="formData.sitename" placeholder="请输入站点名称" clearable :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="网站访问地址" prop="siteurl">
                                <el-input v-model="formData.siteurl" placeholder="请输入网站访问地址" clearable :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="附件访问地址" prop="sitefileurl">
                                <el-input v-model="formData.sitefileurl" placeholder="请输入附件访问地址" clearable :style="{width: '100%'}">
                                </el-input>
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
                            <el-form-item label="后台指定域名访问" prop="domainaccess">
                                <el-select v-model="formData.domainaccess" placeholder="请选择后台指定域名访问" clearable :style="{width: '100%'}">
                                    <el-option v-for="(item, index) in domainaccessOptions" :key="index" :label="item.label"
                                               :value="item.value" :disabled="item.disabled"></el-option>
                                </el-select>
                                <span class="gray"> （该功能需要配合“域名绑定”模块使用，需要在域名绑定模块中添加域名！）</span>
                            </el-form-item>
                            <el-form-item label="是否生成首页" prop="generate">
                                <el-select v-model="formData.generate" placeholder="请选择是否生成首页" clearable :style="{width: '100%'}">
                                    <el-option v-for="(item, index) in generateOptions" :key="index" :label="item.label"
                                               :value="item.value" :disabled="item.disabled"></el-option>
                                </el-select>
                            </el-form-item>

                            <el-form-item label="首页URL规则" prop="index_urlruleid">

                                <el-select v-show="formData.generate === '1'" v-model="formData.index_urlruleid" placeholder="请选择首页URL规则" clearable :style="{width: '100%'}">
                                    <volist name="IndexURL[1]" id="vo" key="key">
                                        <el-option label="{$vo}" value="{$key}" ></el-option>
                                    </volist>
                                </el-select>


                                <el-select v-show="formData.generate === '0'" v-model="formData.index_urlruleid" placeholder="请选择首页URL规则" clearable :style="{width: '100%'}">
                                    <volist name="IndexURL[0]" id="vo" key="key">
                                        <el-option label="{$vo}" value="{$key}" ></el-option>
                                    </volist>
                                </el-select>
                            </el-form-item>

                            <el-form-item label="首页模板" prop="indextp">
                                <el-select v-model="formData.indextp" placeholder="请选择首页模板" clearable :style="{width: '100%'}">
                                    <volist name="indextp" id="vo">
                                        <el-option label="{$vo}" value="{$vo}" ></el-option>
                                    </volist>
                                </el-select>
                                <span class="gray"> 新增模板以index_x<?php echo C("TMPL_TEMPLATE_SUFFIX")?>形式</span>
                            </el-form-item>

                            <el-form-item label="TagURL规则" prop="tagurl">
                                <el-select v-model="formData.tagurl" placeholder="请选择TagURL规则" clearable :style="{width: '100%'}">
                                    <volist name="TagURL" id="vo" key="key">
                                        <el-option label="{$vo}" value="{$key}" ></el-option>
                                    </volist>
                                </el-select>
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
                // 插入export default里面的内容
                components: {},
                props: [],
                data() {
                    return {
                        formData: {
                            sitename: "{$Site.sitename}",
                            siteurl: "{$Site.siteurl}",
                            sitefileurl: "{$Site.sitefileurl}",
                            siteemail: "{$Site.siteemail}",
                            sitekeywords: "{$Site.sitekeywords}",
                            siteinfo: "{$Site.siteinfo}",
                            domainaccess: "{$Site.domainaccess}",
                            generate: "{$Site.generate}",
                            index_urlruleid: "{$Site.index_urlruleid}",
                            indextp: "{$Site.indextp}",
                            tagurl: "{$Site.tagurl}",
                            checkcode_type: "{$Site.checkcode_type}",
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
                            domainaccess: [{
                                required: true,
                                message: '请选择后台指定域名访问',
                                trigger: 'change'
                            }],
                            generate: [{
                                required: true,
                                message: '请选择是否生成首页',
                                trigger: 'change'
                            }],
                            index_urlruleid: [{
                                required: true,
                                message: '请选择首页URL规则',
                                trigger: 'change'
                            }],
                            indextp: [{
                                required: true,
                                message: '请选择首页模板',
                                trigger: 'change'
                            }],
                            tagurl: [{
                                required: true,
                                message: '请选择TagURL规则',
                                trigger: 'change'
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
                created() {},
                mounted() {

                },
                methods: {
                    submitForm() {
                        this.$refs['elForm'].validate(valid => {
                            if (!valid) return;
                            // TODO 提交表单
                            $.ajax({
                                url: "{:U('Config/index')}",
                                method: 'post',
                                dataType: 'json',
                                data: this.formData,
                                success: function (res) {
                                    if (!res.status) {
                                        layer.msg(res.info)
                                    } else {
                                        layer.msg(res.info)
                                    }
                                }
                            });
                        })
                    }
                }
            });
        });
    </script>

    <style>

    </style>
</block>
