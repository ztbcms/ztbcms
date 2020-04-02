<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <div class="filter-container">
                    <h3>角色列表</h3>
            </div>
            <table class="el-table el-table--fit el-table--enable-row-hover el-table--enable-row-transition">
                <thead class="has-gutter">
                <tr class="">
                    <th colspan="1" rowspan="1" class="el-table_2_column_1  is-center   is-leaf">
                        <div class="cell">ID</div>
                    </th>
                    <th colspan="1" rowspan="1" class="el-table_2_column_2   is-center  is-leaf">
                        <div class="cell">角色名称</div>
                    </th>
                    <th colspan="1" rowspan="1" class="el-table_2_column_3  is-center   is-leaf">
                        <div class="cell">角色描述</div>
                    </th>
                    <th colspan="1" rowspan="1" class="el-table_2_column_4  is-center   is-leaf">
                        <div class="cell">启用状态</div>
                    </th>
                    <th colspan="1" rowspan="1"
                        class="el-table_2_column_5  is-center small-padding fixed-width  is-leaf">
                        <div class="cell">操作</div>
                    </th>
                    <th class="gutter" style="width: 0px; display: none;"></th>
                </tr>
                </thead>
                <tbody>
                {$role}
                </tbody>
            </table>

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
        .el-table:before{
            background-color: #fff;
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
                        tab: '',
                        limit: 20,
                        start_time: '',
                        end_time: '',
                        user_name: '{$user_name}',
                        title: ''
                    },
                    Manager:[]
                },
                watch: {},
                filters: {},
                methods: {
                    //编辑角色
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
                                window.location.reload()
                            }
                        })
                    },
                    //添加角色
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
                                window.location.reload()
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
                                window.location.reload()
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
                                window.location.reload()
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
                                window.location.reload()
                            }
                        })
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
                                    window.location.reload()
                                } else {
                                    that.$message.error(res.msg);
                                }
                            }
                        })
                    }
                },
                mounted: function () {
                },
            })
        })
    </script>
</block>