<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <div class="filter-container">
                <h3>行为日志</h3>
            </div>

            <div class="filter-container">
                <div style="margin-bottom: 15px;">

                    <el-select size="small" v-model="listQuery.type" style="width: 250px;" placeholder="请选择">
                        <el-option label="行为ID" value="ruleid"></el-option>
                        <el-option label="标识" value="guid"></el-option>
                    </el-select>

                    <el-input size="small" v-model="listQuery.keyword" placeholder="关键词"
                              style="width: 250px;" class="filter-item">
                    </el-input>

                    <el-button @click="doSearch" size="small" type="primary" icon="el-icon-search">
                        搜索
                    </el-button>

                    <el-button @click="handleDelete" size="small" type="danger">
                        删除一月前数据
                    </el-button>
                </div>
            </div>

            <el-table
                    :key="tableKey"
                    :data="list"
                    highlight-current-row
                    style="width: 100%;"
            >
                <el-table-column label="ID" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.id }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="行为ID" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.ruleid }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="标识" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.guid }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="时间" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.create_time  }}</span>
                    </template>
                </el-table-column>
            </el-table>

            <div class="pagination-container">
                <el-pagination
                        background
                        layout="prev, pager, next, jumper"
                        :total="listQuery.total"
                        v-show="listQuery.total > 0"
                        :current-page.sync="listQuery.page"
                        :page-size.sync="listQuery.limit"
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
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    tableKey: 0,
                    list: [],
                    total: 0,
                    listQuery: {
                        page: 1,
                        limit: 20,
                        total: 0,
                        type : 'ruleid',
                        keyword: ''
                    }
                },
                watch: {},
                filters: {},
                methods: {
                    getList: function () {
                        var that = this;
                        $.ajax({
                            url: "{:U('logs')}",
                            type: "get",
                            dataType: "json",
                            data: that.listQuery,
                            success: function (res) {
                                if (res.status) {
                                    that.list = res.data.items;
                                    that.listQuery.total = res.data.total_items;
                                    that.listQuery.page = res.data.page;
                                    that.listQuery.limit = res.data.limit;
                                }
                            }
                        })
                    },
                    doSearch: function () {
                        var that = this;
                        that.listQuery.page = 1;
                        that.getList();
                    },
                    handleDelete: function () {
                        var that = this;
                        var url = '{:U("Logs/deletelog")}';
                        layer.confirm('您确定需要删除？', {
                            btn: ['确定','取消'] //按钮
                        }, function(){
                            var data = {};
                            that.httpPost(url, data, function(res){
                                if(res.status){
                                    layer.msg('操作成功', {icon: 1});
                                    that.getList();
                                }
                            });
                        });
                    },
                },
                mounted: function () {
                    this.getList();
                },
            })
        })
    </script>
</block>