<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <div class="filter_container">
            <el-row :gutter="10">
                <el-col :span="3">
                    <el-input v-model="form.user_name" placeholder="用户名称" size="medium"/>
                </el-col>
                <el-col :span="3">
                    <el-input v-model="form.ip" placeholder="IP" size="medium"/>
                </el-col>
                <el-col :span="3" v-if="this.form.type == ''">
                    <el-input v-model="form.type" placeholder="来源分类" size="medium"/>
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
                </el-col>
            </el-row>
        </div>
        <el-tabs>
            <el-table
                    size="medium"
                    :data="tableData"
                    style="width: 100%;"
                    @sort-change="handleSortChange">
                <el-table-column
                        prop="user_name"
                        label="用户名称">
                </el-table-column>
                <el-table-column
                        prop="source_type"
                        label="来源分类">
                </el-table-column>
                <el-table-column
                        prop="source"
                        label="来源">
                </el-table-column>
                <el-table-column
                        prop="content"
                        label="操作内容">
                </el-table-column>
                <el-table-column
                        prop="time"
                        label="操作时间">
                    <template slot-scope="scope">
                        <span>{{ scope.row.create_time }}</span>
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
                    user_id: '',
                    user_name: '',
                    ip: '',
                    start_time: '',
                    end_time: '',
                    sort_time: '',//排序：时间
                    source_type: '{:input("get.source_type", "")}',
                    source: '{:input("get.source", "")}',
                    page: 1,
                    limit: 15,
                },
            },
            watch: {
                'form.search_date': function (newValue) {
                    if (newValue && newValue.length == 2) {
                        this.form.start_time = newValue[0]
                        this.form.end_time = newValue[1]
                    } else {
                        this.form.start_time = ''
                        this.form.end_time = ''
                    }
                }
            },
            filters: {
                formatTime: function (timestamp) {
                    return timestamp
                    var date = new Date();
                    date.setTime(parseInt(timestamp) * 1000);
                    return moment(date).format('YYYY-MM-DD HH:mm')
                }
            },
            methods: {
                getList: function () {
                    var that = this
                    var where = this.form
                    where['_action'] = 'getList';
                    $.ajax({
                        url: "{:api_url('/admin/Logs/userOperateLog')}",
                        type: "POST",
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
            },
            mounted: function () {
                this.form.source_type = this.getUrlQuery('source_type');
                this.form.source = this.getUrlQuery('source');
                this.getList();
            }
        })
    })
</script>