<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">

            <template>
                <div>
                    <el-form ref="elForm" :model="formData" size="medium" label-width="180px">
                        <template v-for="item in configFieldList">
                            <template v-if="item.type == 'input' ">
                                <el-form-item :label="item.setting.title">
                                    <el-input v-model="formData[item.fieldname]" :placeholder="item.setting.tips" clearable :style="{width: '100%'}">
                                    </el-input>
                                </el-form-item>
                            </template>

                            <template v-if="item.type == 'textarea' ">
                                <el-form-item :label="item.setting.title">
                                    <el-input v-model="formData[item.fieldname]" :placeholder="item.setting.tips" type="textarea" row="3" clearable :style="{width: '100%'}">
                                    </el-input>
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
                                </el-form-item>
                            </template>


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
        new Vue({
            el: '#app',
            components: {},
            props: [],
            data: function() {
                return {
                    formData: {},
                    configFieldListJson: '{:json_encode($configFieldList)}',
                    configFieldList: [],
                    configMapJson: '{:json_encode($configMap)}',
                }
            },
            computed: {},
            watch: {},
            created: function() {
            },
            mounted: function() {
                this.configFieldList = JSON.parse(this.configFieldListJson)
                this.formData = JSON.parse(this.configMapJson)
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
                }
            }
        });
    });
</script>

<style>

</style>
