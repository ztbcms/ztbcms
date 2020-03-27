<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
                <div class="filter-container">
                    <h3>管理员列表</h3>
            </div>
            <el-table
                    :key="tableKey"
                    :data="Manager"
                    highlight-current-row
                    style="width: 100%;"
            >
                <el-table-column label="ID" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.id }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="用户名" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.username }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="所属角色" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.role_name }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="最后登录IP" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.last_login_ip }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="最后登录时间" prop="input_time" align="">
                    <template slot-scope="scope" >
                        <span v-if="scope.row.last_login_time != 0">{{ scope.row.last_login_time }}</span>
                        <span v-else>该用户还没登录过</span>
                    </template>
                </el-table-column>
                <el-table-column label="E-mail" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.email }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="状态" align="center">
                    <template slot-scope="scope">
                        <div v-if="scope.row.status == 1" class="el-icon-success"
                             style="color: #409EFF;font-size: 1.5rem"></div>
                        <div v-else class="el-icon-error" style="font-size: 1.5rem"></div>
                    </template>
                </el-table-column>
                <el-table-column label="备注" prop="input_time" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.remark }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <el-button type="primary" size="mini" @click="openDetail(scope.row.id)" >修改</el-button>
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
                    input_date: ['', ''],
                    listQuery: {
                        page: 1,
                        limit: 20,
                        total: 0
                    },
                    Manager:[],
                    role_id:"{:I('get.role_id')}"
                },
                watch: {},
                filters: {},
                methods: {
                    openDetail: function (id) {
                        var that = this
                        var url = "{:U('Admin/Management/adminadd')}";
                        if (id !== 0) {
                            url += "&id=" + id;
                        }
                        layer.open({
                            type: 2,
                            title: '编辑',
                            content: url,
                            area: ['100%', '100%'],
                            end: function(){
                                that.getList()
                            }
                        })
                    },
                    getList: function () {
                        var that = this;
                        $.ajax({
                            url:"{:U('getManager')}",
                            type: "get",
                            dataType:"json",
                            data:{
                                page: that.listQuery.page,
                                limit: that.listQuery.limit,
                            },
                            success:function (res) {
                                if(res.status){
                                    that.Manager = res.data.items
                                    that.listQuery.total = res.data.total_items
                                    that.listQuery.page = res.data.page
                                    that.listQuery.limit = res.data.limit
                                }
                            }
                        })
                    },
                    handleDelete: function (index) {
                        var that = this;
                        layer.confirm('是否确定删除吗？', {
                            btn: ['确认', '取消'] //按钮
                        }, function () {
                            that.doDelete(index);
                            layer.closeAll();
                        }, function () {
                            layer.closeAll();
                        });
                    },
                    // 删除管理员
                    doDelete(id){
                        var that = this;
                        $.ajax({
                            url:"{:U('delete')}",
                            type: "get",
                            data:{
                                "id":id
                            },
                            dataType:"json",
                            success:function (res) {
                                if(res.status){
                                    that.$message.success(res.msg)
                                    that.getList();
                                }else{
                                    that.$message.error(res.msg)
                                }
                            }
                        })
                    },
                },
                mounted: function () {
                    this.getList();
                },
            })
        })
    </script>
</block>