<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="80px">
                <el-form-item label="键名" prop="fieldname">
                    <el-input v-model="formData.fieldname" placeholder="请输入键名" clearable :style="{width: '100%'}">
                    </el-input>
                </el-form-item>
                <el-form-item label="名称" prop="setting.title">
                    <el-input v-model="formData['setting']['title']" placeholder="请输入名称" clearable :style="{width: '100%'}">
                    </el-input>
                </el-form-item>
                <el-form-item label="类型" prop="type">
                    <el-select v-model="formData.type" placeholder="请选择类型" clearable
                               :style="{width: '100%'}">
                        <el-option v-for="(item, index) in typeOptions" :key="index" :label="item.label"
                                   :value="item.value"
                                   :disabled="item.disabled"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="提示" prop="setting.tips">
                    <el-input v-model="formData['setting']['tips']" placeholder="请输入提示" clearable :style="{width: '100%'}">
                    </el-input>
                </el-form-item>

                <template v-if="formData.type === 'select' || formData.type === 'radio'">
                    <el-form-item label="选项" prop="setting.option">
                        <el-input v-model="formData['setting']['option']" type="textarea" placeholder="请输入每行一个选项"
                                  :autosize="{minRows: 4, maxRows: 4}" :style="{width: '100%'}"></el-input>
                        <small style="color: #858689;"><span style="color: red">*</span>每行一个选项，示例：选项名称|选项值</small>
                    </el-form-item>
                </template>

                <el-form-item size="large">
                    <el-button type="primary" @click="submitForm" size="small">保存</el-button>
                    <el-button v-if="formData && formData.fid" type="danger" @click="deleteItem">删除</el-button>
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
                    formData: {
                        fid: '{$configField ? $configField["fid"] : ""}',
                        fieldname: '{$configField ? $configField["fieldname"] : ""}',
                        type: '{$configField ? $configField["type"] : "input"}',
                        setting: {
                            title: '{$configField ? $configField["setting"]["title"] : ""}',
                            tips: '{$configField ? $configField["setting"]["tips"] : ""}',
                            option: '{$configField ? $configField["setting"]["option"] : "选项名称|选项值"}'
                        }
                    },
                    configFieldJson: '',
                    rules: {
                        fieldname: [{
                            required: true,
                            message: '请输入键名',
                            trigger: 'blur'
                        }],
                        type: [{
                            required: true,
                            message: '请输入键名',
                            trigger: 'blur'
                        }],
                        'setting.title': [{
                            required: true,
                            message: '请输入名称',
                            trigger: 'blur'
                        }],
                    },
                    typeOptions: [{
                        "label": "单行文本框",
                        "value": "input"
                    }, {
                        "label": "多行文本框",
                        "value": "textarea"
                    }, {
                        "label": "下拉框",
                        "value": "select"
                    }, {
                        "label": "单选框",
                        "value": "radio"
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
                    this.$refs['elForm'].validate(function(valid){
                        if (!valid) return;
                        //  提交表单
                        that.httpPost("{:api_url('/admin/Config/editExtend')}", that.formData, function (res) {
                            layer.msg(res.msg)
                            if(res.status){
                                setTimeout(function(){
                                    window.parent.layer.closeAll()
                                }, 500)
                            }
                        })
                    })
                },
                deleteItem: function() {
                   layer.confirm('确认删除?', this.doDeleteItem.bind(this))
                },
                doDeleteItem: function() {
                    var that = this
                    var formData = {fid: this.formData.fid}
                    that.httpPost("{:api_url('/admin/Config/doDeleteExtendField')}", formData, function (res) {
                        layer.msg(res.msg)
                        if(window.parent !== window){
                            setTimeout(function(){
                                window.parent.layer.closeAll()
                            }, 500)
                        }
                    })
                }
            }
        });
    });
</script>

<style>

</style>
