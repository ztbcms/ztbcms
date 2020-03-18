<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <div class="filter-container">
                    <h3>角色列表</h3>
            </div>
            <el-table
                :data="Manager"
                highlight-current-row
                style="width: 100%;"
            >
                <el-table-column label="ID" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.id }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="角色名称" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.name }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="角色描述" align="">
                    <template slot-scope="scope">
                        <span>{{ scope.row.remark }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="启用状态" align="center">
                    <template slot-scope="scope">
                        <div v-if="scope.row.status == 1" class="el-icon-success"
                             style="color: #409EFF;font-size: 1.5rem"></div>
                        <div v-else class="el-icon-error" style="font-size: 1.5rem"></div>
                    </template>
                </el-table-column>
                <el-table-column label="操作" align="center" width="530" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <span>
                        <el-button class="itembtn" size="mini" @click="getRoleAccessGroup(scope.row.id)" v-if="scope.row.id != 1">权限组设置</el-button>
                        <el-button class="itembtn"  size="mini" @click="openAuth(scope.row.id)"  :disabled="scope.row.id == 1">
                            权限设置
                        </el-button>
                            <el-button class="itembtn"size="mini" @click="gotoAdminPage(scope.row.id)" v-if="scope.row.id != 1">栏目权限</el-button>
                        <el-button class="itembtn"size="mini" @click="gotomanagerPage(scope.row.id)">成员管理</el-button>
                        <el-button class="itembtn"size="mini" @click="openDetail(scope.row.id)" :disabled="scope.row.id == 1">修改</el-button>
                        <el-button class="itembtn"size="mini" @click="handleDelete(scope.row.id)" :disabled="scope.row.id == 1">删除</el-button>
                        </span>


                    </template>

                </el-table-column>
            </el-table>

            <div class="pagination-container">
                <el-pagination
                    background
                    layout="prev, pager, next, jumper"
                    :total="listQuery.total"
                    v-show="listQuery.total>0"
                    :current-page.sync="listQuery.page"
                    :page-size.sync="listQuery.limit"
                    @current-change="getList"
                >
                </el-pagination>
            </div>

        </el-card>
    </div>

    <style>
        .itembtn{
            margin-top: 10px;

        }
        .el-button+.el-button{
            margin-left: 1px;
        }
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
                    listQuery: {
                        total: 0,
                        page: 1,
                        limit: 20,
                    },
                    Manager:[]
                },
                watch: {},
                filters: {},
                methods: {
                    search: function () {
                        this.getList();
                    },
                    //编辑功能未完善
                    openDetail: function (id) {
                        var that = this
                        var url = "{:U('Admin/Rbac/roleedit')}";
                        if (id !== 0) {
                            url += '&id=' + id
                        }
                        //直接打开新页面
                        layer.open({
                            type: 2,
                            title: '详情',
                            content: url,
                            area: ['100%', '100%'],
                            end:function () {
                                that.getList()
                            }
                        })
                    },
                    openAdd:function(){
                        var that = this
                        var url = "{:U('Admin/Rbac/roleadd')}";
                        //直接打开新页面
                        layer.open({
                            type: 2,
                            title: '添加角色',
                            content: url,
                            area: ['100%', '100%'],
                            end:function () {
                                that.getList()
                            }
                        })
                    },
                    //权限组管理
                    getRoleAccessGroup: function (id) {
                        var url = "{:U('Admin/AccessGroup/accessGroupRoleSetting')}";
                        url = url + '&roleid=' + id

                        layer.open({
                            type: 2,
                            title: '权限组管理',
                            content: url,
                            area: ['100%', '100%'],
                            end:function () {
                            }
                        })
                    },
                    //栏目管理
                    gotoAdminPage: function (id) {
                        var url = "{:U('Admin/Rbac/setting_cat_priv')}";
                        url = url + '&roleid=' + id

                        layer.open({
                            type: 2,
                            title: '栏目管理',
                            content: url,
                            area: ['100%', '100%'],
                            end:function () {
                            }
                        })
                    },
                    //成员管理
                    gotomanagerPage: function (id) {
                        var url = "{:U('Admin/Management/manager')}";
                        url = url + '&role_id=' + id

                        layer.open({
                            type: 2,
                            title: '成员管理',
                            content: url,
                            area: ['100%', '100%'],
                            end:function () {
                            }
                        })
                    },
                    openAuth:function(id){
                        var that = this
                        var url = "{:U('Admin/Rbac/authorize')}";
                        url += '&id=' + id;

                        layer.open({
                            type: 2,
                            title: '权限配置',
                            content: url,
                            area: ['100%', '100%'],
                            end:function () {
                                that.getList()
                            }
                        })
                    },
                    getList: function () {
                        var that = this;
                        $.ajax({
                            url:"{:U('getroleList')}",
                            type: "get",
                            dataType:"json",
                            data:that.listQuery,
                            success:function (res) {
                                if(res.status){
                                    that.Manager = res.data.items
                                    that.listQuery.total = res.data.total_items;
                                    that.listQuery.page = res.data.page;
                                }
                            }

                        })
                    },
                    handleClick: function () {
                        this.getList();
                    },
                    handleDelete: function (index) {
                        var that = this;
                        layer.confirm('是否确定删除该内容吗？', {
                            btn: ['确认', '取消'] //按钮
                        }, function () {
                            that.toDelete(index);
                            layer.closeAll();
                        }, function () {
                            layer.closeAll();
                        });
                    },
                    toDelete:function (id) {
                        var that = this;
                        $.ajax({
                            url:"{:U('roledelete')}",
                            type: "get",
                            data:{id:id},
                            dataType:"json",
                            success:function (res) {
                                if(res.status){
                                    that.$message.success(res.msg);
                                    that.getList();
                                } else {
                                    that.$message.error(res.msg);
                                }
                            }

                        })
                    }
                },
                mounted: function () {
                    this.getList();
                },
            })
        })
    </script>
</block>