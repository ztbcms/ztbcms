<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="18" :md="18">
                <!--                插入template 文件-->
                <template>
                    <div>
                        <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="200px"
                                 label-position="left">

                            <h3>Cookie配置</h3>

                            <el-form-item label="Cookie有效期" prop="COOKIE_EXPIRE">
                                <el-input v-model="formData.COOKIE_EXPIRE" placeholder="请输入单行文本Cookie有效期" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">单位秒</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="Cookie有效域名" prop="COOKIE_DOMAIN">
                                <el-input v-model="formData.COOKIE_DOMAIN" placeholder="请输入Cookie有效域名" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">例如：“.ztbcms.com”表示这个域名下都可以访问</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="Cookie路径" prop="COOKIE_PATH">
                                <el-input v-model="formData.COOKIE_PATH" placeholder="请输入Cookie路径" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">一般是“/”</template>
                                </el-input>
                            </el-form-item>

                            <h3>Session配置</h3>

                            <el-form-item label="Session前缀" prop="SESSION_PREFIX">
                                <el-input v-model="formData.SESSION_PREFIX" placeholder="请输入Session前缀" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">一般为空即可</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="Session域名" prop="SESSION_OPTIONS.domain">
                                <el-input v-model="formData.SESSION_OPTIONS.domain" placeholder="请输入Session域名"
                                          clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">一般为空即可</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="Session有效时间" prop="SESSION_OPTIONS.expire">
                                <el-input v-model="formData.SESSION_OPTIONS.expire" placeholder="请输入Session有效时间"
                                          clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">一般是“.ztbcms.com”</template>
                                </el-input>
                            </el-form-item>

                            <h3>错误设置</h3>

                            <el-form-item label="显示错误信息" prop="SHOW_ERROR_MSG">
                                <el-radio-group v-model="formData.SHOW_ERROR_MSG" size="medium">
                                    <el-radio v-for="(item, index) in SHOW_ERROR_MSGOptions" :key="index"
                                              :label="item.value"
                                              :disabled="item.disabled">{{item.label}}
                                    </el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="错误显示信息" prop="ERROR_MESSAGE">
                                <el-input v-model="formData.ERROR_MESSAGE" placeholder="请输入错误显示信息" clearable
                                          :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="错误定向页面" prop="ERROR_PAGE">
                                <el-input v-model="formData.ERROR_PAGE" placeholder="请输入错误定向页面" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">例如：http://www.ztbcms.com/error.html</template>
                                </el-input>
                            </el-form-item>

                            <h3>URL设置</h3>

                            <el-form-item label="URL不区分大小写" prop="URL_CASE_INSENSITIVE">
                                <el-radio-group v-model="formData.URL_CASE_INSENSITIVE" size="medium">
                                    <el-radio v-for="(item, index) in URL_CASE_INSENSITIVEOptions" :key="index"
                                              :label="item.value"
                                              :disabled="item.disabled">{{item.label}}
                                    </el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="URL访问模式" prop="URL_MODEL">
                                <el-select v-model="formData.URL_MODEL" placeholder="请选择URL访问模式" clearable
                                           :style="{width: '100%'}">
                                    <el-option v-for="(item, index) in URL_MODELOptions" :key="index"
                                               :label="item.label"
                                               :value="item.value" :disabled="item.disabled"></el-option>
                                </el-select>
                                <span>除了普通模式外其他模式可能需要服务器伪静态支持，同时需要写相应伪静态规则！</span>
                            </el-form-item>
                            <el-form-item label="PATHINFO模式参数分割线" prop="URL_PATHINFO_DEPR">
                                <el-input v-model="formData.URL_PATHINFO_DEPR" placeholder="请输入PATHINFO模式参数分割线"
                                          clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">例如：“/”</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="URL伪静态后缀" prop="URL_HTML_SUFFIX">
                                <el-input v-model="formData.URL_HTML_SUFFIX" placeholder="请输入URL伪静态后缀" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">例如：“.html”</template>
                                </el-input>
                            </el-form-item>

                            <h3>表单令牌</h3>

                            <el-form-item label="使用说明" prop="">
                                <p>开启前，需要在行为管理 view_filter 行为里添加 phpfile:TokenBuildBehavior 行为才能正常启用。</p>
                            </el-form-item>

                            <el-form-item label="是否开启令牌验证" prop="TOKEN_ON">
                                <el-radio-group v-model="formData.TOKEN_ON" size="medium">
                                    <el-radio v-for="(item, index) in TOKEN_ONOptions" :key="index" :label="item.value"
                                              :disabled="item.disabled">{{item.label}}
                                    </el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="表单隐藏字段名称" prop="TOKEN_NAME">
                                <el-input v-model="formData.TOKEN_NAME" placeholder="请输入表单隐藏字段名称" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">令牌验证的表单隐藏字段名称！</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="令牌哈希验证规则" prop="TOKEN_TYPE">
                                <el-input v-model="formData.TOKEN_TYPE" placeholder="请输入令牌哈希验证规则" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">令牌哈希验证规则 默认为MD5</template>
                                </el-input>
                            </el-form-item>

                            <h3>分页配置</h3>

                            <el-form-item label="默认分页数" prop="PAGE_LISTROWS">
                                <el-input v-model="formData.PAGE_LISTROWS" placeholder="请输入默认分页数" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">默认20！</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="分页变量" prop="VAR_PAGE">
                                <el-input v-model="formData.VAR_PAGE" placeholder="请输入分页变量" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">默认：page，建议不修改</template>
                                </el-input>
                            </el-form-item>

                            <h3>杂项配置</h3>

                            <el-form-item label="默认分页模板" prop="PAGE_TEMPLATE">
                                <el-input v-model="formData.PAGE_TEMPLATE" type="textarea" placeholder="请输入默认分页模板"
                                          :autosize="{minRows: 4, maxRows: 4}" :style="{width: '100%'}"></el-input>

