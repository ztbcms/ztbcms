<div id="app" v-cloak>
    <el-card>
        <div slot="header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>队列监控 · 执行记录</span>
            <span style="font-size: 12px; color: #909399;">共 {{ totalItems }} 条记录</span>
        </div>

        <el-form :inline="true" :model="searchForm" size="small">
            <el-form-item>
                <el-select v-model="searchForm.queue" placeholder="全部队列" clearable filterable style="width: 170px;">
                    <el-option v-for="q in options.queues" :key="q" :label="q" :value="q"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-select v-model="searchForm.job_name" placeholder="全部任务类" clearable filterable style="width: 240px;">
                    <el-option v-for="j in options.job_names" :key="j" :label="j" :value="j"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-select v-model="searchForm.status" placeholder="全部状态" clearable style="width: 130px;">
                    <el-option v-for="s in options.statuses" :key="s.value" :label="s.label" :value="s.value"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item>
                <el-date-picker
                    v-model="searchForm.daterange"
                    type="daterange"
                    range-separator="至"
                    value-format="yyyy-MM-dd"
                    start-placeholder="开始日期"
                    end-placeholder="结束日期"
                    style="width: 240px;">
                </el-date-picker>
            </el-form-item>
            <el-form-item>
                <el-input v-model="searchForm.min_duration" placeholder="耗时 ≥ (ms)" clearable style="width: 125px;"></el-input>
            </el-form-item>
            <el-form-item>
                <el-input v-model="searchForm.keyword" placeholder="链路UUID / 队列任务ID" clearable style="width: 200px;"></el-input>
            </el-form-item>
            <el-form-item>
                <el-button type="primary" icon="el-icon-search" @click="search">查询</el-button>
                <el-button @click="resetSearch">重置</el-button>
            </el-form-item>
        </el-form>

        <el-table :data="lists" v-loading="loading" empty-text="暂无执行记录" size="small" style="width: 100%;">
            <el-table-column prop="id" label="ID" width="75" align="center"></el-table-column>
            <el-table-column prop="queue" label="队列" min-width="140"></el-table-column>
            <el-table-column prop="job_name" label="任务类" min-width="240" show-overflow-tooltip></el-table-column>
            <el-table-column label="状态" width="95" align="center">
                <template slot-scope="scope">
                    <el-tag :type="statusTagType(scope.row.status)" size="small">{{ scope.row.status_text }}</el-tag>
                </template>
            </el-table-column>
            <el-table-column prop="attempt" label="第N次" width="70" align="center"></el-table-column>
            <el-table-column label="开始时间" width="165" align="center">
                <template slot-scope="scope">{{ formatTimeMs(scope.row.started_at) }}</template>
            </el-table-column>
            <el-table-column label="耗时" width="100" align="center">
                <template slot-scope="scope">
                    <span v-if="scope.row.status === 'running' || scope.row.status === 'lost'">-</span>
                    <span v-else>{{ formatDuration(scope.row.duration_ms) }}</span>
                </template>
            </el-table-column>
            <el-table-column label="排队时长" width="100" align="center">
                <template slot-scope="scope">{{ formatDuration(scope.row.wait_ms) }}</template>
            </el-table-column>
            <el-table-column label="内存峰值" width="95" align="center">
                <template slot-scope="scope">{{ formatMemory(scope.row.memory_peak) }}</template>
            </el-table-column>
            <el-table-column label="主机 / PID" min-width="150" show-overflow-tooltip>
                <template slot-scope="scope">{{ scope.row.hostname }}:{{ scope.row.pid }}</template>
            </el-table-column>
            <el-table-column prop="exception_class" label="异常类" min-width="150" show-overflow-tooltip></el-table-column>
            <el-table-column label="操作" width="85" align="center" fixed="right">
                <template slot-scope="scope">
                    <el-button type="text" size="small" @click="openDetail(scope.row.id)">详情</el-button>
                </template>
            </el-table-column>
        </el-table>

        <div style="margin-top: 16px; display: flex; justify-content: flex-end;">
            <el-pagination
                background
                @current-change="currentPageChange"
                layout="total, prev, pager, next"
                :current-page="currentPage"
                :page-size="limit"
                :total="totalItems">
            </el-pagination>
        </div>
    </el-card>
</div>

<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                loading: false,
                options: {
                    queues: [],
                    job_names: [],
                    statuses: []
                },
                searchForm: {
                    queue: "",
                    job_name: "",
                    status: "",
                    daterange: [],
                    min_duration: "",
                    keyword: ""
                },
                lists: [],
                currentPage: 1,
                limit: 20,
                totalItems: 0
            },
            mounted: function () {
                this.initFromQuery();
                this.loadOptions();
                this.getList();
            },
            methods: {
                initFromQuery: function () {
                    var params = new URLSearchParams(window.location.search);
                    if (params.get("queue")) this.searchForm.queue = params.get("queue");
                    if (params.get("job_name")) this.searchForm.job_name = params.get("job_name");
                    if (params.get("status")) this.searchForm.status = params.get("status");
                    if (params.get("keyword")) this.searchForm.keyword = params.get("keyword");
                },
                loadOptions: function () {
                    var _this = this;
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    $.ajax({
                        url: "{:api_url('/common/queue.dashboard/monitorLog')}",
                        data: { _action: "getOptions" },
                        dataType: "json",
                        type: "get",
                        success: function (res) {
                            if (res.status && res.data) {
                                _this.options = res.data;
                            }
                        }
                    });
                },
                search: function () {
                    this.currentPage = 1;
                    this.getList();
                },
                resetSearch: function () {
                    this.searchForm = {
                        queue: "",
                        job_name: "",
                        status: "",
                        daterange: [],
                        min_duration: "",
                        keyword: ""
                    };
                    this.currentPage = 1;
                    this.getList();
                },
                currentPageChange: function (page) {
                    this.currentPage = page;
                    this.getList();
                },
                getList: function () {
                    var _this = this;
                    this.loading = true;
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    var range = this.searchForm.daterange || [];
                    $.ajax({
                        url: "{:api_url('/common/queue.dashboard/monitorLog')}",
                        data: {
                            _action: "getList",
                            page: this.currentPage,
                            limit: this.limit,
                            queue: this.searchForm.queue,
                            job_name: this.searchForm.job_name,
                            status: this.searchForm.status,
                            start_date: range[0] || "",
                            end_date: range[1] || "",
                            min_duration: this.searchForm.min_duration,
                            keyword: this.searchForm.keyword
                        },
                        dataType: "json",
                        type: "get",
                        success: function (res) {
                            _this.loading = false;
                            if (res.status && res.data) {
                                _this.lists = res.data.items || [];
                                _this.totalItems = res.data.total_items || 0;
                                _this.currentPage = res.data.page || 1;
                            } else {
                                _this.$message.error(res.msg || "加载执行记录失败");
                            }
                        },
                        error: function () {
                            _this.loading = false;
                            _this.$message.error("请求执行记录失败");
                        }
                    });
                },
                openDetail: function (id) {
                    var base = "{:api_url('/common/queue.dashboard/monitorDetail')}";
                    var url = base + (base.indexOf("?") === -1 ? "?" : "&") + "id=" + encodeURIComponent(id);
                    layer.open({
                        type: 2,
                        title: "执行记录详情 #" + id,
                        shadeClose: true,
                        area: ["82%", "85%"],
                        content: url
                    });
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
                    return (n / 1024 / 1024).toFixed(1) + " MB";
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
