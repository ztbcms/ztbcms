<div id="app" v-cloak>
    <el-card style="margin-bottom: 16px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <span style="font-size: 16px; font-weight: 600;">队列监控 · 概况</span>
                <el-tag size="small" :type="realtime.monitor_enable ? 'success' : 'info'" style="margin-left: 8px;">
                    {{ realtime.monitor_enable ? '监控记录开启' : '监控记录已关闭' }}
                </el-tag>
                <el-tag v-if="realtime.connection" size="small" type="primary" style="margin-left: 6px;">
                    连接: {{ realtime.connection }} (retry_after: {{ realtime.retry_after }}s)
                </el-tag>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <el-radio-group v-model="range" size="small" @change="loadStats">
                    <el-radio-button v-for="item in ranges" :key="item.value" :label="item.value">{{ item.label }}</el-radio-button>
                </el-radio-group>
                <el-checkbox v-model="autoRefresh" @change="toggleAutoRefresh">自动刷新(10s)</el-checkbox>
                <el-button size="small" type="primary" icon="el-icon-refresh" :loading="loading" @click="refreshAll">刷新</el-button>
                <el-button size="small" @click="openMonitorLog('', '', '')">执行记录</el-button>
                <el-button size="small" type="danger" plain @click="openFailedJobs('')">失败任务 ({{ summary.failed_jobs || 0 }})</el-button>
            </div>
        </div>
        <el-alert v-if="realtimeError" :title="realtimeError" type="warning" show-icon :closable="false" style="margin-top: 12px;"></el-alert>
    </el-card>

    <el-row :gutter="12" style="margin-bottom: 16px;">
        <el-col :span="4">
            <el-card shadow="never" class="stat-card">
                <div class="stat-label">区间执行次数</div>
                <div class="stat-value">{{ summary.total || 0 }}</div>
                <div class="stat-sub">执行中: {{ summary.running || 0 }}</div>
            </el-card>
        </el-col>
        <el-col :span="5">
            <el-card shadow="never" class="stat-card">
                <div class="stat-label">成功率 / 失败率</div>
                <div class="stat-value">
                    <span style="color: #67C23A;">{{ formatPercent(summary.success_rate) }}</span>
                    <span style="font-size: 14px; color: #909399;"> / </span>
                    <span style="color: #F56C6C; font-size: 18px;">{{ formatPercent(summary.error_rate) }}</span>
                </div>
                <div class="stat-sub">成功 {{ summary.succeeded || 0 }} / 异常或失败 {{ summary.errors || 0 }}</div>
            </el-card>
        </el-col>
        <el-col :span="5">
            <el-card shadow="never" class="stat-card">
                <div class="stat-label">平均耗时 / 最大耗时</div>
                <div class="stat-value">{{ formatDuration(summary.avg_duration) }}</div>
                <div class="stat-sub">最大: {{ formatDuration(summary.max_duration) }}</div>
            </el-card>
        </el-col>
        <el-col :span="5">
            <el-card shadow="never" class="stat-card">
                <div class="stat-label">P95 耗时 / 平均排队</div>
                <div class="stat-value">{{ formatDuration(summary.p95_duration) }}</div>
                <div class="stat-sub">平均排队: {{ formatDuration(summary.avg_wait) }}</div>
            </el-card>
        </el-col>
        <el-col :span="5">
            <el-card shadow="never" class="stat-card" style="cursor: pointer;" @click.native="openFailedJobs('')">
                <div class="stat-label">当前失败任务表</div>
                <div class="stat-value" :style="{ color: (summary.failed_jobs || 0) > 0 ? '#F56C6C' : '#303133' }">
                    {{ summary.failed_jobs || 0 }}
                </div>
                <div class="stat-sub">点击管理失败任务</div>
            </el-card>
        </el-col>
    </el-row>

    <el-card style="margin-bottom: 16px;">
        <div slot="header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>队列实时状态（database: queue_jobs）</span>
            <span style="font-size: 12px; color: #909399;">疑似卡死指 reserve_time 超过 retry_after({{ realtime.retry_after || 60 }}s) 仍未完成</span>
        </div>
        <el-table :data="realtime.queues" v-loading="loadingRealtime" empty-text="当前无排队任务及近期活跃队列" size="small" style="width: 100%;">
            <el-table-column prop="queue" label="队列名称" min-width="200">
                <template slot-scope="scope">
                    <el-button type="text" @click="openMonitorLog(scope.row.queue, '', '')">{{ scope.row.queue }}</el-button>
                </template>
            </el-table-column>
            <el-table-column prop="pending" label="待执行" align="center" min-width="100">
                <template slot-scope="scope">
                    <el-tag :type="scope.row.pending > 0 ? 'warning' : 'info'" size="small">{{ scope.row.pending }}</el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="delayed" label="延迟中" align="center" min-width="100"></el-table-column>
            <el-table-column prop="reserved" label="执行中" align="center" min-width="100">
                <template slot-scope="scope">
                    <el-tag :type="scope.row.reserved > 0 ? 'primary' : 'info'" size="small">{{ scope.row.reserved }}</el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="stale" label="疑似卡死" align="center" min-width="110">
                <template slot-scope="scope">
                    <el-tag v-if="scope.row.stale > 0" type="danger" size="small">{{ scope.row.stale }} ⚠</el-tag>
                    <span v-else>0</span>
                </template>
            </el-table-column>
            <el-table-column prop="max_wait_seconds" label="最长等待" align="center" min-width="120">
                <template slot-scope="scope">
                    <span :style="{ color: scope.row.max_wait_seconds >= 300 ? '#F56C6C' : '#606266' }">
                        {{ scope.row.max_wait_seconds > 0 ? scope.row.max_wait_seconds + 's' : '-' }}
                    </span>
                </template>
            </el-table-column>
            <el-table-column prop="failed" label="失败任务数" align="center" min-width="110">
                <template slot-scope="scope">
                    <el-button v-if="scope.row.failed > 0" type="text" style="color: #F56C6C;" @click="openFailedJobs(scope.row.queue)">
                        {{ scope.row.failed }}
                    </el-button>
                    <span v-else>0</span>
                </template>
            </el-table-column>
            <el-table-column label="操作" align="center" width="120">
                <template slot-scope="scope">
                    <el-button type="text" size="small" @click="openMonitorLog(scope.row.queue, '', '')">查看记录</el-button>
                </template>
            </el-table-column>
        </el-table>
    </el-card>

    <el-card style="margin-bottom: 16px;" v-loading="loadingStats">
        <div slot="header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>吞吐量趋势 <span style="font-size: 12px; color: #909399; font-weight: normal;">（Y 轴：执行任务数，单位：{{ trendUnitLabel }}）</span></span>
            <div style="font-size: 12px; color: #606266;">
                <span class="legend-dot" style="background: #67C23A;"></span>成功
                <span class="legend-dot" style="background: #E6A23C; margin-left: 10px;"></span>重新入队
                <span class="legend-dot" style="background: #F56C6C; margin-left: 10px;"></span>失败/异常
            </div>
        </div>
        <div v-if="trend.points && trend.points.length" class="trend-chart">
            <div class="trend-y-axis">
                <span>{{ yAxisTop }}</span>
                <span>{{ yAxisMid }}</span>
                <span>0</span>
            </div>
            <div class="trend-bars">
                <div class="trend-grid-line" style="bottom: 132px;"></div>
                <div class="trend-grid-line" style="bottom: 77px;"></div>
                <div v-for="(pt, idx) in trend.points" :key="idx" class="trend-bar-col" :title="formatPointTooltip(pt)">
                    <div class="trend-bar-stack" :style="{ height: barHeight(pt.total) + 'px' }">
                        <div v-if="pt.errors > 0" class="bar-seg bar-error" :style="{ flex: pt.errors }"></div>
                        <div v-if="pt.released > 0" class="bar-seg bar-released" :style="{ flex: pt.released }"></div>
                        <div v-if="pt.succeeded > 0" class="bar-seg bar-success" :style="{ flex: pt.succeeded }"></div>
                        <div v-if="pt.other > 0" class="bar-seg bar-other" :style="{ flex: pt.other }"></div>
                    </div>
                    <div class="trend-bar-label" v-if="shouldShowLabel(idx, trend.points.length)">{{ formatBucketLabel(pt.time, trend.bucket_seconds) }}</div>
                </div>
            </div>
        </div>
        <div v-else style="text-align: center; color: #909399; padding: 24px 0;">暂无趋势数据</div>
    </el-card>

    <el-card style="margin-bottom: 16px;">
        <div slot="header">
            <span>任务类性能统计</span>
        </div>
        <el-table :data="jobs" v-loading="loadingStats" empty-text="统计区间内暂无执行记录" size="small" style="width: 100%;">
            <el-table-column prop="job_name" label="任务类" min-width="260" show-overflow-tooltip></el-table-column>
            <el-table-column prop="queue" label="队列" min-width="140"></el-table-column>
            <el-table-column prop="total" label="执行次数" align="center" width="95"></el-table-column>
            <el-table-column label="成功率" align="center" width="95">
                <template slot-scope="scope">{{ formatPercent(scope.row.success_rate) }}</template>
            </el-table-column>
            <el-table-column prop="errors" label="失败/异常" align="center" width="95">
                <template slot-scope="scope">
                    <span :style="{ color: scope.row.errors > 0 ? '#F56C6C' : '#606266' }">{{ scope.row.errors }}</span>
                </template>
            </el-table-column>
            <el-table-column label="平均耗时" align="center" width="110">
                <template slot-scope="scope">{{ formatDuration(scope.row.avg_duration) }}</template>
            </el-table-column>
            <el-table-column label="P95 耗时" align="center" width="110">
                <template slot-scope="scope">{{ formatDuration(scope.row.p95_duration) }}</template>
            </el-table-column>
            <el-table-column label="最大耗时" align="center" width="110">
                <template slot-scope="scope">{{ formatDuration(scope.row.max_duration) }}</template>
            </el-table-column>
            <el-table-column label="平均排队" align="center" width="110">
                <template slot-scope="scope">{{ formatDuration(scope.row.avg_wait) }}</template>
            </el-table-column>
            <el-table-column label="操作" align="center" width="90">
                <template slot-scope="scope">
                    <el-button type="text" size="small" @click="openMonitorLog(scope.row.queue, scope.row.job_name, '')">记录</el-button>
                </template>
            </el-table-column>
        </el-table>
    </el-card>

    <el-card>
        <div slot="header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>活跃 Worker（近 {{ Math.round(workerWindow / 60) }} 分钟有执行记录）</span>
            <span style="font-size: 12px; color: #909399;">根据执行记录推断，空闲未处理任务的 Worker 不显示</span>
        </div>
        <el-table :data="workers" v-loading="loadingWorkers" empty-text="近期无活跃 Worker 执行记录" size="small" style="width: 100%;">
            <el-table-column prop="hostname" label="主机名" min-width="160"></el-table-column>
            <el-table-column prop="pid" label="进程 PID" width="110" align="center"></el-table-column>
            <el-table-column label="状态" width="100" align="center">
                <template slot-scope="scope">
                    <el-tag :type="scope.row.is_busy ? 'primary' : 'success'" size="small">
                        {{ scope.row.is_busy ? '执行中' : '空闲/待命' }}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="last_queue" label="最近队列" min-width="140"></el-table-column>
            <el-table-column prop="last_job_name" label="最近任务" min-width="240" show-overflow-tooltip></el-table-column>
            <el-table-column label="最近活动时间" width="170" align="center">
                <template slot-scope="scope">{{ formatTimeMs(scope.row.last_started_at) }}</template>
            </el-table-column>
            <el-table-column prop="processed" label="窗口内处理数" width="120" align="center"></el-table-column>
        </el-table>
    </el-card>
