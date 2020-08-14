<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <div class="filter-container">
                <h3>行为列表</h3>
            </div>

            <div class="filter-container">
                <div style="margin-bottom: 15px;">
                    <el-input size="small" v-model="listQuery.keyword" placeholder="行为标识"
                              style="width: 350px;" class="filter-item">
                    </el-input>

                    <el-button @click="doSearch" size="small" type="primary" icon="el-icon-search">
                        搜索
                    </el-button>
                </div>
            </div>

            <el-table
                    :key="tableKey"
                    :data="list"
                    highlight-current-row
                    style="width: 100%;"
            >
                <el-table-column label="编号" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.id }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="行为标识" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.name }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="行为名称" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.title }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="规则说明" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.remark }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="类型" align="">
                    <template slot-scope="scope">
                        <span v-if="scope.row.type === '1'">控制器</span>
                        <span v-if="scope.row.type === '2'">视图</span>
                    </template>
                </el-table-column>

                <el-table-column label="状态" width="150px" align="center">
                    <template slot-scope="{row}">
                        <el-switch @change="updateShow(row.id,row.status)" v-model="row.status" size="small" active-value="1" inactive-value="0">
                        </el-switch>
                    </template>
                </el-table-column>

                <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <el-button type="primary" size="mini" @click="openDetail(scope.row.id)">修改</el-button>
                        <el-button type="danger" size="mini" @click="handleDelete(scope.row.id)">删除</el-button>
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
                        keyword: ''
                    }
                },
                watch: {},
                filters: {},
                methods: {
                    getList: function () {
                        var that = this;
                        $.ajax({
                            url: "{:U('index')}",
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
                    handleDelete: function (index) {

                        var that = this;
                        var url = '{:U("Behavior/delete")}';
                        layer.confirm('您确定需要删除？', {
                            btn: ['确定','取消'] //按钮
                        }, function(){
                            var data = {
                                "id": index
                            };
                            that.httpPost(url, data, function(res){
                                if(res.status){
                                    layer.msg('操作成功', {icon: 1});
                                    that.getList();
                                }
                            });
                        });
                    },
                    updateShow: function(id, value){
                        var that = this;
                        var url = '{:U("Behavior/status")}';
                        var data = {
                            id: id
                        };
                        that.httpPost(url, data, function(res){
                            if(res.status){
                                that.$message.success('修改成功');
                                that.getList();
                            } else {
                                layer.msg(res.info);
                            }
                        });
                    },
                    openDetail: function (id) {
                        Ztbcms.openNewIframeByUrl('编辑', '/index.php?g=Admin&m=Behavior&a=edit&id='+ id)
                    }
                },
                mounted: function () {
                    this.getList();
                },
            })
        })
    </script>
</block>