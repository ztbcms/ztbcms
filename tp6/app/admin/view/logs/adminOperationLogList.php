<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-alert type="success" :closable="false">
            <p><strong>用户操作管理后台发起 POST 请求时会记录操作</strong></p>
            <p><strong>默认开启，性能上有损耗，请根据实际情况进行调整</strong></p>
        </el-alert>

        <div class="filter_container">
            <el-row :gutter="10">
                <el-col :span="3">
                    <el-input v-model="form.uid" placeholder="用户ID" size="medium"/>
                </el-col>
                <el-col :span="3">
                    <el-input v-model="form.ip" placeholder="IP" size="medium"/>
                </el-col>

                <el-col :span="4">
                    <el-date-picker
                            v-model="form.search_date"
                            value-format="yyyy-MM-dd"
                            type="daterange"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期" size="medium">
                    </el-date-picker>
                </el-col>

            </el-row>

            <el-row :gutter="10" style="margin-top: 10px;">

                <el-col :span="10">
                    <el-button type="primary" @click="getList" size="medium">
                        筛选
                    </el-button>

                    <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('admin', 'logs', 'deleteAdminOperationLog')){ ?>
                        <el-button @click="deleteLog" type="primary" plain size="medium">
                            删除30日前日志
                        </el-button>
                    <?php } ?>

                    <el-button @click="setOperationRecord" type="danger" plain size="medium">
                        设置操作开关
                    </el-button>

                </el-col>
            </el-row>
        </div>

        <el-tabs @tab-click="handleClickTabs">
            <el-tab-pane label="全部"></el-tab-pane>
            <el-tab-pane label="成功"></el-tab-pane>
            <el-tab-pane label="失败"></el-tab-pane>

            <el-table
                    size="medium"
                    :data="tableData"
                    style="width: 100%;"
                    @sort-change="handleSortChange">
                <el-table-column
                        prop="id"
                        label="记录ID"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="uid"
                        label="用户ID"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="method"
                        label="请求方法"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="url"
                        label="URL"
                        width="250">
                </el-table-column>
                <el-table-column
                        prop="params"
                        label="请求参数"
                        width="80">
                    <template slot-scope="scope">
                        <el-button type="text" @click="previewContent(scope.row.params)">查看</el-button>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="info"
                        label="响应"
                        width="80">
                    <template slot-scope="scope">
                        <el-button type="text" @click="previewContent(scope.row.response)">查看</el-button>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="status"
                        label="状态"
                        width="80">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status == 1" style="color: green">成功</span>
                        <span v-else style="color: red">失败</span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="time"
                        label="请求时间"
                        width="200">
                    <template slot-scope="scope">
                        <span>{{ scope.row.time | formatTime }}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="ip"
                        label="IP"
                        width="100">
                </el-table-column>
            </el-table>
            <div class="pager_container" style="margin-top: 15px;">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        :page-size="pagination.limit"
                        :current-page.sync="pagination.page"
                        :total="pagination.total_items"
                        @current-change="getList">
                </el-pagination>
            </div>
        </el-tabs>
    </el-card>
</div>

<style>
    .filter_container {
        margin-top: 6px;
    }

    .pager_container {}
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                tableData: [],
                pagination: {
                    page: 1,
                    limit: 20,
                    total_pages: 0,
                    total_items: 0,
                },
                form: {
                    search_date: ["{:date('Y-m-d', time()-7*24*60*60)}", "{:date('Y-m-d')}"],
                    uid: '',
                    ip: '',
                    start_time: '',
                    end_time: '',
                    status: '',
                    sort_time: ''//排序：时间
                },
                admin_operation_switch : 1
            },
            watch: {
                'form.search_date': function (newValue) {
                    if (newValue && newValue.length == 2) {
                        this.form.start_time = newValue[0]
                        this.form.end_time = newValue[1]
                    }else{
                        this.form.start_time = ''
                        this.form.end_time = ''
                    }
                }
            },
            filters: {
                formatTime:function(timestamp) {
                    var date = new Date();
                    date.setTime(parseInt(timestamp) * 1000);
                    return moment(date).format('YYYY-MM-DD HH:mm')
                }
            },
            methods: {
                getList: function () {
                    var that = this;
                    var where = {
                        page: this.pagination.page,
                        limit: this.pagination.limit,
                        uid: this.form.uid,
                        ip: this.form.ip,
                        status: this.form.status,
                        start_time: this.form.start_time,
                        end_time: this.form.end_time,
                        sort_time: this.form.sort_time,
                        _action: 'getList'
                    };
                    $.ajax({
                        url: "{:api_url('/admin/Logs/adminOperationLogList')}",
                        type: "get",
                        dataType: "json",
                        data: where,
                        success: function (res) {
                            var data = res.data;
                            that.pagination.page = data.page;
                            that.pagination.limit = data.limit;
                            that.pagination.total_pages = data.total_pages;
                            that.pagination.total_items = data.total_items;
                            that.tableData = data.items
                        }
                    });
                },
                deleteLog: function () {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/admin/Logs/deleteAdminOperationLog')}",
                        data: {
                            day: 30
                        },
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            if (res.status) {
                                ELEMENT.Message.success(res.msg)
                            } else {
                                ELEMENT.Message.error(res.msg)
                            }
                            that.getList()
                        }
                    })
                },
                handleClickTabs: function (tab) {
                    if (tab.index == 0) {
                        this.form.status = '';
                    }
                    if (tab.index == 1) {
                        this.form.status = '1';
                    }
                    if (tab.index == 2) {
                        this.form.status = '0';
                    }
                    this.getList()
                },
                handleSortChange: function (event) {
                    if (event.prop == 'time' && event.order != null) {
                        if (event.order.toLowerCase().indexOf('asc') >= 0) {
                            this.form.sort_time = 'asc'
                            this.getList();
                            return;
                        } else if (event.order.toLowerCase().indexOf('desc') >= 0) {
                            this.form.sort_time = 'desc'
                            this.getList();
                            return;
                        }
                    }
                    this.form.sort_time = '';
                    this.getList();
                },

                // 预览内容
                previewContent: function (content){
                    try{
                        content = JSON.stringify(JSON.parse(content), null, 2);
                    }catch (e) {}
                    layer.open({
                        type: 1,
                        area: ['500px', '300px'],
                        title: '查看',
                        content: '<pre style="padding: 4px 8px">' + content + '</pre>'
                    });
                },
                //设置操作记录开关
                setOperationRecord: function () {
                    var that = this;
                    var url = "{:api_url('/admin/Logs/setOperationLog')}?switch=" + that.admin_operation_switch;
                    layer.open({
                        type: 2,
                        title: '设置',
                        shadeClose: true,
                        area: ['50%', '60%'],
                        content: url,
                        end: function (res) {}
                    });
                }
            },
            mounted: function () {
                this.getList();
            }
        })
    })
</script>