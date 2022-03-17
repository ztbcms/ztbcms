<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>下载记录</span>
        </div>

        <div class="filter-container" style="margin-bottom: 15px;">

            <el-input v-model="listQuery.keywords"
                      placeholder="请输入关键词"
                      style="width: 200px;" class="filter-item">
            </el-input>

            <el-button @click="doSearch" type="primary">筛选</el-button>
        </div>

        <el-tabs v-model="listQuery.downloader_state" @tab-click="onChangeDownloadState">
            <el-tab-pane label="全部" name="0"></el-tab-pane>
            <el-tab-pane label="待下载" name="10"></el-tab-pane>
            <el-tab-pane label="下载中" name="20"></el-tab-pane>
            <el-tab-pane label="下载成功" name="30"></el-tab-pane>
            <el-tab-pane label="下载失败" name="40"></el-tab-pane>
        </el-tabs>

        <el-table size="small"
                  :key="tableKey"
                  :data="lists"
                  fit
                  highlight-current-row
                  style="width: 100%;"
        >
            <el-table-column label="下载结果" align="left" min-width="250">
                <template slot-scope="{row}">
                    <span>{{ row.downloader_state_name }}</span>
                    <i v-if="row.downloader_state == 10 || row.downloader_state == 20" class="el-icon-loading">
                    </i>

                    <el-tooltip
                            style="margin-left: 5px;"
                            v-if="row.downloader_state == 40 && row.downloader_result"
                            class="item"
                            effect="dark"
                            :content="row.downloader_result"
                            placement="top-start">
                        <el-link type="danger"> 原因</el-link>
                    </el-tooltip>

                    <br>
                    <span>{{ row.downloader_url }}</span>
                </template>
            </el-table-column>

            <el-table-column label="文件信息" align="left" min-width="280">
                <template slot-scope="{row}">
                    <div v-if="row.file_name">
                        <span>文件名 ：{{ row.file_name }}</span>
                        <br>
                        <span v-if="row.file_path">保存路径：{{ row.file_path }}</span>
                    </div>
                    <span v-else> - </span>
                </template>
            </el-table-column>

            <el-table-column label="文件" align="left" min-width="80">
                <template slot-scope="{row}">
                    <div v-if="row.file_url">
                        <el-link :href="row.file_url" target="_blank" type="primary">点击预览</el-link>
                    </div>
                    <span v-else> - </span>
                </template>
            </el-table-column>

            <el-table-column label="下载时长" align="left" min-width="120">
                <template slot-scope="{row}">
                    <span>开始时间 ：{{ row.process_start_date }}</span>
                    <br>
                    <span>结束时间 ：{{ row.process_end_date }}</span>
                    <br>
                    <span v-if="row.downloader_state == 30">耗时：{{ row.downloader_duration }} s</span>
                </template>
            </el-table-column>

            <el-table-column label="重试次数" align="left" min-width="80">
                <template slot-scope="{row}">
                    {{ row.downloader_implement_num }} 次
                </template>
            </el-table-column>

            <el-table-column label="时间" min-width="120" align="center">
                <template slot-scope="{row}">
                    <span>{{ row.create_time }}</span>
                </template>
            </el-table-column>

            <el-table-column
                    fixed="right"
                    width="160"
                    align="center"
                    label="操作">
                <template slot-scope="props">
                    <el-button
                            v-if="props.row.downloader_state == 10"
                            @click="implementDownloaderTask(props.row.downloader_id)"
                            type="text" size="mini">
                        立即执行
                    </el-button>

                    <el-button
                            v-if="props.row.downloader_state == 40"
                            @click="retryDownloaderTask(props.row.downloader_id)"
                            type="text" size="mini">
                        重新下载
                    </el-button>

                    <el-button @click="deleteDownloaderTask(props.row.downloader_id)"
                               type="text" size="mini"
                               style="color: #F56C6C">
                        删除
                    </el-button>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container" style="margin-top: 15px;">
            <el-pagination
                    background
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="total"
                    v-show="total>0"
                    :current-page.sync="listQuery.page"
                    :page-size.sync="listQuery.limit"
                    @current-change="getList"
                    :page-size="10"
            >
            </el-pagination>
        </div>
    </el-card>
</div>
<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                tableKey: 0,
                total: 0,
                page: 1,
                limit: 20,
                lists: [],
                page_count: 0,
                listQuery: {
                    page: 1,
                    limit: 20,
                    keywords: '',
                    downloader_status: '0',
                },
            },
            computed: {},
            watch: {},
            filters: {
                parseTime: function (time, format) {
                    return Ztbcms.formatTime(time, format)
                },
                statusFilter: function (status) {
                    let statusMap = {
                        published: 'success',
                        draft: 'info',
                        deleted: 'danger'
                    };
                    return statusMap[status]
                }
            },
            methods: {
                doSearch: function () {
                    var that = this;
                    that.page = 1;
                    that.getList();
                },
                getList: function () {
                    let that = this;
                    let url = '{:api_url("/common/downloader.Log/index")}';
                    let data = that.listQuery;
                    data._action = 'list';
                    that.httpGet(url, data, function (res) {
                        if (res.status) {
                            that.lists = res.data.data;
                            that.total = res.data.total;
                            that.page = res.data.page;
                            that.page_count = res.data.per_page;
                        } else {
                            layer.msg(res.msg, {time: 1000});
                        }
                    });
                },
                implementDownloaderTask: function (downloader_id) {
                    var _this = this
                    var data = {
                        downloader_id: downloader_id,
                        _action: 'implement'
                    }
                    this.httpPost("{:api_url('/common/downloader.Log/index')}", data, function (res) {
                        if (res.status) {
                            _this.getList();
                        }
                        layer.msg(res.msg, {time: 1000});
                    })
                },
                retryDownloaderTask: function (downloader_id) {
                    var _this = this
                    var data = {
                        downloader_id: downloader_id,
                        _action: 'retry'
                    }
                    this.httpPost("{:api_url('/common/downloader.Log/index')}", data, function (res) {
                        if (res.status) {
                            _this.getList();
                        }
                        layer.msg(res.msg, {time: 1000});
                    })
                },
                deleteDownloaderTask: function (downloader_id) {
                    var _this = this
                    layer.confirm('是否确认删除?', {title: '提示'}, function () {
                        _this.doDeleteDownloaderTask(downloader_id)
                    })
                },
                doDeleteDownloaderTask: function (downloader_id) {
                    var _this = this
                    var data = {
                        downloader_id: downloader_id,
                        _action: 'delete'
                    }
                    this.httpPost("{:api_url('/common/downloader.Log/index')}", data, function (res) {
                        if (res.status) {
                            _this.getList();
                        }
                        layer.msg(res.msg, {time: 1000});
                    })
                },
                onChangeDownloadState: function(){
                    this.listQuery.page = 1
                    this.getList()
                }
            },
            mounted: function () {
                this.getList();
            }
        })
    })
</script>