<!--                                <textarea name="PAGE_TEMPLATE" style="width:500px;">{$addition.PAGE_TEMPLATE}</textarea>-->

                                <span>当没有设置分页模板时，默认使用该项设置</span>
                            </el-form-item>
                            <el-form-item label="默认模块" prop="DEFAULT_MODULE">
                                <el-input v-model="formData.DEFAULT_MODULE" placeholder="请输入默认模块" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">默认：Content，建议不修改，填写时注意大小写</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="默认时区" prop="DEFAULT_TIMEZONE">
                                <el-input v-model="formData.DEFAULT_TIMEZONE" placeholder="请输入默认时区" clearable
                                          :style="{width: '100%'}"></el-input>
                            </el-form-item>
                            <el-form-item label="AJAX 数据返回格式" prop="DEFAULT_AJAX_RETURN">
                                <el-input v-model="formData.DEFAULT_AJAX_RETURN" placeholder="请输入AJAX 数据返回格式" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">默认AJAX 数据返回格式,可选JSON XML ...</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="默认参数过滤方法" prop="DEFAULT_FILTER">
                                <el-input v-model="formData.DEFAULT_FILTER" placeholder="请输入默认参数过滤方法" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">默认参数过滤方法 用于 $this->_get('变量名');$this->_post('变量名')...
                                    </template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="默认语言" prop="DEFAULT_LANG">
                                <el-input v-model="formData.DEFAULT_LANG" placeholder="请输入默认语言" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">默认语言</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="数据缓存类型" prop="DATA_CACHE_TYPE">
                                <el-select v-model="formData.DATA_CACHE_TYPE" placeholder="请选择数据缓存类型" clearable
                                           :style="{width: '100%'}">
                                    <el-option v-for="(item, index) in DATA_CACHE_TYPEOptions" :key="index" :label="item.label"
                                               :value="item.value" :disabled="item.disabled"></el-option>
                                </el-select>
                                <span> 数据缓存类型,支持:File|Redis|Memcache|Xcache</span>
                            </el-form-item>
                            <el-form-item label="子目录缓存" prop="DATA_CACHE_SUBDIR">
                                <el-radio-group v-model="formData.DATA_CACHE_SUBDIR" size="medium">
                                    <el-radio v-for="(item, index) in DATA_CACHE_SUBDIROptions" :key="index"
                                              :label="item.value"
                                              :disabled="item.disabled">{{item.label}}
                                    </el-radio>
                                </el-radio-group>
                            </el-form-item>
                            <el-form-item label="函数加载" prop="LOAD_EXT_FILE">
                                <el-input v-model="formData.LOAD_EXT_FILE" placeholder="请输入函数加载" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">加载app/Common/目录下的扩展函数，扩展函数建议添加到extend.php。多个用逗号间隔。
                                    </template>
                                </el-input>
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
                            COOKIE_EXPIRE: "{$addition.COOKIE_EXPIRE}",
                            COOKIE_DOMAIN: "{$addition.COOKIE_DOMAIN}",
                            COOKIE_PATH: "{$addition.COOKIE_PATH}",
                            SESSION_PREFIX: "{$addition.SESSION_PREFIX}",
                            SESSION_OPTIONS: {
                                'domain': "{$addition.SESSION_OPTIONS.domain}",
                                'expire': "{$addition.SESSION_OPTIONS.expire}"
                            },
                            SHOW_ERROR_MSG: "{$addition.SHOW_ERROR_MSG}",
                            ERROR_MESSAGE: "{$addition.ERROR_MESSAGE}",
                            ERROR_PAGE: "{$addition.ERROR_PAGE}",
                            URL_CASE_INSENSITIVE: "{$addition.URL_CASE_INSENSITIVE}",
                            URL_MODEL: "{$addition.URL_MODEL}",
                            URL_PATHINFO_DEPR: "{$addition.URL_PATHINFO_DEPR}",
                            URL_HTML_SUFFIX: "{$addition.URL_HTML_SUFFIX}",
                            TOKEN_ON: "{$addition.TOKEN_ON}",
                            TOKEN_NAME: "{$addition.TOKEN_NAME}",
                            TOKEN_TYPE: "{$addition.TOKEN_TYPE}",
                            PAGE_LISTROWS: "{$addition.PAGE_LISTROWS}",
                            VAR_PAGE: "{$addition.VAR_PAGE}",
                            PAGE_TEMPLATE: '{$addition.PAGE_TEMPLATE}',
                            DEFAULT_MODULE: "{$addition.DEFAULT_MODULE}",
                            DEFAULT_TIMEZONE: "{$addition.DEFAULT_TIMEZONE}",
                            DEFAULT_AJAX_RETURN: "{$addition.DEFAULT_AJAX_RETURN}",
                            DEFAULT_FILTER: "{$addition.DEFAULT_FILTER}",
                            DEFAULT_LANG: "{$addition.DEFAULT_LANG}",
                            DATA_CACHE_TYPE: "{$addition.DATA_CACHE_TYPE}",
                            DATA_CACHE_SUBDIR: "{$addition.DATA_CACHE_SUBDIR}",
                            LOAD_EXT_FILE: "{$addition.LOAD_EXT_FILE}",
                        },
                        rules: {
                            COOKIE_EXPIRE: [{
                                required: true,
                                message: '请输入单行文本Cookie有效期',
                                trigger: 'blur'
                            }],
                            COOKIE_PATH: [{
                                required: true,
                                message: '请输入Cookie路径',
                                trigger: 'blur'
                            }],
                            SHOW_ERROR_MSG:
                                [{
                                    required: true,
                                    message: '显示错误信息不能为空',
                                    trigger: 'change'
                                }],
                            ERROR_MESSAGE:
                                [{
                                    required: true,
                                    message: '请输入错误显示信息',
                                    trigger: 'blur'
                                }],
                            URL_MODEL:
                                [{
                                    required: true,
                                    message: '请选择URL访问模式',
                                    trigger: 'change'
                                }],
                            URL_PATHINFO_DEPR:
                                [{
                                    required: true,
                                    message: '请输入PATHINFO模式参数分割线',
                                    trigger: 'blur'
                                }],
                            URL_HTML_SUFFIX:
                                [{
                                    required: true,
                                    message: '请输入URL伪静态后缀',
                                    trigger: 'blur'
                                }],
                            TOKEN_NAME:
                                [{
                                    required: true,
                                    message: '请输入表单隐藏字段名称',
                                    trigger: 'blur'
                                }],
                            TOKEN_TYPE:
                                [{
                                    required: true,
                                    message: '请输入令牌哈希验证规则',
                                    trigger: 'blur'
                                }],
                            PAGE_LISTROWS:
                                [{
                                    required: true,
                                    message: '请输入默认分页数',
                                    trigger: 'blur'
                                }],
                            VAR_PAGE:
                                [{
                                    required: true,
                                    message: '请输入分页变量',
                                    trigger: 'blur'
                                }],
                            PAGE_TEMPLATE:
                                [{
                                    required: true,
                                    message: '请输入默认分页模板',
                                    trigger: 'blur'
                                }],
                            DEFAULT_MODULE:
                                [{
                                    required: true,
                                    message: '请输入默认模块',
                                    trigger: 'blur'
                                }],
                            DEFAULT_TIMEZONE:
                                [{
                                    required: true,
                                    message: '请输入默认时区',
                                    trigger: 'blur'
                                }],
                            DEFAULT_AJAX_RETURN:
                                [{
                                    required: true,
                                    message: '请输入AJAX 数据返回格式',
                                    trigger: 'blur'
                                }],
                            DEFAULT_FILTER:
                                [{
                                    required: true,
                                    message: '请输入默认参数过滤方法',
                                    trigger: 'blur'
                                }],
                            DEFAULT_LANG:
                                [{
                                    required: true,
                                    message: '请输入默认语言',
                                    trigger: 'blur'
                                }],
                            DATA_CACHE_TYPE:
                                [{
                                    required: true,
                                    message: '请选择数据缓存类型',
                                    trigger: 'change'
                                }],
                            DATA_CACHE_SUBDIR:
                                [{
                                    required: true,
                                    message: '子目录缓存不能为空',
                                    trigger: 'change'
                                }],
                            LOAD_EXT_FILE:
                                [{
                                    required: true,
                                    message: '请输入函数加载',
                                    trigger: 'blur'
                                }],
                        },
                        SHOW_ERROR_MSGOptions: [{
                            "label": "开启",
                            "value": '1'
                        }, {
                            "label": "关闭",
                            "value": '0'
                        }],
                        URL_CASE_INSENSITIVEOptions:
                            [{
                                "label": "开启",
                                "value": '1'
                            }, {
                                "label": "关闭",
                                "value": '0'
                            }],
                        URL_MODELOptions:
                            [{
                                "label": "普通模式",
                                "value": "0"
                            }, {
                                "label": "PATHINFO 模式",
                                "value": "1"
                            }, {
                                "label": "REWRITE  模式",
                                "value": "2"
                            }, {
                                "label": "兼容模式",
                                "value": "3"
                            }],
                        TOKEN_ONOptions:
                            [{
                                "label": "开启",
                                "value": '1'
                            }, {
                                "label": "关闭",
                                "value": '0'
                            }],
                        DATA_CACHE_TYPEOptions:
                            [{
                                "label": "File",
                                "value": "File"
                            }, {
                                "label": "Redis",
                                "value": "Redis"
                            }, {
                                "label": "Memcache",
                                "value": "Memcache"
                            }, {
                                "label": "Xcache",
                                "value": "Xcache"
                            }],
                        DATA_CACHE_SUBDIROptions:
                            [{
                                "label": "是",
                                "value": '1'
                            }, {
                                "label": "否",
                                "value": '0'
                            }],
                    }
                },
                computed: {},
                watch: {},
                created() {
                },
                mounted() {

                },
                methods: {
                    submitForm() {
                        this.$refs['elForm'].validate(valid => {
                            if (!valid) return;
                            // TODO 提交表单
                            $.ajax({
                                url: "{:U('Config/addition')}",
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
