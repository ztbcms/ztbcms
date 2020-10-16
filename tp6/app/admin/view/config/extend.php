<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">

            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="180px">
                        <template v-for="item in configFieldList">
                            <template v-if="item.type == 'input' ">
                                <el-form-item :label="item.setting.title" >
                                    <el-input v-model="formData[item.fieldname]" :placeholder="item.tips" clearable :style="{width: '100%'}">
                                        <template slot="append">只允许英文、数组、下划线</template>
                                    </el-input>
                                    <small style="color: #858689;">{{ item.tips }}</small>
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
            data() {
                return {
                    formData: {},
                    configFieldListJson: '{:json_encode($configFieldList)}',
                    configFieldList: [],
                    configMapJson: '{:json_encode($configMap)}',
                }
            },
            computed: {},
            watch: {},
            created() {
            },
            mounted() {
                console.log(this.configFieldListJson)
                this.configFieldList = JSON.parse(this.configFieldListJson)
                this.formData = JSON.parse(this.configMapJson)
                // for(var i=0;i<this.configFieldList;i++){
                //     var item = this.configFieldList[i];
                //     this.formData[item.fieldname] = ''
                // }
            },
            methods: {
                submitForm() {
                    this.$refs['elForm'].validate(valid => {
                        if (!valid) return
                        $.ajax({
                            url: "{:api_url('/admin/Config/email')}",
                            method: 'post',
                            dataType: 'json',
                            data: this.formData,
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
