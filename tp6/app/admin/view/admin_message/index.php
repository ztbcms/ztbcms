<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-row>
                <el-col :span="5"><h3>所有通知</h3></el-col>
                <el-col :span="19">
                    <div style="float: right">
                        <el-button class="filter-item" type="primary"  @click="readAll">
                            标记所有为已读
                        </el-button>

                        <el-button class="filter-item" plain @click="readNowPage">
                            标记本页为已读
                        </el-button>
                    </div>
                </el-col>
            </el-row>

            <el-table
                    :key="tableKey"
                    :data="tableData"
                    border
                    fit
                    highlight-current-row
                    style="width: 100%;"
            >
                <el-table-column label="" prop="id"  align="left" >
                    <template slot-scope="scope">
                        <div @click="read(scope.row.id)" style="padding-left: 10px;">
                            <i class="el-icon-s-opportunity" style="color: red;" v-if="scope.row.read_status == 0"></i>
                            <span>{{ scope.row.content | ellipsis }}</span>
                            <span style="float: right"><i class="el-icon-time"></i> {{ scope.row.create_time | parseTime('{m}-{d} {h}:{i}') }}</span>
                        </div>
                    </template>
                </el-table-column>

            </el-table>

            <div class="pagination-container ">
                <el-pagination
                        background
                        layout="total, prev, pager, next, jumper"
                        :total="total"
                        v-show="total > 0"
                        :current-page.sync="where.page"
                        :page-size.sync="where.limit"
                        @current-change="getList"
                >
                </el-pagination>
            </div>

        </el-card>
    </div>

    <style>
        .filter-container {
            padding-bottom: 10px;
        }

        .pagination-container {
            padding: 32px 16px;
        }

        .el-table__header{
            display: none;
        }
    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    tableKey: 0,
                    tableData: [],
                    total: 0,
                    where: {
                        page: 1,
                        limit: 20,
                        type: ""
                    },
                },
                watch: {},
                filters: {
                    parseTime: function (time,format) {
                        return Ztbcms.formatTime(time, format)
                    },
                    ellipsis(value) {
                        if (!value) return "";
                        if (value.length > 120) {
                            return value.slice(0, 120) + "...";
                        }
                        return value;
                    }
                },
                methods: {
                    getList: function () {
                        var that = this;
                        $.ajax({
                            url: "{:api_url('/Admin/AdminMessage/getAdminMsgList')}",
                            data: that.where,
                            type: 'get',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    that.tableData = res.data.items;
                                    that.total = res.data.total_items;
                                    that.where.page = res.data.page;
                                }
                            }
                        });
                    },
                    // 已读
                    read:function(id){
                        var that = this;
                        $.ajax({
                            url: "{:api_url('/Admin/AdminMessage/readMsg')}",
                            data: {
                                'ids': [id]
                            },
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    that.getList()
                                }
                            }
                        });
                    },
                    // 全部已读
                    readAll: function () {
                        var that = this;
                        layer.confirm('确定将所有的消息标记为已读？', {title:'提示'}, function(index){
                            that.doReadAll();
                            layer.close(index);
                        });
                    },
                    doReadAll:function(){
                        var that = this;
                        $.ajax({
                            url: "{:api_url('/Admin/AdminMessage/readMsgAll')}",
                            data: {},
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    that.getList()
                                }
                            }
                        });
                    },
                    // 本页已读
                    readNowPage: function () {
                        var that = this;
                        layer.confirm('确定将本页的消息标记为已读？', {title:'提示'}, function(index){
                            that.doReadNowPage()
                            layer.close(index);
                        });
                    },
                    // 本页已读
                    doReadNowPage:function(){
                        var that = this;
                        var ids  = [];
                        for(var item in that.tableData){
                            ids.push(that.tableData[item]['id']);
                        }
                        $.ajax({
                            url:  "{:api_url('/Admin/AdminMessage/readMsg')}",
                            data: {
                                ids:ids
                            },
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    that.getList()
                                }
                            }
                        });
                    }
                },
                mounted: function () {
                    this.getList();
                }
            })
        })
    </script>
</block>
