<div id="app" v-cloak>
    <el-card>
        <div>
            <el-form :inline="true" :model="searchForm" class="demo-form-inline">
                <el-form-item label="">
                    <el-date-picker
                            v-model="searchForm.datetime"
                            type="datetimerange"
                            range-separator="至"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="">
                    <el-input v-model="searchForm.user_time" placeholder="耗时"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="search">查询</el-button>
                </el-form-item>
            </el-form>
        </div>
        <el-table
                :data="lists"
                border
                style="width: 100%">
            <el-table-column
                    align="center"
                    prop="id"
                    label="id"
                    width="60">
            </el-table-column>
            <el-table-column
                    min-width="180"
                    align="center"
                    prop="start_time"
                    label="开始时间"
            >
            </el-table-column>
            <el-table-column
                    min-width="180"
                    align="center"
                    prop="start_time"
                    label="结束时间">
            </el-table-column>
            <el-table-column
                    align="center"
                    min-width="100"
                    prop="use_time"
                    label="耗时（秒）">
            </el-table-column>
            <el-table-column
                    align="center"
                    prop="error_count"
                    min-width="100"
                    label="异常次数">
            </el-table-column>
            <el-table-column
                    min-width="100"
                    align="center"
                    prop="cron_count"
                    label="执行任务数">
            </el-table-column>
        </el-table>
        <div style="text-align: center;margin-top: 20px">
            <el-pagination
                    background
                    @current-change="currentPageChange"
                    layout="prev, pager, next"
                    :current-page="currentPage"
                    :page-count="totalCount"
                    :page-size="pageSize"
                    :total="totalCount">
            </el-pagination>
        </div>
    </el-card>
</div>
<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                lists: [],
                totalCount: 0,
                pageSize: 10,
                pageCount: 0,
                currentPage: 1,
                searchForm: {}
            },
            mounted: function () {
                this.getList();
            },
            methods: {
                search: function () {
                    this.currentPage = 1;
                    this.getList();
                },
                currentPageChange: function(e) {
                    this.currentPage = e;
                    this.getList();
                },
                getList: function () {
                    var _this = this;
                    $.ajax({
                        url: "{:api_url('/common/cron.dashboard/schedulingLog')}",
                        data: Object.assign({
                            page: this.currentPage
                        }, this.searchForm),
                        dataType: 'json',
                        type: 'get',
                        success: function (res) {
                            var data = res.data;
                            _this.lists = data.data;
                            _this.totalCount = data.total;
                            _this.pageSize = data.per_page;
                            _this.pageCount = data.last_page;
                            _this.currentPage = data.current_page;
                        }
                    })
                },
            }
        });
    })
</script>
