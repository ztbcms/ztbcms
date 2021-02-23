
<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <div class="filter-container">
            <h3>后台菜单</h3>
        </div>

        <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('admin', 'menu', 'menuAdd')){ ?>
        <el-button class="filter-item" style="margin-left: 10px;margin-bottom: 15px;" size="small" type="primary" @click="details('')">
            添加菜单
        </el-button>
        <?php } ?>

        <el-table
                :key="tableKey"
                :data="list"
                highlight-current-row
                style="width: 100%;"
        >
            <el-table-column label="排序" width="80px" align="center">
                <template slot-scope="{row}">
                    {{ row.listorder }}
                    <i @click="updateSort(row.id, row.listorder)" class="el-icon-edit update-sort"></i>
                </template>
            </el-table-column>

            <el-table-column label="ID" align="center" width="100px">
                <template slot-scope="scope">
                    <span>{{ scope.row.id }}</span>
                </template>
            </el-table-column>

            <el-table-column label="菜单名称" align="">
                <template slot-scope="scope">
                    <template v-for="i in scope.row.level * 2"><span>&nbsp;</span></template>
                    <template v-if="scope.row.level > 0"><span> ∟</span></template>
                    <span>{{ scope.row.name }}  | {{scope.row.app}}/{{scope.row.controller}}/{{scope.row.action}}</span>
                </template>
            </el-table-column>

            <el-table-column label="权限菜单" width="100px" align="center">
                <template slot-scope="{row}">
                    <template v-if="row.type == 1">
                        <span style="color:green">是</span>
                    </template>
                    <template v-else>
                        <span style="color:gray">否</span>
                    </template>
                </template>
            </el-table-column>

            <el-table-column label="状态" width="100px" align="center">
                <template slot-scope="{row}">
                    <template v-if="row.status == 1">
                        <span style="color:green">显示</span>
                    </template>
                    <template v-else>
                        <span style="color:gray">不显示</span>
                    </template>
                </template>
            </el-table-column>

            <el-table-column label="操作" align="center" width="280" class-name="small-padding fixed-width">
                <template slot-scope="scope">

                    <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('admin', 'menu', 'menuAdd')){ ?>
                    <el-button type="text" size="mini" @click="linkMenuAdd(scope.row.id)">添加子菜单</el-button>
                    <?php } ?>

                    <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('admin', 'menu', 'menuEdit')){ ?>
                    <el-button type="text" size="mini" @click="details(scope.row.id)">修改</el-button>
                    <?php } ?>

                    <?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('admin', 'menu', 'menuDelete')){ ?>
                        <el-button type="text" size="mini" @click="handleDelete(scope.row.id)" style="color: #e74c3c;">删除</el-button>
                    <?php } ?>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination
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
                    var data = that.listQuery;
                    data._action = 'getMenuList';

                    that.httpGet("{:api_url('/admin/Menu/index')}", data, function(res){
                        if (res.status) {
                            that.list = res.data;
                        }
                    });
                },
                doSearch: function () {
                    var that = this;
                    that.getList();
                },
                handleDelete: function (index) {
                    var that = this;
                    var url = '{:api_url("/admin/Menu/menuDelete")}';
                    layer.confirm('您确定需要删除？', {
                        title: '提示',
                        btn: ['确定','取消'] //按钮
                    }, function(){
                        var data = {
                            "id": index
                        };
                        that.httpPost(url, data, function(res){
                            layer.closeAll()
                            if(res.status){
                                that.$message.success('删除成功');
                                that.getList();
                            } else {
                                that.$message.error(res.msg);
                            }
                        });
                    });
                },
                updateSort: function(id, sort){
                    var that = this;
                    that.$prompt('请输入排序', {
                        confirmButtonText: '保存',
                        cancelButtonText: '取消',
                        inputValue: sort,
                        closeOnClickModal: false,
                        beforeClose: function(action, instance, done){
                            if(action == 'confirm'){
                                var url = '{:api_url("/admin/Menu/index")}?_action=updateSort';
                                var data = {
                                    id: id,
                                    listorder: instance.inputValue
                                };
                                that.httpPost(url, data, function(res){
                                    if(res.status){
                                        that.$message.success('修改成功');
                                        that.getList();
                                    } else {
                                        that.$message.error(res.msg);
                                    }
                                    done();
                                });
                            }else{
                                done();
                            }
                        }
                    })
                },
                details : function (id) {
                    var that = this;
                    var url = '{:api_url("/admin/Menu/menuEdit")}';
                    if(id) url += '?id=' + id;
                    layer.open({
                        type: 2,
                        title: '菜单设置',
                        content: url,
                        area: ['95%', '95%'],
                        end: function(){
                            that.getList();
                        }
                    })
                },
                linkMenuAdd: function (parentid) {
                    var that = this;
                    var url = '{:api_url("/admin/Menu/menuAdd")}';
                    if(parentid) {
                        url += '?parentid=' + parentid
                    }
                    layer.open({
                        type: 2,
                        title: '添加子菜单',
                        content: url,
                        area: ['95%', '95%'],
                        end: function(){
                            that.getList();
                        }
                    })
                }
            },
            mounted: function () {
                this.getList();
            }
        })
    })
</script>