<div id="app" v-cloak>
    <el-card>
        <div style="margin-bottom: 20px;">
            <el-button @click="createCron" type="primary">
                新增任务
            </el-button>
            <el-button onclick="javascript:location.href='{:urlx(\'common/cron.dashboard/schedulingLog\')}';"
                       type="default">
                调度日志
            </el-button>
            <el-button onclick="javascript:location.href='{:urlx(\'common/cron.dashboard/cronLog\')}';"
                       type="default">
                任务日志
            </el-button>
        </div>
        <el-table
                :data="lists"
                border
                style="width: 100%">
            <el-table-column
                    align="center"
                    prop="subject"
                    label="计划标题"
                    width="180">
            </el-table-column>
            <el-table-column
                    min-width="300"
                    align="center"
                    prop="cron_file"
                    label="执行文件"
            >
            </el-table-column>
            <el-table-column
                    width="180"
                    align="center"
                    prop="loop_time_text"
                    label="任务周期">
            </el-table-column>
            <el-table-column
                    align="center"
                    width="80"
                    label="任务状态">
                <template slot-scope="props">
                    <el-tag v-if="props.row.isopen == 1" type="success">开启</el-tag>
                    <el-tag v-else type="danger">关闭</el-tag>
                </template>
            </el-table-column>
            <el-table-column
                    align="center"
                    prop="modified_time"
                    width="180"
                    label="上次执行时间">
            </el-table-column>
            <el-table-column
                    width="180"
                    align="center"
                    prop="next_time"
                    label="下次执行时间">
            </el-table-column>
            <el-table-column
                    fixed="right"
                    width="300"
                    align="center"
                    label="操作">
                <template slot-scope="props">
                    <el-button @click="editCron(props.row.cron_id)" type="primary">
                        编辑
                    </el-button>
                    <el-button @click="deleteCron(props.row.cron_id)" type="danger"> 删除
                    </el-button>
                    <el-button @click="runCronAction(props.row.cron_id)" type="success">立即执行
                    </el-button>
                </template>
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
                currentPage: 1
            },
            mounted: function () {
                this.getList();
            },
            methods: {
                runCronAction(cronId) {
                    $.ajax({
                        url: "{:urlx('common/cron.dashboard/runAction')}",
                        data: {cron_id: cronId},
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            layer.msg(res.msg)
                        }
                    })
                },
                deleteCron(cronId) {
                    var _this = this;
                    this.$confirm('是否确认删除？').then(() => {
                        $.ajax({
                            url: "{:urlx('common/cron.dashboard/deleteCron')}",
                            data: {cron_id: cronId},
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                _this.getList();
                            }
                        })
                    }).catch(() => {
                    });
                },
                editCron(cronId) {
                    location.href = "{:urlx('common/cron.dashboard/createCron')}?cron_id=" + cronId;
                },
                createCron: function () {
                    location.href = "{:urlx('common/cron.dashboard/createCron')}";
                },
                currentPageChange(e) {
                    this.currentPage = e;
                    this.getList();
                },
                getList: function () {
                    var _this = this;
                    $.ajax({
                        url: "{:urlx('common/cron.dashboard/getCronList')}",
                        data: {
                            page: this.currentPage
                        },
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
