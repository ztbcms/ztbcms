<div id="app" v-cloak style="padding: 12px;">
    <el-card v-loading="loading" shadow="never">
        <div v-if="item">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span style="font-size: 15px; font-weight: 600;">失败任务 #{{ item.id }}</span>
                <div>
                    <el-button size="small" type="primary" :loading="submitting" :disabled="submitting" @click="doRetry">重试该任务</el-button>
                    <el-button size="small" type="danger" :loading="submitting" :disabled="submitting" @click="doDelete">删除</el-button>
                </div>
            </div>

            <el-descriptions :column="2" border size="small">
                <el-descriptions-item label="记录 ID">{{ item.id }}</el-descriptions-item>
                <el-descriptions-item label="失败时间">{{ item.fail_time }}</el-descriptions-item>
                <el-descriptions-item label="队列连接">{{ item.connection }}</el-descriptions-item>
                <el-descriptions-item label="队列名称">{{ item.queue }}</el-descriptions-item>
                <el-descriptions-item label="任务类" :span="2">{{ item.job_name || '-' }}</el-descriptions-item>
                <el-descriptions-item label="原链路 UUID" :span="2">{{ item.monitor_uuid || '-' }}</el-descriptions-item>
            </el-descriptions>

            <div style="margin-top: 18px;">
                <div class="sec-title">异常信息与堆栈</div>
                <pre class="code-box">{{ item.exception }}</pre>
            </div>

            <div style="margin-top: 18px;">
                <div class="sec-title">任务 Payload</div>
                <pre class="code-box">{{ formatPayload(item.payload) }}</pre>
            </div>
        </div>
        <div v-else-if="!loading" style="text-align: center; color: #909399; padding: 32px 0;">
            失败任务不存在或已被处理
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
        max-height: 340px;
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
                submitting: false,
                item: null
            },
            mounted: function () {
                this.loadDetail();
            },
            methods: {
                loadDetail: function () {
                    var _this = this;
                    this.loading = true;
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    $.ajax({
                        url: "{:api_url('/common/queue.dashboard/failedJobDetail')}",
                        data: { _action: "getDetail", id: this.id },
                        dataType: "json",
                        type: "get",
                        success: function (res) {
                            _this.loading = false;
                            if (res.status && res.data) {
                                _this.item = res.data.item;
                            } else {
                                _this.item = null;
                            }
                        },
                        error: function () {
                            _this.loading = false;
                            _this.$message.error("请求详情失败");
                        }
                    });
                },
                doRetry: function () {
                    var _this = this;
                    if (!this.item || this.submitting) return;
                    layer.confirm("确认将该失败任务重新推入队列？", { title: "重试确认" }, function (index) {
                        layer.close(index);
                        _this.submitAction("{:api_url('/common/queue.dashboard/retryFailedJob')}");
                    });
                },
                doDelete: function () {
                    var _this = this;
                    if (!this.item || this.submitting) return;
                    layer.confirm("确认永久删除该失败任务？", { title: "删除确认" }, function (index) {
                        layer.close(index);
                        _this.submitAction("{:api_url('/common/queue.dashboard/deleteFailedJob')}");
                    });
                },
                submitAction: function (url) {
                    var _this = this;
                    this.submitting = true;
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    $.ajax({
                        url: url,
                        data: { ids: [this.id] },
                        dataType: "json",
                        type: "post",
                        success: function (res) {
                            _this.submitting = false;
                            if (res.status) {
                                _this.$message.success(res.msg || "操作成功");
                                setTimeout(function () {
                                    if (parent && parent.layer) {
                                        var idx = parent.layer.getFrameIndex(window.name);
                                        if (idx) {
                                            parent.layer.close(idx);
                                            return;
                                        }
                                    }
                                    _this.loadDetail();
                                }, 500);
                            } else {
                                _this.$message.error(res.msg || "操作失败");
                            }
                        },
                        error: function () {
                            _this.submitting = false;
                            _this.$message.error("请求失败");
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
                }
            }
        });
    });
</script>
