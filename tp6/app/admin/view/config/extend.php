<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-alert type="success" :closable="false">
            <p>1、请保证键名唯一、小写+下划线 </p>
            <p>2、可通过 AdminConfigService::getInstance()->getConfig('键名')['data'] 来获取配置值 </p>
        </el-alert>

        <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('admin', 'Config', 'editExtend')){ ?>
            <div style="margin-top: 4px">
                <el-button @click="extendAdd" type="primary" size="small">添加配置项</el-button>
            </div>
        <?php } ?>
        <el-col :sm="24" :md="18">
            <template v-if="configFieldList.length > 0 ">
                <div>
                    <el-form ref="elForm" :model="formData" size="medium" label-width="180px">
                        <template v-for="item in configFieldList">
                            <template v-if="item.type == 'input' ">
                                <el-form-item :label="item.setting.title">
                                    <el-input v-model="formData[item.fieldname]" :placeholder="item.setting.tips" clearable :style="{width: '100%'}">
                                    </el-input>
                                    <div>
                                        <el-button type="text" @click="toEdit(item)">编辑</el-button>
                                    </div>
                                </el-form-item>
                            </template>

                            <template v-if="item.type == 'textarea' ">
                                <el-form-item :label="item.setting.title">
                                    <el-input v-model="formData[item.fieldname]" :placeholder="item.setting.tips" type="textarea" row="3" clearable :style="{width: '100%'}">
                                    </el-input>
                                    <div>
                                        <el-button type="text" @click="toEdit(item)">编辑</el-button>
                                    </div>
                                </el-form-item>
                            </template>

                            <template v-if="item.type == 'select' ">
                                <el-form-item :label="item.setting.title">
                                    <el-select v-model="formData[item.fieldname]" :placeholder="item.setting.tips">
                                        <el-option
                                                v-for="item in item.setting.option"
                                                :key="item.value"
                                                :label="item.title"
                                                :value="item.value">
                                        </el-option>
                                    </el-select>
                                    <div>
                                        <el-button type="text" @click="toEdit(item)">编辑</el-button>
                                    </div>
                                </el-form-item>
                            </template>

                            <template v-if="item.type == 'radio' ">
                                <el-form-item :label="item.setting.title">
                                    <el-radio-group v-model="formData[item.fieldname]" size="small">
                                        <el-radio
                                                v-for="item in item.setting.option"
                                                :key="item.value"
                                                :label="item.value"
                                        >
                                            {{item.title}}
                                        </el-radio>
                                    </el-radio-group>
                                    <div>
                                        <el-button type="text" @click="toEdit(item)">编辑</el-button>
                                    </div>

                                </el-form-item>
                            </template>

                        </template>

                        <el-form-item size="large">
                            <el-button type="primary" @click="submitForm" size="small">保存</el-button>
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
                    formData: {},
                    configFieldList: [],
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
                        $.ajax({
                            url: "{:api_url('/admin/Config/extend')}",
                            method: 'post',
                            dataType: 'json',
                            data: that.formData,
                            success: function (res) {
                                layer.msg(res.msg)
                            }
                        });
                    })
                },
                // 获取详情
                getDetail: function() {
                    var that = this
                    var formData = {}
                    formData['_action'] = 'getDetail'
                    that.httpGet("{:api_url('/admin/Config/extend')}", formData, function(res){
                        that.formData = res.data.configMap
                        that.configFieldList = res.data.configFieldList
                    })
                },
                toEdit: function(item){
                    var that = this
                    var url = "{:api_url('/admin/Config/editExtend')}" + '?fid=' + item.fid
                    layer.open({
                        type: 2,
                        title: '编辑',
                        content: url,
                        area: ['670px', '550px'],
                        end: function(){
                            that.getDetail()
                        }
                    })
                },
                extendAdd: function () {
                    var that = this
                    var url = "{:api_url('/admin/Config/editExtend')}"
                    layer.open({
                        type: 2,
                        title: '添加配置项',
                        content: url,
                        area: ['670px', '550px'],
                        end: function(){
                            that.getDetail()
                        }
                    })
                }
            }
        });
    });
</script>

<style>

</style>
