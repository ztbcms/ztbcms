<div id="app" v-cloak>
    <el-card>
        <div>
            <el-form :inline="true" :model="searchForm" class="demo-form-inline">
                <el-form-item label="">
                    <el-select v-model="searchForm.cron_id" placeholder="请选择计划任务" :filterable="true"  style="min-width: 240px">
                        <?php foreach ($corns as $key => $corn): ?>
                            <el-option label="{$corn}" value="{$key}"></el-option>
                        <?php endforeach; ?>
                    </el-select>
                </el-form-item>
                <el-form-item label="">
                    <el-date-picker
                            v-model="searchForm.datetime"
                            type="daterange"
                            range-separator="至"
                            value-format="yyyy-MM-dd"
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
                style="width: 100%">
            <el-table-column
                    align="center"
                    prop="id"
                    label="ID"
                    min-width="60">
            </el-table-column>
            <el-table-column
                    min-width="240"
                    align="left"
                    prop="cron_file"
                    label="计划任务"
            >
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
                    label="耗时(ms)">
            </el-table-column>
            <el-table-column
                    align="center"
                    min-width="100"
                    prop="throughput"
                    label="吞吐率(req/s)">
            </el-table-column>
            <el-table-column
                    align="center"
                    min-width="100"
                    label="执行结果">
                <template slot-scope="props">
                    <el-tag v-if="props.row.result == 1" type="success">成功</el-tag>
                    <el-tag v-else type="danger">失败</el-tag>
                </template>
            </el-table-column>
            <el-table-column
                    min-width="100"
                    align="center"
                    prop="result_msg"
                    label="返回结果">
                <template slot-scope="props">
                    <el-button @click="openDetail(props.row.result_msg)" type="text">查看</el-button>
                </template>
            </el-table-column>
        </el-table>
        <div style="margin-top: 20px">
            <el-pagination
                    background
                    @current-change="currentPageChange"
                    layout="prev, pager, next"
                    :current-page="currentPage"
                    :page-count="total_pages">
            </el-pagination>
        </div>
    </el-card>
</div>
<style>
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
    }
</style>
<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                searchForm: {
                    cron_id: "",
                    datetime: ["{:date('Y-m-d')}", "{:date('Y-m-d')}"],
                    user_time: ""
                },
                lists: [],
                total_pages: 0,
                currentPage: 1
            },
            mounted: function () {
                this.getList();
            },
            methods: {
                search: function () {
                    this.currentPage = 1;
                    this.getList();
                },
                currentPageChange: function (e) {
                    this.currentPage = e;
                    this.getList();
                },
                openDetail: function (detail) {
                    var info = '<pre style="padding: 12px">'+ detail + '</pre>'
                    layer.open({
                        type: 1,
                        skin:"",
                        title: false,
                        shadeClose: true,
                        area: ['80%','90%'],
                        content: info,
                        cancel: function () {
                        }
                    })
                },
                getList: function () {
                    var _this = this;
                    var data = this.searchForm
                    data['_action'] = 'getList'
                    data['page'] = this.currentPage
                    $.ajax({
                        url: "{:api_url('/common/cron.dashboard/cronLog')}",
                        data: data,
                        dataType: 'json',
                        type: 'get',
                        success: function (res) {
                            var data = res.data;
                            _this.lists = data.items;
                            _this.total_pages = data.total_pages;
                            _this.currentPage = data.page;
                        }
                    })
                }
            }
        });
    })
</script>
