<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="24">
            <!--                插入template 文件-->

            <div id="app" style="padding-left: 20px;padding-top: 20px;" v-cloak>

                <div class="filter-container">
                    <div style="margin-bottom: 15px;">
                        <el-button @click="detail('0')" size="small" type="success">
                            添加
                        </el-button>
                    </div>
                </div>


                <el-table
                        style="margin-bottom: 30px;"
                        :data="accessGroupList"
                        highlight-current-row
                        style="width: 100%;"
                >
                    <el-table-column label="ID" align="center">
                        <template slot-scope="scope">
                            <span>{{ scope.row.id }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="名称" align="">
                        <template slot-scope="scope">
                            <template v-for="i in scope.row.level * 4"><span>&nbsp;</span></template>
                            |—
                            <span>{{ scope.row.name }}</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="启用" width="150px" align="center">
                        <template slot-scope="{row}">
                            <span v-if="row.status == '1'">启用</span>
                            <span v-else>关闭</span>
                        </template>
                    </el-table-column>

                    <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
                        <template slot-scope="scope">
                            <el-button type="primary" size="mini" @click="detail(scope.row.id)">修改</el-button>
                            <el-button type="danger" size="mini" @click="clickDelteItem(scope.row.id)">删除</el-button>
                        </template>
                    </el-table-column>
                </el-table>


            </div>
        </el-col>
    </el-card>
</div>

<script>
    $(document).ready(function () {
        var App = new Vue({
            el: '#app',
            data: {
                accessGroupList: []
            },
            computed: {
                selectedItems: function () {
                    var result = [];
                    if (this.accessGroupList) {
                        this.accessGroupList.forEach(function (item) {
                            if (item.checked) {
                                result.push(item);
                            }
                        });
                    }
                    return result;
                }
            },
            methods: {
                fetchData: function () {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('Admin/AccessGroup/getAccessGroupList')}",
                        type: "get",
                        dataType: "json",
                        success: function (res) {
                            if (res.status) {
                                res.data.forEach(function (item, index) {
                                    res.data[index]['checked'] = false;
                                });
                                that.accessGroupList = res.data;
                            } else {
                                layer.msg('操作繁忙，请稍后再试')
                            }
                        }
                    })
                },
                confirmSelect: function () {
                    if (parent.window.selectAccessGroupListCallback) {
                        parent.window.selectAccessGroupListCallback(this.selectedItems)
                    }
                    parent.window.layer.closeAll();
                },
                selectAll: function () {
                    var that = this;
                    that.accessGroupList.forEach(function (item, index) {
                        that.accessGroupList[index]['checked'] = true;
                    });
                },
                unSelectAll: function () {
                    var that = this;
                    that.accessGroupList.forEach(function (item, index) {
                        that.accessGroupList[index]['checked'] = false;
                    });
                },
                clickDelteItem: function (group_id) {
                    var that = this;
                    layer.confirm('确认要删除?', function () {
                        $.ajax({
                            url: "{:api_url('/Admin/AccessGroup/deleteAccessGroup')}",
                            type: "post",
                            data: {
                                group_id: group_id
                            },
                            dataType: "json",
                            success: function (res) {
                                if (res.status) {
                                    layer.msg('操作成功');
                                    that.fetchData();
                                } else {
                                    layer.msg('操作繁忙，请稍后再试')
                                }
                            }
                        })
                    });
                },
               detail: function (id) {
                    var that = this;
                    var url = '{:api_url("/Admin/AccessGroup/accessGroupDetails")}';
                   if(id) url += '&id='+id;
                    layer.open({
                        type: 2,
                        title: '管理',
                        content: url,
                        area: ['95%', '95%'],
                        end: function(){
                            that.fetchData();
                        }
                    });
                }
            },
            mounted: function () {
                this.fetchData();
            }
        })
    });
</script>

<link href="/statics/css/admin_style.css" rel="stylesheet"/>
<style>

</style>


