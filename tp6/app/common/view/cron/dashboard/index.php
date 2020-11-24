<div id="app" v-cloak>
    <el-card>
        <el-form style="width: 800px" ref="form" label-width="140px">

            <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('common', 'cron.dashboard', 'setCronEnable')){ ?>
                <el-form-item label="计划任务启用状态">
                    <template v-if="cron_config.enable_cron == 1">
                        <span style="color: green">启用中 </span>
                        <el-button type="danger" @click="toSetCronEnable(0)">停用</el-button>
                    </template>
                    <template v-else>
                        <span style="color: red">停用中 </span>
                        <el-button type="success" @click="toSetCronEnable(1)">启用</el-button>
                    </template>
                    <p><span style="color: red;">*</span> 停用计划任务是平滑进行。需要等待该轮的任务调度执行完成后，再完全停止。</p>
                </el-form-item>
            <?php } ?>


            <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('common', 'cron.dashboard', 'setCronSecretKey')){ ?>
                <el-form-item label="密钥">
                    <el-input v-model="cron_config.secret_key">
                        <el-button slot="append" @click="toSetSecretKey">更新</el-button>
                    </el-input>
                </el-form-item>
            <?php } ?>

            <el-form-item label="计划任务HTTP入口">
                <a :href="cron_entry_url" target="_blank">{{cron_entry_url}}</a>
                <p>* 接入方式请参考<a href="http://ztbcms.com/module/cron/" target="_blank">计划任务文档</a></p>
            </el-form-item>
            <el-form-item label="正在执行数量">
                <strong>{{cron_status.current_exec_amount}}</strong>
            </el-form-item>
            <el-form-item label="当前执行任务">
                <template v-for="cron in cron_status.current_exec_cron">
                    <strong>{{cron.subject}}({{cron.cron_file}})</strong>
                </template>
                <template v-if="cron_status.current_exec_cron.length == 0">
                    <strong>暂无</strong>
                </template>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" @click="getStatus">获取最新状态</el-button>
            </el-form-item>
        </el-form>
    </el-card>
</div>
<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                form: {},
                items: [],
                cron_config: {
                    enable_cron: 0,
                    secret_key: ''
                },
                cron_status: {
                    current_exec_amount: 0,
                    current_exec_cron: [],
                },
                cron_entry_url: '',
            },
            mounted: function () {
                this.getStatus();
            },
            methods: {
                getStatus: function () {
                    var that = this;
                    var data = {};
                    $.ajax({
                        url: "{:api_url('/common/cron.dashboard/getCronStatus')}",
                        data: data,
                        dataType: 'json',
                        type: 'get',
                        success: function (res) {
                            var data = res.data;
                            that.cron_config = data.cron_config;
                            that.cron_status = data.cron_status;
                            that.cron_entry_url = data.cron_entry_url
                        }
                    })
                },
                toSetCronEnable: function (value) {
                    var that = this;
                    layer.confirm('修改密钥将会影响计划任务运行，请在用户流量少的情况下进行操作。确认要操作？',{title: '提示'}, function(){
                        that.setCronEnable(value);
                    }, function(){

                    })
                },
                setCronEnable: function (value) {
                    var that = this;
                    var data = {
                        enable: value
                    };
                    $.ajax({
                        url: "{:api_url('/common/cron.dashboard/setCronEnable')}",
                        data: data,
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            layer.msg(res.msg);
                            setTimeout(function () {
                                that.getStatus()
                            }, 1000)
                        }
                    })
                },
                toSetSecretKey: function () {
                    var that = this;
                    layer.confirm('修改密钥将会影响计划任务运行，请在用户流量少的情况下进行操作。确认要操作？',{title: '提示'}, function(){
                        that.setSecretKey()
                    }, function(){

                    })
                },
                setSecretKey: function () {
                    var that = this;
                    var data = {
                        secret_key: that.cron_config.secret_key
                    };
                    $.ajax({
                        url: "{:api_url('/common/cron.dashboard/setCronSecretKey')}",
                        data: data,
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            layer.msg(res.msg);
                            setTimeout(function () {
                                that.getStatus()
                            }, 1000)
                        }
                    })
                },
                onSubmit: function () {

                }
            }
        });
    })
</script>

<style>
    a {
        color: #337ab7;
        text-decoration: none;
    }
</style>