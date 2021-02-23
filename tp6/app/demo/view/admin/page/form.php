<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <h3>表单</h3>
        <el-row>
            <el-col :span="8">
                <div class="grid-content ">
                    <el-form ref="form" :model="form" label-width="80px">
                        <el-form-item label="活动名称">
                            <el-input v-model="form.name"></el-input>
                        </el-form-item>
                        <el-form-item label="活动区域">
                            <el-select v-model="form.region" placeholder="请选择活动区域">
                                <el-option label="区域一" value="shanghai"></el-option>
                                <el-option label="区域二" value="beijing"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="开始时间">
                            <el-col :span="11">
                                <el-date-picker type="date" format="yyyy-MM-dd" value-format="yyyy-MM-dd"
                                                placeholder="选择日期" v-model="form.start_date"
                                                style="width: 100%;"></el-date-picker>
                            </el-col>
                            <el-col :span="2" style="text-align: center;">-</el-col>
                            <el-col :span="11">
                                <el-time-picker placeholder="选择时间" format="HH:mm:ss" value-format="HH:mm:ss"
                                                v-model="form.start_time" style="width: 100%;"></el-time-picker>
                            </el-col>
                        </el-form-item>
                        <el-form-item label="结束时间">
                            <el-col :span="11">
                                <el-date-picker type="date" format="yyyy-MM-dd" value-format="yyyy-MM-dd"
                                                placeholder="选择日期" v-model="form.end_date"
                                                style="width: 100%;"></el-date-picker>
                            </el-col>
                            <el-col :span="2" style="text-align: center;">-</el-col>
                            <el-col :span="11">
                                <el-time-picker placeholder="选择时间" format="HH:mm:ss" value-format="HH:mm:ss"
                                                v-model="form.end_time" style="width: 100%;"></el-time-picker>
                            </el-col>
                        </el-form-item>
                        <el-form-item label="即时配送">
                            <el-switch v-model="form.delivery" active-value="1" inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item label="活动性质">
                            <el-checkbox-group v-model="form.type">
                                <el-checkbox label="美食/餐厅线上活动" name="type"></el-checkbox>
                                <el-checkbox label="地推活动" name="type"></el-checkbox>
                                <el-checkbox label="线下主题活动" name="type"></el-checkbox>
                                <el-checkbox label="单纯品牌曝光" name="type"></el-checkbox>
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item label="功能开关">
                            <el-radio-group v-model="form.enable">
                                <el-radio label="1">开启</el-radio>
                                <el-radio label="0">关闭</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="活动形式">
                            <el-input type="textarea" v-model="form.desc" rows="3"></el-input>
                        </el-form-item>
                        <el-form-item>
                            <el-button type="primary" @click="onSubmit">立即创建</el-button>
                            <el-button @click="onCancel">取消</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>
            <el-col :span="16">
                <div class="grid-content "></div>
            </el-col>
        </el-row>


    </el-card>
</div>

<style>

</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                form: {
                    name: '',
                    region: '',
                    start_date: '',
                    start_time: '',
                    end_date: '',
                    end_time: '',
                    delivery: 0,
                    type: [],
                    enable: '1',
                    desc: ''
                }
            },
            watch: {},
            filters: {},
            methods: {
                onSubmit: function () {
                    console.log(this.form)
                    this.$message.success('提交成功');
                },
                onCancel: function () {
                    this.$message.error('已取消');
                },
            },
            mounted: function () {

            },

        })
    })
</script>
