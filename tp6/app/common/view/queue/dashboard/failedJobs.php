<div id="app" v-cloak>
    <el-card>
        <div slot="header" style="display: flex; justify-content: space-between; align-items: center;">
            <span>队列监控 · 失败任务</span>
            <span style="font-size: 12px; color: #909399;">共 {{ totalItems }} 条失败任务</span>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap;">
            <el-form :inline="true" :model="searchForm" size="small">
                <el-form-item>
                    <el-select v-model="searchForm.queue" placeholder="全部队列" clearable filterable style="width: 180px;">
                        <el-option v-for="q in queues" :key="q" :label="q" :value="q"></el-option>
                    </el-select>
                </el-form-item>
                <el-form-item>
                    <el-input v-model="searchForm.keyword" placeholder="任务类 / Payload 关键字" clearable style="width: 220px;"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-date-picker
                        v-model="searchForm.daterange"
                        type="daterange"
                        range-separator="至"
                        value-format="yyyy-MM-dd"
                        start-placeholder="失败开始日期"
                        end-placeholder="失败结束日期"
                        style="width: 240px;">
                    </el-date-picker>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" icon="el-icon-search" @click="search">查询</el-button>
                    <el-button @click="resetSearch">重置</el-button>
                </el-form-item>
            </el-form>

            <div>
                <el-button
                    size="small"
                    type="primary"
                    :disabled="!selectedIds.length || submitting"
                    :loading="submitting"
                    @click="batchRetry">
                    批量重试 ({{ selectedIds.length }})
                </el-button>
                <el-button
                    size="small"
                    type="danger"
                    :disabled="!selectedIds.length || submitting"
                    :loading="submitting"
                    @click="batchDelete">
                    批量删除 ({{ selectedIds.length }})
                </el-button>
            </div>
        </div>

        <el-table
            :data="lists"
            v-loading="loading"
            empty-text="暂无失败任务"
            size="small"
            style="width: 100%;"
            @selection-change="handleSelectionChange">
            <el-table-column type="selection" width="48" align="center"></el-table-column>
            <el-table-column prop="id" label="ID" width="80" align="center"></el-table-column>
            <el-table-column prop="connection" label="连接" width="110" align="center"></el-table-column>
            <el-table-column prop="queue" label="队列" min-width="150"></el-table-column>
            <el-table-column prop="job_name" label="任务类" min-width="240" show-overflow-tooltip></el-table-column>
            <el-table-column prop="exception_summary" label="异常摘要" min-width="280" show-overflow-tooltip></el-table-column>
            <el-table-column prop="fail_time" label="失败时间" width="170" align="center"></el-table-column>
            <el-table-column label="操作" width="160" align="center" fixed="right">
                <template slot-scope="scope">
                    <el-button type="text" size="small" @click="openDetail(scope.row.id)">详情</el-button>
                    <el-button type="text" size="small" :disabled="submitting" @click="retrySingle(scope.row.id)">重试</el-button>
                    <el-button type="text" size="small" style="color: #F56C6C;" :disabled="submitting" @click="deleteSingle(scope.row.id)">删除</el-button>
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
                submitting: false,
                queues: [],
                searchForm: {
                    queue: "",
                    keyword: "",
                    daterange: []
                },
                lists: [],
                selectedIds: [],
                currentPage: 1,
                limit: 20,
                totalItems: 0
            },
            mounted: function () {
                var params = new URLSearchParams(window.location.search);
                if (params.get("queue")) {
                    this.searchForm.queue = params.get("queue");
                }
                this.getList();
            },
            methods: {
                search: function () {
                    this.currentPage = 1;
                    this.getList();
                },
                resetSearch: function () {
                    this.searchForm = { queue: "", keyword: "", daterange: [] };
                    this.currentPage = 1;
                    this.getList();
                },
                currentPageChange: function (page) {
                    this.currentPage = page;
                    this.getList();
                },
                handleSelectionChange: function (rows) {
                    this.selectedIds = rows.map(function (r) { return r.id; });
                },
                getList: function () {
                    var _this = this;
                    this.loading = true;
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    var range = this.searchForm.daterange || [];
                    $.ajax({
                        url: "{:api_url('/common/queue.dashboard/failedJobs')}",
                        data: {
                            _action: "getList",
                            page: this.currentPage,
                            limit: this.limit,
                            queue: this.searchForm.queue,
                            keyword: this.searchForm.keyword,
                            start_date: range[0] || "",
                            end_date: range[1] || ""
                        },
                        dataType: "json",
                        type: "get",
                        success: function (res) {
                            _this.loading = false;
                            if (res.status && res.data) {
                                _this.lists = res.data.items || [];
                                _this.totalItems = res.data.total_items || 0;
                                _this.currentPage = res.data.page || 1;
                                _this.queues = res.data.queues || [];
                            } else {
                                _this.$message.error(res.msg || "加载失败任务失败");
                            }
                        },
                        error: function () {
                            _this.loading = false;
                            _this.$message.error("请求失败任务失败");
                        }
                    });
                },
                openDetail: function (id) {
                    var _this = this;
                    var base = "{:api_url('/common/queue.dashboard/failedJobDetail')}";
                    var url = base + (base.indexOf("?") === -1 ? "?" : "&") + "id=" + encodeURIComponent(id);
                    layer.open({
                        type: 2,
                        title: "失败任务详情 #" + id,
                        shadeClose: true,
                        area: ["82%", "85%"],
                        content: url,
                        end: function () {
                            _this.getList();
                        }
                    });
                },
                retrySingle: function (id) {
                    this.confirmRetry([id]);
                },
                batchRetry: function () {
                    this.confirmRetry(this.selectedIds.slice());
                },
                confirmRetry: function (ids) {
                    var _this = this;
                    if (!ids.length || this.submitting) return;
                    layer.confirm("确认将选中的 " + ids.length + " 条失败任务重新推入队列？", { title: "重试确认" }, function (index) {
                        layer.close(index);
                        _this.doAction("retry", ids);
                    });
                },
                deleteSingle: function (id) {
                    this.confirmDelete([id]);
                },
                batchDelete: function () {
                    this.confirmDelete(this.selectedIds.slice());
                },
                confirmDelete: function (ids) {
                    var _this = this;
                    if (!ids.length || this.submitting) return;
                    layer.confirm("确认永久删除选中的 " + ids.length + " 条失败任务？", { title: "删除确认" }, function (index) {
                        layer.close(index);
                        _this.doAction("delete", ids);
                    });
                },
                doAction: function (action, ids) {
                    var _this = this;
                    this.submitting = true;
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    var url = action === "retry"
                        ? "{:api_url('/common/queue.dashboard/retryFailedJob')}"
                        : "{:api_url('/common/queue.dashboard/deleteFailedJob')}";
                    $.ajax({
                        url: url,
                        data: { ids: ids },
                        dataType: "json",
                        type: "post",
                        success: function (res) {
                            _this.submitting = false;
                            if (res.status) {
                                _this.$message.success(res.msg || "操作成功");
                                _this.getList();
                            } else {
                                _this.$message.error(res.msg || "操作失败");
                            }
                        },
                        error: function () {
                            _this.submitting = false;
                            _this.$message.error("请求失败");
                        }
                    });
                }
            }
        });
    });
</script>
