<div id="app" v-cloak>
    <el-card>
        <div style="margin-bottom: 20px;">

            <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('common', 'cron.dashboard', 'addOrEditCron')){ ?>
                <el-button @click="createCron" type="primary" size="mini">
                    新增任务
                </el-button>
            <?php } ?>

            <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('common', 'cron.dashboard', 'schedulingLog')){ ?>
            <el-button @click="openSchedulingLog" type="primary" size="mini">
                调度日志
            </el-button>
            <?php } ?>

            <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('common', 'cron.dashboard', 'cronLog')){ ?>
            <el-button @click="openTaskLog"  type="primary" size="mini">
                任务日志
            </el-button>
            <?php } ?>
        </div>
        <el-table
                :data="lists"
                highlight-current-row
                style="width: 100%">
            <el-table-column
                    align="center"
                    prop="subject"
                    label="计划标题"
                    width="100">
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
                    label="开启状态">
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
                    <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('common', 'cron.dashboard', 'addOrEditCron')){ ?>
                    <el-button @click="editCron(props.row.cron_id)" type="text" size="mini">
                        编辑
                    </el-button>
                    <?php } ?>

                    <el-button @click="runCronAction(props.row.cron_id)" type="text" size="mini">立即执行
                    </el-button>

                    <el-button @click="deleteCron(props.row.cron_id)" type="text" size="mini" style="color: #F56C6C"> 删除
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
                runCronAction: function (cronId) {
                    $.ajax({
                        url: "{:api_url('/common/cron.dashboard/cron')}",
                        data: {
                            cron_id: cronId,
                            _action : 'runAction'
                        },
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            layer.msg(res.msg)
                        }
                    })
                },
                deleteCron: function (cronId) {
                    var _this = this
                    layer.confirm('是否确认删除?', {title: '提示'}, function () {
                        _this.doDeleteCron(cronId)
                    })
                },
                doDeleteCron: function (cronId) {
                    var _this = this
                    $.ajax({
                        url: "{:api_url('/common/cron.dashboard/cron')}",
                        data: {
                            cron_id: cronId,
                            _action : 'deleteCron'
                        },
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            layer.msg(res.msg)
                            _this.getList();
                        }
                    })
                },
                editCron: function (cronId) {
                    var _this = this
                    var url = "{:api_url('/common/cron.dashboard/addOrEditCron')}?cron_id=" + cronId;
                    layer.open({
                        type: 2,
                        title: '编辑计划任务',
                        shadeClose: true,
                        area: ['70%', '70%'],
                        content: url,
                        end: function(){
                           _this.getList()
                        }
                    });
                },
                createCron: function () {
                    var _this = this
                    var url = "{:api_url('/common/cron.dashboard/addOrEditCron')}";
                    layer.open({
                        type: 2,
                        title: '新增计划任务',
                        shadeClose: true,
                        area: ['70%', '70%'],
                        content: url,
                        end: function(){
                            _this.getList()
                        }
                    });
                },
                openSchedulingLog: function(){
                    Ztbcms.openNewIframeByUrl('调度日志', "{:api_url('/common/cron.dashboard/schedulingLog')}")
                },
                openTaskLog: function(){
                    Ztbcms.openNewIframeByUrl('任务日志', "{:api_url('/common/cron.dashboard/cronLog')}")
                },
                currentPageChange: function (e) {
                    this.currentPage = e;
                    this.getList();
                },
                getList: function () {
                    var _this = this
                    $.ajax({
                        url: "{:api_url('/common/cron.dashboard/cron')}",
                        data: {
                            page: this.currentPage,
                            _action : 'getCronList'
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
                }
            }
        });
    })
</script>
