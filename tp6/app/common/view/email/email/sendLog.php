<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <div class="filter_container">
            <el-row :gutter="10">

                <el-col :span="3">
                    <el-input v-model="form.from_email" placeholder="发件邮箱" size="medium"/>
                </el-col>

                <el-col :span="3">
                    <el-input v-model="form.to_mail" placeholder="收件邮箱" size="medium"/>
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

                    <el-button type="primary" @click="toSendEmail" size="medium">
                        发送邮件
                    </el-button>
                </el-col>
            </el-row>
        </div>

        <el-tabs @tab-click="handleClickTabs">
            <el-tab-pane label="全部"></el-tab-pane>
            <el-tab-pane label="成功"></el-tab-pane>
            <el-tab-pane label="失败"></el-tab-pane>
        </el-tabs>

        <el-table
            size="medium"
            :data="tableData"
            style="width: 100%;">
            <el-table-column
                prop="id"
                label="ID"
                width="80">
            </el-table-column>
            <el-table-column
                prop="from_email"
                label="发件邮箱"
                width="180">
            </el-table-column>
            <el-table-column
                prop="to_email"
                label="收件邮箱"
                width="180">
            </el-table-column>
            <el-table-column
                prop="subject"
                label="标题"
                width="180">
            </el-table-column>

            <el-table-column
                prop="send_time"
                label="发送时间"
                width="200"
            >
                <template slot-scope="scope">
                    <span>{{ scope.row.send_time | formatTime }}</span>
                </template>
            </el-table-column>
            <el-table-column
                prop="status"
                label="发送状态"
                width="80">
                <template slot-scope="scope">
                    <span v-if="scope.row.status == 1" style="color: green">成功</span>
                    <span v-else style="color: red">失败</span>
                </template>
            </el-table-column>
            <el-table-column
                prop="error_msg"
                label="错误信息"
                >
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

    </el-card>
</div>

<style>
    .filter_container {
        background: #f8f8f8;
        padding: 25px 36px 12px;
    }

</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                request_url: "{:api_url('/common/email.Email/sendLog')}",
                tableData: [],
                pagination: {
                    page: 1,
                    limit: 15,
                    total_pages: 0,
                    total_items: 0
                },
                form: {
                    search_date: [],
                    start_time: '',
                    end_time: '',
                    status: '',
                    from_email: '',
                    to_email: ''
                }
            },
            watch: {
                'form.search_date': function (newValue) {
                    if (newValue && newValue.length == 2) {
                        this.form.start_time = newValue[0];
                        this.form.end_time = newValue[1]
                    }else{
                        this.form.start_time = '';
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
                    var that = this
                    var where = {
                        page: this.pagination.page,
                        limit: this.pagination.limit,
                        to_email: this.form.to_email,
                        from_email: this.form.from_email,
                        status: this.form.status,
                        start_time: this.form.start_time,
                        end_time: this.form.end_time,
                        _action : 'getList'
                    }
                    this.httpGet(this.request_url, where, function (res) {
                        var data = res.data;
                        that.pagination.page = data.page;
                        that.pagination.limit = data.limit;
                        that.pagination.total_pages = data.total_pages;
                        that.pagination.total_items = data.total_items;
                        that.tableData = data.items
                    })
                },
                handleClickTabs:function(tab) {
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
                toSendEmail: function(){
                    var that = this
                    layer.open({
                        type: 2,
                        title: '发送邮件',
                        content: "{:api_url('/common/email.Email/sendEmail')}",
                        area: ['670px', '550px'],
                        end: function(){
                            that.getList()
                        }
                    })
                }
            },
            created: function () {
                this.getList();
            }
        })
    })
</script>