</div>

<style>
    .stat-card .stat-label {
        font-size: 12px;
        color: #909399;
    }
    .stat-card .stat-value {
        font-size: 22px;
        font-weight: 600;
        margin: 6px 0 4px;
        color: #303133;
    }
    .stat-card .stat-sub {
        font-size: 12px;
        color: #909399;
    }
    .legend-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 2px;
        margin-right: 4px;
        vertical-align: middle;
    }
    .trend-chart {
        display: flex;
        align-items: stretch;
    }
    .trend-y-axis {
        width: 44px;
        padding: 6px 8px 22px 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: right;
        font-size: 11px;
        color: #909399;
        border-right: 1px solid #EBEEF5;
        user-select: none;
    }
    .trend-bars {
        flex: 1;
        display: flex;
        align-items: flex-end;
        height: 140px;
        padding: 12px 4px 22px 8px;
        gap: 3px;
        border-bottom: 1px solid #EBEEF5;
        position: relative;
    }
    .trend-grid-line {
        position: absolute;
        left: 0;
        right: 0;
        border-top: 1px dashed #F2F6FC;
        pointer-events: none;
    }
    .trend-bar-col {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        justify-content: flex-end;
        position: relative;
        min-width: 4px;
    }
    .trend-bar-stack {
        width: 100%;
        max-width: 24px;
        display: flex;
        flex-direction: column;
        border-radius: 2px 2px 0 0;
        overflow: hidden;
        background: #F2F6FC;
        min-height: 2px;
    }
    .bar-seg {
        width: 100%;
    }
    .bar-success { background: #67C23A; }
    .bar-released { background: #E6A23C; }
    .bar-error { background: #F56C6C; }
    .bar-other { background: #909399; }
    .trend-bar-label {
        position: absolute;
        bottom: -20px;
        font-size: 10px;
        color: #909399;
        white-space: nowrap;
    }
</style>

<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                ranges: <?php echo json_encode($ranges, JSON_UNESCAPED_UNICODE); ?>,
                range: "1h",
                autoRefresh: false,
                timer: null,
                loadingRealtime: false,
                loadingStats: false,
                loadingWorkers: false,
                realtimeError: "",
                realtime: {
                    connection: "",
                    retry_after: 180,
                    monitor_enable: true,
                    queues: []
                },
                summary: {},
                jobs: [],
                trend: {
                    bucket_seconds: 60,
                    points: []
                },
                workers: [],
                workerWindow: 300
            },
            computed: {
                loading: function () {
                    return this.loadingRealtime || this.loadingStats || this.loadingWorkers;
                },
                maxPointTotal: function () {
                    var max = 0;
                    var pts = this.trend.points || [];
                    for (var i = 0; i < pts.length; i++) {
                        if (pts[i].total > max) {
                            max = pts[i].total;
                        }
                    }
                    return max > 1 ? max : 2;
                },
                yAxisTop: function () {
                    return this.maxPointTotal;
                },
                yAxisMid: function () {
                    return Math.round(this.maxPointTotal / 2);
                },
                trendUnitLabel: function () {
                    var b = Number(this.trend.bucket_seconds || 60);
                    if (b >= 86400) return "次/天";
                    if (b >= 3600) return "次/小时";
                    return "次/分钟";
                }
            },
            mounted: function () {
                this.refreshAll();
            },
            beforeDestroy: function () {
                this.clearTimer();
            },
            methods: {
                refreshAll: function () {
                    this.loadRealtime();
                    this.loadStats();
                    this.loadWorkers();
                },
                toggleAutoRefresh: function (val) {
                    var _this = this;
                    this.clearTimer();
                    if (val) {
                        this.timer = setInterval(function () {
                            _this.loadRealtime(true);
                        }, 10000);
                    }
                },
                clearTimer: function () {
                    if (this.timer) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                },
                loadRealtime: function (silent) {
                    var _this = this;
                    if (!silent) {
                        this.loadingRealtime = true;
                    }
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    $.ajax({
                        url: "{:api_url('/common/queue.dashboard/index')}",
                        data: { _action: "getRealtime" },
                        dataType: "json",
                        type: "get",
                        success: function (res) {
                            _this.loadingRealtime = false;
                            if (res.status) {
                                _this.realtimeError = "";
                                _this.realtime = res.data;
                            } else {
                                _this.realtimeError = res.msg || "获取实时状态失败";
                            }
                        },
                        error: function () {
                            _this.loadingRealtime = false;
                            _this.realtimeError = "请求实时状态失败";
                        }
                    });
                },
                loadStats: function () {
                    var _this = this;
                    this.loadingStats = true;
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    $.ajax({
                        url: "{:api_url('/common/queue.dashboard/index')}",
                        data: { _action: "getStats", range: this.range },
                        dataType: "json",
                        type: "get",
                        success: function (res) {
                            _this.loadingStats = false;
                            if (res.status && res.data) {
                                _this.summary = res.data.summary || {};
                                _this.jobs = res.data.jobs || [];
                                _this.trend = res.data.trend || { bucket_seconds: 60, points: [] };
                            } else {
                                _this.$message.error(res.msg || "加载统计数据失败");
                            }
                        },
                        error: function () {
                            _this.loadingStats = false;
                            _this.$message.error("请求统计数据失败");
                        }
                    });
                },
                loadWorkers: function () {
                    var _this = this;
                    this.loadingWorkers = true;
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    $.ajax({
                        url: "{:api_url('/common/queue.dashboard/index')}",
                        data: { _action: "getWorkers" },
                        dataType: "json",
                        type: "get",
                        success: function (res) {
                            _this.loadingWorkers = false;
                            if (res.status && res.data) {
                                _this.workers = res.data.workers || [];
                                _this.workerWindow = res.data.active_seconds || 300;
                            }
                        },
                        error: function () {
                            _this.loadingWorkers = false;
                        }
                    });
                },
                openMonitorLog: function (queue, jobName, status) {
                    var url = "{:api_url('/common/queue.dashboard/monitorLog')}";
                    var params = [];
                    if (queue) params.push("queue=" + encodeURIComponent(queue));
                    if (jobName) params.push("job_name=" + encodeURIComponent(jobName));
                    if (status) params.push("status=" + encodeURIComponent(status));
                    if (params.length) {
                        url += (url.indexOf("?") === -1 ? "?" : "&") + params.join("&");
                    }
                    if (window.Ztbcms && Ztbcms.openNewIframeByUrl) {
                        Ztbcms.openNewIframeByUrl("队列执行记录", url);
                    } else {
                        window.location.href = url;
                    }
                },
                openFailedJobs: function (queue) {
                    var url = "{:api_url('/common/queue.dashboard/failedJobs')}";
                    if (queue) {
                        url += (url.indexOf("?") === -1 ? "?" : "&") + "queue=" + encodeURIComponent(queue);
                    }
                    if (window.Ztbcms && Ztbcms.openNewIframeByUrl) {
                        Ztbcms.openNewIframeByUrl("队列失败任务", url);
                    } else {
                        window.location.href = url;
                    }
                },
                barHeight: function (total) {
                    if (!total || total <= 0) return 2;
                    return Math.max(6, Math.round((total / this.maxPointTotal) * 110));
                },
                shouldShowLabel: function (idx, len) {
                    if (len <= 12) return true;
                    var step = Math.ceil(len / 8);
                    return idx % step === 0 || idx === len - 1;
                },
                formatBucketLabel: function (ts, bucketSeconds) {
                    var d = new Date(ts * 1000);
                    var pad = function (n) { return n < 10 ? "0" + n : "" + n; };
                    if (bucketSeconds >= 86400) {
                        return pad(d.getMonth() + 1) + "-" + pad(d.getDate());
                    }
                    return pad(d.getHours()) + ":" + pad(d.getMinutes());
                },
                formatPointTooltip: function (pt) {
                    var d = new Date(pt.time * 1000);
                    return d.toLocaleString() + " | 总计:" + pt.total + " 成功:" + pt.succeeded + " 重入队:" + pt.released + " 失败/异常:" + pt.errors;
                },
                formatPercent: function (val) {
                    if (val === null || val === undefined) return "-";
                    return val + "%";
                },
                formatDuration: function (ms) {
                    if (ms === null || ms === undefined) return "-";
                    var n = Number(ms);
                    if (isNaN(n)) return "-";
                    if (n < 1000) return Math.round(n) + " ms";
                    return (n / 1000).toFixed(2) + " s";
                },
                formatTimeMs: function (ms) {
                    if (!ms) return "-";
                    var d = new Date(Number(ms));
                    var pad = function (n) { return n < 10 ? "0" + n : "" + n; };
                    return d.getFullYear() + "-" + pad(d.getMonth() + 1) + "-" + pad(d.getDate()) + " " + pad(d.getHours()) + ":" + pad(d.getMinutes()) + ":" + pad(d.getSeconds());
                }
            }
        });
    });
</script>
