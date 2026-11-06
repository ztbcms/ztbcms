<div id="app" v-cloak style="padding: 12px;">
    <el-card v-loading="loading" shadow="never">
        <div v-if="item">
            <el-descriptions title="基本信息" :column="2" border size="small">
                <el-descriptions-item label="记录 ID">{{ item.id }}</el-descriptions-item>
                <el-descriptions-item label="状态">
                    <el-tag :type="statusTagType(item.status)" size="small">{{ item.status_text }}</el-tag>
                </el-descriptions-item>
                <el-descriptions-item label="队列连接 / 队列">{{ item.connection }} / {{ item.queue }}</el-descriptions-item>
                <el-descriptions-item label="尝试次数">第 {{ item.attempt }} 次 (queue_job_id: {{ item.queue_job_id }})</el-descriptions-item>
                <el-descriptions-item label="任务类" :span="2">{{ item.job_name }}</el-descriptions-item>
                <el-descriptions-item label="链路 UUID" :span="2">{{ item.monitor_uuid || '-' }}</el-descriptions-item>
                <el-descriptions-item label="首次入队时间">{{ formatTimeMs(item.pushed_at) }}</el-descriptions-item>
                <el-descriptions-item label="排队等待时长">{{ formatDuration(item.wait_ms) }}</el-descriptions-item>
                <el-descriptions-item label="开始执行时间">{{ formatTimeMs(item.started_at) }}</el-descriptions-item>
                <el-descriptions-item label="结束执行时间">{{ formatTimeMs(item.finished_at) }}</el-descriptions-item>
                <el-descriptions-item label="执行耗时">{{ (item.status === 'running' || item.status === 'lost') ? '-' : formatDuration(item.duration_ms) }}</el-descriptions-item>
                <el-descriptions-item label="进程内存峰值">{{ formatMemory(item.memory_peak) }}</el-descriptions-item>
                <el-descriptions-item label="Worker 主机 / PID" :span="2">{{ item.hostname }} (PID: {{ item.pid }})</el-descriptions-item>
            </el-descriptions>

            <div v-if="chain && chain.length > 1" style="margin-top: 18px;">
                <div class="sec-title">同一任务执行链路（共 {{ chain.length }} 次）</div>
                <el-table :data="chain" size="mini" border style="width: 100%;">
                    <el-table-column prop="attempt" label="第N次" width="70" align="center"></el-table-column>
                    <el-table-column prop="id" label="记录ID" width="85" align="center">
                        <template slot-scope="scope">
                            <strong v-if="Number(scope.row.id) === Number(item.id)">#{{ scope.row.id }} (当前)</strong>
                            <el-button v-else type="text" size="mini" @click="switchRecord(scope.row.id)">#{{ scope.row.id }}</el-button>
                        </template>
                    </el-table-column>
                    <el-table-column prop="queue_job_id" label="任务行ID" width="90" align="center"></el-table-column>
                    <el-table-column label="状态" width="95" align="center">
                        <template slot-scope="scope">
                            <el-tag :type="statusTagType(scope.row.status)" size="mini">{{ scope.row.status_text }}</el-tag>
                        </template>
                    </el-table-column>
                    <el-table-column label="开始时间" width="165" align="center">
                        <template slot-scope="scope">{{ formatTimeMs(scope.row.started_at) }}</template>
                    </el-table-column>
                    <el-table-column label="耗时" width="95" align="center">
                        <template slot-scope="scope">{{ (scope.row.status === 'running' || scope.row.status === 'lost') ? '-' : formatDuration(scope.row.duration_ms) }}</template>
                    </el-table-column>
                    <el-table-column label="主机/PID" min-width="130">
                        <template slot-scope="scope">{{ scope.row.hostname }}:{{ scope.row.pid }}</template>
                    </el-table-column>
                    <el-table-column prop="exception_class" label="异常类" min-width="140" show-overflow-tooltip></el-table-column>
                </el-table>
            </div>

            <div v-if="item.exception_class || item.exception_message" style="margin-top: 18px;">
                <div class="sec-title">异常信息 {{ item.exception_class ? '(' + item.exception_class + ')' : '' }}</div>
                <pre class="code-box">{{ item.exception_message }}</pre>
            </div>

            <div style="margin-top: 18px;">
                <div class="sec-title">任务 Payload</div>
                <pre class="code-box">{{ formatPayload(item.payload) }}</pre>
            </div>
        </div>
        <div v-else-if="!loading" style="text-align: center; color: #909399; padding: 32px 0;">
            记录不存在
        </div>
    </el-card>
</div>

<style>
    .sec-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #303133;
    }
    .code-box {
        background: #F5F7FA;
        border: 1px solid #E4E7ED;
        border-radius: 4px;
        padding: 10px 12px;
        font-size: 12px;
        line-height: 1.5;
        max-height: 320px;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-all;
        margin: 0;
    }
</style>

<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                id: <?php echo intval($id); ?>,
                loading: false,
                item: null,
                chain: []
            },
            mounted: function () {
                this.loadDetail();
            },
            methods: {
                switchRecord: function (id) {
                    this.id = id;
                    this.loadDetail();
                },
                loadDetail: function () {
                    var _this = this;
                    this.loading = true;
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    $.ajax({
                        url: "{:api_url('/common/queue.dashboard/monitorDetail')}",
                        data: { _action: "getDetail", id: this.id },
                        dataType: "json",
                        type: "get",
                        success: function (res) {
                            _this.loading = false;
                            if (res.status && res.data) {
                                _this.item = res.data.item;
                                _this.chain = res.data.chain || [];
                            } else {
                                _this.$message.error(res.msg || "加载详情失败");
                            }
                        },
                        error: function () {
                            _this.loading = false;
                            _this.$message.error("请求详情失败");
                        }
                    });
                },
                formatPayload: function (raw) {
                    if (!raw) return "";
                    try {
                        return JSON.stringify(JSON.parse(raw), null, 2);
                    } catch (e) {
                        return raw;
                    }
                },
                statusTagType: function (status) {
                    var map = {
                        running: "primary",
                        succeeded: "success",
                        released: "warning",
                        unacked: "warning",
                        failed: "danger",
                        exception: "danger",
                        timeout: "danger",
                        interrupted: "danger",
                        lost: "info"
                    };
                    return map[status] || "info";
                },
                formatDuration: function (ms) {
                    var n = Number(ms || 0);
                    if (n < 1000) return n + " ms";
                    return (n / 1000).toFixed(2) + " s";
                },
                formatMemory: function (bytes) {
                    var n = Number(bytes || 0);
                    if (n <= 0) return "-";
                    return (n / 1024 / 1024).toFixed(2) + " MB";
                },
                formatTimeMs: function (ms) {
                    if (!ms || Number(ms) <= 0) return "-";
                    var d = new Date(Number(ms));
                    var pad = function (n) { return n < 10 ? "0" + n : "" + n; };
                    return d.getFullYear() + "-" + pad(d.getMonth() + 1) + "-" + pad(d.getDate()) + " " + pad(d.getHours()) + ":" + pad(d.getMinutes()) + ":" + pad(d.getSeconds()) + "." + (d.getMilliseconds() + 1000).toString().slice(1);
                }
            }
        });
    });
</script>
