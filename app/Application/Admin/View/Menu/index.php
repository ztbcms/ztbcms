
<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <div class="filter-container">
                <h3>后台菜单</h3>
            </div>


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
                        <span>{{ scope.row.name }}  | {{scope.row.app}}/{{scope.row.controller}}/{{scope.row.action}}</span>
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
                        <el-button type="text" size="mini" @click="linkMenuAdd(scope.row.id)">添加子菜单</el-button>
                        <el-button type="text" size="mini" @click="linkMenuEdit(scope.row.id)">修改</el-button>
                        <el-button type="text" size="mini" @click="handleDelete(scope.row.id)">删除</el-button>
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
                            url: "{:U('indexAjax')}",
                            type: "get",
                            dataType: "json",
                            data: that.listQuery,
                            success: function (res) {
                                if (res.status) {
                                    that.list = res.data;
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
                        var url = '{:U("Menu/delete")}';
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
                                } else {
                                    layer.msg(res.info);
                                }
                            });
                        });
                    },
                    updateShow: function(id, value){
                        var that = this;
                        var url = '{:U("Menu/updateTable")}';
                        var data = {
                            field: 'status',
                            value: value, where_name : 'id', where_value : id
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
                    updateSort: function(id, sort){
                        var that = this;
                        that.$prompt('请输入排序', {
                            confirmButtonText: '保存',
                            cancelButtonText: '取消',
                            inputValue: sort,
                            roundButton: true,
                            closeOnClickModal: false,
                            beforeClose: function(action, instance, done){
                                if(action == 'confirm'){
                                    var url = '{:U("Menu/updateTable")}';
                                    var data = {
                                        field: 'listorder',where_name: 'id',
                                        value: instance.inputValue, where_value: id
                                    };
                                    that.httpPost(url, data, function(res){
                                        if(res.status){
                                            that.$message.success('修改成功');
                                            that.getList();
                                            done();
                                        }
                                    });
                                }else{
                                    done();
                                }
                            }
                        }).then(function(e){}).catch(function(){});
                    },
                    linkMenuAdd: function (id) {
                        Ztbcms.openNewIframeByUrl('添加子菜单', '/index.php?g=Admin&m=Menu&a=add&parentid='+ id)
                    },
                    linkMenuEdit:function (id) {
                        Ztbcms.openNewIframeByUrl('编辑菜单', '/index.php?g=Admin&m=Menu&a=edit&id='+ id)
                    }
                },
                mounted: function () {
                    this.getList();
                },
            })
        })
    </script>
</block>



<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form class="J_ajaxForm" action="{:U('Menu/index')}" method="post">
    <div class="table_list">
      <table width="100%">
        <thead>
          <tr>
            <td align="center">排序</td>
            <td align="center">ID</td>
            <td >菜单名称</td>
            <td width="140">备注</td>
            <td align="center">状态</td>
            <td align="center">管理操作</td>
          </tr>
        </thead>
        {$categorys}
      </table>
      <div class="p10"><div class="pages"> {$Page} </div> </div>
     
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">排序</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
