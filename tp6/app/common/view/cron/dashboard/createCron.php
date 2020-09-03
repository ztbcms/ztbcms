<div id="app" v-cloak>
    <el-card>
        <el-form style="width: 800px" ref="form" label-width="80px">
            <el-form-item label="任务标题">
                <el-input v-model="form.subject"></el-input>
            </el-form-item>
            <el-form-item label="执行时间">
                <div>
                    <el-row :gutter="0">
                        <el-col :span="8">
                            <el-select v-model="form.loop_type" placeholder="请选择">
                                <el-option
                                        v-for="item in loop_type_options"
                                        :key="item.value"
                                        :label="item.text"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                        </el-col>
                        <template v-if="form.loop_type=='month'">
                            <el-col :span="8">
                                <el-select v-model="loop_data.month_day" placeholder="请选择">
                                    <el-option
                                            v-for="item in days_options"
                                            :key="item.value"
                                            :label="item.text"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-col>
                            <el-col :span="8">
                                <el-select v-model="loop_data.month_hour" placeholder="请选择">
                                    <el-option
                                            v-for="item in hours_options"
                                            :key="item.value"
                                            :label="item.text"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-col>
                        </template>
                        <template v-if="form.loop_type=='week'">
                            <el-col :span="8">
                                <el-select v-model="loop_data.week_day" placeholder="请选择">
                                    <el-option :key="1" label="周一" :value="1"></el-option>
                                    <el-option :key="2" label="周二" :value="2"></el-option>
                                    <el-option :key="3" label="周三" :value="3"></el-option>
                                    <el-option :key="4" label="周四" :value="4"></el-option>
                                    <el-option :key="5" label="周五" :value="5"></el-option>
                                    <el-option :key="6" label="周六" :value="6"></el-option>
                                    <el-option :key="0" label="周日" :value="0"></el-option>
                                </el-select>
                            </el-col>
                            <el-col :span="8">
                                <el-select v-model="loop_data.week_hour" placeholder="请选择">
                                    <el-option
                                            v-for="item in hours_options"
                                            :key="item.value"
                                            :label="item.text"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-col>
                        </template>
                        <template v-if="form.loop_type=='day'">
                            <el-col :span="8">
                                <el-select v-model="loop_data.day_hour" placeholder="请选择">
                                    <el-option
                                            v-for="item in hours_options"
                                            :key="item.value"
                                            :label="item.text"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </el-col>
                        </template>
                        <template v-if="form.loop_type=='hour'">
                            <el-col :span="8">
                                <el-select v-model="loop_data.hour_minute" placeholder="请选择">
                                    <el-option :key="0" label="00分" :value="0"></el-option>
                                    <el-option :key="10" label="10分" :value="10"></el-option>
                                    <el-option :key="20" label="20分" :value="20"></el-option>
                                    <el-option :key="30" label="30分" :value="30"></el-option>
                                    <el-option :key="40" label="40分" :value="40"></el-option>
                                    <el-option :key="50" label="50分" :value="50"></el-option>
                                </el-select>
                            </el-col>
                        </template>
                        <template v-if="form.loop_type=='now'">
                            <el-col :span="8">
                                <el-input style="width: 200px;" v-model="form.now_time" type="number"></el-input>
                            </el-col>
                            <el-col :span="8">
                                <el-select v-model="loop_data.now_type" placeholder="请选择">
                                    <el-option :key="minute" label="分钟" value="minute"></el-option>
                                    <el-option :key="hour" label="小时" value="hour"></el-option>
                                    <el-option :key="day" label="天" value="day"></el-option>
                                </el-select>
                            </el-col>
                        </template>
                    </el-row>
                </div>
            </el-form-item>
            <el-form-item label="开启计划">
                <template>
                    <el-radio v-model="form.isopen" :label="1">开启</el-radio>
                    <el-radio v-model="form.isopen" :label="0">关闭</el-radio>
                </template>
            </el-form-item>
            <el-form-item label="任务类型">
                <el-select v-model="form.type" placeholder="请选择">
                    <el-option
                            :key="0"
                            label="普通计划任务"
                            :value="0">
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="执行文件">
                <el-select v-model="form.cron_file" placeholder="请选择">
                    <?php foreach ($cronFileList as $value): ?>
                        <el-option
                                key="{$value}"
                                label="{$value}"
                                value="{$value}">
                        </el-option>
                    <?php endforeach; ?>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-button @click="submit" type="primary">保存</el-button>
            </el-form-item>
        </el-form>
    </el-card>
</div>
<script>
    $(function () {
        new Vue({
            el: "#app",
            computed: {
                days_options: function () {
                    var days_options = [];
                    for (var i = 1; i <= 32; i++) {
                        days_options.push({
                            value: i < 32 ? i : 99,
                            text: i < 32 ? (i + "日") : "最后一天"
                        });
                    }
                    return days_options;
                },
                hours_options: function () {
                    var hours_options = [];
                    for (var i = 0; i < 24; i++) {
                        hours_options.push({
                            value: i,
                            text: i + "时"
                        });
                    }
                    return hours_options;
                }
            },
            data: {
                loop_type_options: [
                    {
                        value: "month",
                        text: "每月"
                    },
                    {
                        value: "week",
                        text: "每周"
                    },
                    {
                        value: "day",
                        text: "每日"
                    }, {
                        value: "hour",
                        text: "每小时"
                    },
                    {
                        value: "now",
                        text: "每隔"
                    }
                ],
                loop_data: {
                    month_day: 1,
                    month_hour: 0,
                    week_day: 1,
                    week_hour: 0,
                    day_hour: 0,
                    hour_minute: 0,
                    now_time: 1,
                    now_type: "minute"
                },
                form: {
                    loop_type: "month",
                    isopen: 1,
                    type: 0
                },
            },
            mounted: function () {
            },
            methods: {
                submit: function () {
                    console.log('form', this.form);
                    console.log('loop_data', this.loop_data);
                    if (!this.form.subject) {
                        layer.msg('任务标题不能为空');
                        return
                    }
                    if (!this.form.cron_file) {
                        layer.msg('执行文件不能为空');
                        return
                    }
                    $.ajax({
                        url: "{:urlx('common/cron.dashboard/createCron')}",
                        data: {form: this.form, loop_data: this.loop_data},
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            // var data = res.data;
                        }
                    })
                }
            }
        });
    })
</script>

