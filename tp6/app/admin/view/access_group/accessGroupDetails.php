<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="100px">

                        <h3> 角色信息 </h3>

                        <el-form-item label="名称" prop="name">
                            <el-input v-model="info.name" placeholder="请输入名称" clearable
                                      :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="父级权限组" prop="name">
                            <el-select v-model="info.parentid" placeholder="请选择">
                                <el-option value="0">无</el-option>
                                <el-option
                                    v-for="item in accessGroupTreeArray"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="备注" prop="remark">
                            <el-input v-model="info.remark" placeholder="请输入名称" clearable
                                      :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="状态" prop="status">
                            <el-radio v-model="info.status" label="1">启用</el-radio>
                            <el-radio v-model="info.status" label="0">禁止</el-radio>
                        </el-form-item>

                        <el-button type="primary" style="margin-bottom: 20px;" @click="submitForm">保存</el-button>

                        <div v-if="info.id != ''">
                            <h3> 权限组 </h3>

                            <el-table
                                    :data="accessGroupList"
                                    highlight-current-row
                                    style="width: 100%;"
                            >

                                <el-table-column label="权限组ID" align="center">
                                    <template slot-scope="scope">
                                        <span>{{ scope.row.group_id }}</span>
                                    </template>
                                </el-table-column>

                                <el-table-column label="权限组名称" align="">
                                    <template slot-scope="scope">
                                        <span>{{ scope.row.group_name }}</span>
                                    </template>
                                </el-table-column>

                                <el-table-column label="操作" align="center" width="530"
                                                 class-name="small-padding fixed-width">
                                    <template slot-scope="scope">

                                        <el-button type="danger" class="itembtn" size="mini"
                                                   @click="deleteItem(scope.$index, scope.row)">
                                            删除
                                        </el-button>

                                    </template>
                                </el-table-column>
                            </el-table>

                            <el-form-item size="large" style="margin-top: 15px;">
                                <el-button type="primary" @click="clickSelectAccessList">添加权限组</el-button>
                            </el-form-item>
                        </div>


                    </el-form>
                </div>
            </template>
        </el-col>
    </el-card>
</div>

<script>
    $(document).ready(function () {
        window.App = new Vue({
            el: '#app',
            // 插入export default里面的内容
            components: {},
            props: [],
            data() {
                return {
                    info: {
                        id: "{$info.id}",
                        name: "{$info.name}",
                        remark: "{$info.remark}",
                        status: "{$info.status}"
                    },
                    formData: {},
                    rules: {},
                    accessGroupList: [],
                    accessGroupTreeArray : []
                }
            },
            computed: {

                selectedItemIds: function () {
                    var that = this;
                    var ids = [];
                    if (that.accessGroupList) {
                        that.accessGroupList.forEach(function (item) {
                            ids.push(item['group_id'])
                        })
                    }
                    return ids;
                }

            },
            watch: {},
            created() {
            },
            mounted() {
                this.fetchData();
            },
            methods: {
                fetchData: function () {
                    var that = this;
                    if (that.info.id) {
                        $.ajax({
                            url: "{:api_url('/Admin/AccessGroup/getRoleAccessGroup')}&role_id=" + that.info.id,
                            type: "get",
                            dataType: "json",
                            success: function (res) {
                                if (res.status) {
                                    that.accessGroupList = res.data;
                                } else {
                                    layer.msg('操作繁忙，请稍后再试')
                                }
                            }
                        })
                    }
                },
                deleteItem: function (index, item) {
                    this.accessGroupList.splice(index, 1);
                },
                addAccessGroup: function (accessList) {
                    var that = this;
                    if (accessList) {
                        accessList.filter(
                            function (element, index, self) {
                                for (var j in that.accessGroupList) {
                                    if (that.accessGroupList[j]['group_id'] == element['id']) {
                                        return;
                                    }
                                }
                                that.accessGroupList.push({
                                    group_id: element.id,
                                    group_name: element.name,
                                    role_id: element.role_id,
                                    group_parentid: element.parentid
                                });
                            });
                    }
                },
                submitForm() {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/Admin/AccessGroup/doSaveAccessGroupRole')}",
                        type: "post",
                        dataType: "json",
                        data: {
                            role_id: that.info.id,
                            accessGroupList: that.accessGroupList,
                        },
                        success: function (res) {
                            if (res.status) {
                                layer.msg('操作成功！');
                                setTimeout(function () {
                                    window.parent.layer.closeAll();
                                }, 1000);
                            } else {
                                layer.msg('操作繁忙，请稍后再试')
                            }
                        }
                    });
                },
                clickSelectAccessList: function () {
                    layer.open({
                        type: 2,
                        title: '权限',
                        shadeClose: true,
                        shade: 0.8,
                        area: ['70%', '70%'],
                        content: "{:api_url('/Admin/AccessGroup/selectAccessGroupList')}" + '&selected_ids=' + this.selectedItemIds
                    });
                },

            }
        });
    });

    //选择权限回调
    function selectAccessGroupListCallback(accessGroupList) {
        if (accessGroupList) {
            window.App.addAccessGroup(accessGroupList)
        }
    }
</script>

<style>

</style>
