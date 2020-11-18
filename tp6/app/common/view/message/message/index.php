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
                    <el-input v-model="searchMessage.target" placeholder="来源"></el-input>
                </el-form-item>
                <el-form-item label="">
                    <el-input v-model="searchMessage.target_type" placeholder="来源类型"></el-input>
                </el-form-item>

                <br>

                <el-form-item label="">
                    <el-input v-model="searchMessage.sender" placeholder="发送者"></el-input>
                </el-form-item>
                <el-form-item label="">
                    <el-input v-model="searchMessage.sender_type" placeholder="发送者类型"></el-input>
                </el-form-item>
                <el-form-item label="">
                    <el-input v-model="searchMessage.receiver" placeholder="接收者"></el-input>
                </el-form-item>
                <el-form-item label="">
                    <el-input v-model="searchMessage.receiver_type" placeholder="接收者类型"></el-input>
                </el-form-item>

                <br>

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
                    label="ID"
                    min-width="60">
            </el-table-column>
            <el-table-column
                    min-width="80"
                    align="center"
                    label="消息源"
            >
                <template slot-scope="props">
                    <div>{{props.row.target}}</div>
                    <div>{{props.row.target_type}}</div>
                </template>
            </el-table-column>
            <el-table-column
                    min-width="80"
                    align="center"
                    label="发送者"
            >
                <template slot-scope="props">
                    <div>{{props.row.sender}}</div>
                    <div>{{props.row.sender_type}}</div>
                </template>
            </el-table-column>
            <el-table-column
                    min-width="80"
                    align="center"
                    label="接收者">
                <template slot-scope="props">
                    <div>{{props.row.receiver}}</div>
                    <div>{{props.row.receiver_type}}</div>
                </template>
            </el-table-column>
            <el-table-column
                    min-width="180"
                    align="center"
                    label="内容">
                <template slot-scope="props">
                    <div>
                        <strong>{{props.row.title}}</strong>
                    </div>
                    <div>
                        {{props.row.content}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                    align="center"
                    min-width="60"
                    label="处理次数/状态">
                <template slot-scope="props">
                    {{props.row.process_num}} / {{props.row.process_status}}
                </template>
            </el-table-column>
            <el-table-column
                    align="center"
                    min-width="180"
                    label="时间">
                <template slot-scope="props">
                    <div>
                        创建：{{props.row.create_time}}
                    </div>
                    <div>
                        发送：{{props.row.send_time?props.row.send_time:'-'}}
                    </div>
                </template>
            </el-table-column>
            <el-table-column
                    min-width="150"
                    align="center"
                    fixed="right"
                    label="操作">
                <template slot-scope="props">
                    <el-button @click="handMessage(props.row)" type="primary">执行</el-button>
                    <el-button @click="openDetail(props.row)" type="success">查看</el-button>
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
                searchForm: {
                    datetime: "",
                },
                searchMessage: {},
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
                handMessage: function (message) {
                    var _this = this;
                    var hander = function () {
                        $.ajax({
                            url: "{:api_url('/common/message.message/handMessage')}",
                            data: {message_id: message.id},
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                if (res.status) {
                                    layer.msg(res.msg);
                                    _this.getList();
                                } else {
                                    layer.msg(res.msg);
                                }
                            }
                        })
                    };
                    if (message.process_status === 1) {
                        this.$confirm('该消息已经处理完成，是否再次执行？').then(res => hander()).catch(err => {
                        })
                    } else {
                        hander();
                    }
                },
                openDetail: function (detail) {
                    this.$alert(JSON.stringify(detail));
                },
                search: function () {
                    this.currentPage = 1;
                    this.getList();
                },
                currentPageChange:function(e) {
                    this.currentPage = e;
                    this.getList();
                },
                getList: function () {
                    var _this = this;
                    $.ajax({
                        url: "{:api_url('common/message.message/getMessageList')}",
                        data: Object.assign({
                            search_message: this.searchMessage,
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
