<div id="app" v-cloak>
    <el-card>
        <div>
            <el-form :inline="true" :model="searchForm" class="demo-form-inline">
                <el-form-item label="">
                    <el-input v-model="searchForm.message_id" placeholder="消息id"></el-input>
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
                    label="ID"
                    min-width="60">
            </el-table-column>
            <el-table-column
                    min-width="80"
                    align="center"
                    prop="message_id"
                    label="消息ID"
            >
            </el-table-column>
            <el-table-column
                    min-width="300"
                    align="center"
                    prop="sender"
                    label="发送器"
            >
            </el-table-column>
            <el-table-column
                    min-width="80"
                    align="center"
                    label="状态">
                <template slot-scope="props">
                    <el-tag v-if="props.row.status == 1" type="success">成功</el-tag>
                    <el-tag v-else type="danger">失败</el-tag>
                </template>
            </el-table-column>
            <el-table-column
                    min-width="180"
                    align="center"
                    prop="result_msg"
                    label="结果">
            </el-table-column>
            <el-table-column
                    align="center"
                    min-width="180"
                    prop="create_time"
                    label="时间">
            </el-table-column>
            <el-table-column
                    min-width="100"
                    align="center"
                    fixed="right"
                    label="操作">
                <template slot-scope="props">
                    <el-button @click='handleAgain(props.row)' type="primary">再次执行</el-button>
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
                    message_id: "",
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
                handleAgain: function (record) {
                    var _this = this;
                    var hand = function () {
                        $.ajax({
                            url: "{:api_url('/common/message.message/handleAgainLog')}",
                            data: {log_id: record.id},
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
                        url: "{:api_url('/common/message.message/sendLog')}",
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
                }
            }
        });
    })
</script>
