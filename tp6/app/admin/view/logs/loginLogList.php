<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <div class="filter_container">
            <el-row :gutter="10">
                <el-col :span="3">
                    <el-input v-model="form.username" placeholder="用户名称" size="medium"/>
                </el-col>
                <el-col :span="3">
                    <el-input v-model="form.loginip" placeholder="IP" size="medium"/>
                </el-col>

                <el-col :span="4">
                    <el-date-picker
                            v-model="form.search_date"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            type="datetimerange"
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

                    <el-button @click="deletelog" type="primary" plain size="medium">
                        删除一个月前的操作日志
                    </el-button>
                </el-col>
            </el-row>
        </div>


        <el-tabs type="border-card" style="margin-top: 20px;"
                 @tab-click="handleClickTabs">
            <el-tab-pane label="全部"></el-tab-pane>
            <el-tab-pane label="成功"></el-tab-pane>
            <el-tab-pane label="失败"></el-tab-pane>

            <el-table
                    size="medium"
                    :data="tableData"
                    border
                    style="width: 100%;"
                    @sort-change="handleSortChange">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="username"
                        label="用户名"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="password"
                        label="密码"
                        width="80">
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
                        prop="info"
                        label="其他说明">
                    <template slot-scope="scope">
                        <p v-html="scope.row.info"></p>
                    </template>
                </el-table-column>

                <el-table-column
                        prop="time"
                        label="时间"
                        width="200"
                        sortable="custom"
                >
                    <template slot-scope="scope">
                        <span>{{ scope.row.logintime | formatTime }}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="loginip"
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
        background: #f8f8f8;
        padding: 25px 36px 12px;
    }

    .pager_container {

    }
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
                    search_date: [],
                    uid: '',
                    ip: '',
                    start_time: '',
                    end_time: '',
                    status: '',
                    sort_time: '',//排序：时间
                }
            },
            watch: {
                'form.search_date': function (newValue) {
                    if (newValue && newValue.length == 2) {
                        this.form.start_time = newValue[0];
                        this.form.end_time = newValue[1]
                    }
                }
            },
            filters: {
                formatTime(timestamp) {
                    var date = new Date();
                    date.setTime(parseInt(timestamp) * 1000);
                    return moment(date).format('YYYY-MM-DD HH:mm:ss')
                }
            },
            methods: {
                getList: function () {
                    var that = this;
                    var where = {
                        page: this.pagination.page,
                        limit: this.pagination.limit,
                        username: this.form.username,
                        loginip: this.form.loginip,
                        status: this.form.status,
                        start_time: this.form.start_time,
                        end_time: this.form.end_time,
                        sort_time: this.form.sort_time
                    };
                    $.ajax({
                        url: "{:api_url('/Admin/Logs/loginLogList')}",
                        data: where,
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            var data = res.data;
                            that.pagination.page = data.page;
                            that.pagination.limit = data.limit;
                            that.pagination.total_pages = data.total_pages;
                            that.pagination.total_items = data.total_items;
                            that.tableData = data.items
                        }
                    })
                },
                deletelog: function () {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/Admin/Logs/deleteLoginLog')}",
                        data: {},
                        dataType: 'json',
                        type: 'get',
                        success: function (res) {
                            if (res.status) {
                                ELEMENT.Message.success(res.msg)
                            } else {
                                ELEMENT.Message.error(res.msg)
                            }
                        }
                    })
                },
                handleClickTabs(tab) {
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
                handleSortChange(event) {
                    if (event.prop == 'time') {
                        if (event.order.toLowerCase().indexOf('asc') >= 0) {
                            this.form.sort_time = 'asc';
                            this.getList();
                            return;
                        } else if (event.order.toLowerCase().indexOf('desc') >= 0) {
                            this.form.sort_time = 'desc';
                            this.getList();
                            return;
                        }
                    }
                    this.form.sort_time = '';
                    this.getList();
                }
            },
            mounted: function () {
                this.getList();
            }
        })
    })
</script>

