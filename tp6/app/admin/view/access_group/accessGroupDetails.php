<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="100px">

                        <h3>权限组设置</h3>

                        <el-form-item label="名称" prop="name">
                            <el-input v-model="info.name" placeholder="请输入名称" clearable
                                      :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="父级权限组" prop="name">
                            <el-select v-model="info.parentid" placeholder="请选择" :style="{width: '100%'}">
                                <el-option :value="default_parentid" label="无"></el-option>
                                <el-option
                                        v-for="item in accessGroupTreeArray"
                                        :key="item.value"
                                        :label="item.view_name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>

                        <el-form-item label="备注" prop="description">
                            <el-input v-model="info.description" placeholder="请输入备注" clearable
                                      :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="状态" prop="status">
                            <el-radio v-model="info.status" label="1">启用</el-radio>
                            <el-radio v-model="info.status" label="0">禁止</el-radio>
                        </el-form-item>

                        <el-form-item label="">
                            <el-button type="primary" style="margin-bottom: 20px;" @click="submitForm">保存</el-button>
                        </el-form-item>


                        <div v-if="info.id != ''">
                            <h3> 权限列表 </h3>
                            <el-button v-if="info.id != ''" type="primary" size="mini" @click="clickSelectAccessList">添加权限</el-button>
                            <el-button v-if="info.id != ''" type="primary" size="mini"  @click="clickSave">保存权限列表</el-button>

                            <el-table
                                    :data="accessGroupItems"
                                    highlight-current-row
                                    style="width: 100%;"
                            >

                                <el-table-column label="名称" align="center">
                                    <template slot-scope="scope">
                                        <span>{{ scope.row.name }}</span>
                                    </template>
                                </el-table-column>

                                <el-table-column label="模块" align="">
                                    <template slot-scope="scope">
                                        <span>{{ scope.row.app }}</span>
                                    </template>
                                </el-table-column>

                                <el-table-column label="控制器" align="">
                                    <template slot-scope="scope">
                                        <span>{{ scope.row.controller }}</span>
                                    </template>
                                </el-table-column>

                                <el-table-column label="方法" align="">
                                    <template slot-scope="scope">
                                        <span>{{ scope.row.action }}</span>
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
            data: function () {
                return {
                    info: {
                        id: "{$info.id}",
                        name: "{$info.name}",
                        description: "{$info.description}",
                        status: "{$info.status}",
                        parentid: "{$info.parentid}" * 1
                    },
                    default_parentid : '0' * 1,
                    formData: {},
                    rules: {},
                    accessGroupTreeArray: [],
                    accessGroupItems: [],
                }
            },
            computed: {
                selectedItemIds: function () {
                    var that = this;
                    var ids = [];
                    if (that.accessGroupItems) {
                        that.accessGroupItems.forEach(function (item) {
                            ids.push(item['access_id'])
                        })
                    }
                    return ids;
                }
            },
            watch: {},
            created: function () {
            },
            mounted: function () {
                this.getAccessGroupTreeArray();
                this.fetchData();
            },
            methods: {
                fetchData: function () {
                    var that = this;
                    if (that.info.id) {
                        $.ajax({
                            url: "{:api_url('/admin/AccessGroup/getAccessGroupById')}" + '?id=' + that.info.id,
                            type: "get",
                            dataType: "json",
                            success: function (res) {
                                if (res.status) {
                                    that.id = res.data.id;
                                    that.name = res.data.name;
                                    that.parentid = res.data.parentid;
                                    that.description = res.data.description;
                                    that.status = res.data.status;
                                    that.accessGroupItems = res.data.accessGroupItems;
                                }
                            }
                        })
                    }
                },
                submitForm: function () {
                    var that = this;
                    var data = {
                        id: that.info.id,
                        name: that.info.name,
                        parentid: that.info.parentid,
                        description: that.info.description,
                        status: that.info.status
                    };
                    var req_url = "{:api_url('/admin/AccessGroup/doCreateAccessGroup')}";
                    $.ajax({
                        url: req_url,
                        type: "post",
                        dataType: "json",
                        data: data,
                        success: function (res) {
                            if (res.status) {
                                layer.msg('操作成功！');
                                setTimeout(function () {
                                    parent.window.layer.closeAll();
                                }, 1000)
                            } else {
                                layer.msg('操作繁忙，请稍后再试')
                            }
                        }
                    })
                },
                getAccessGroupTreeArray: function () {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/admin/AccessGroup/getAccessGroupTreeArray')}",
                        type: "post",
                        dataType: "json",
                        data: {},
                        success: function (res) {
                            if (res.status) {
                                that.accessGroupTreeArray = res.data;
                            }
                        }
                    });
                },
                deleteItem: function(index, item){
                    this.accessGroupItems.splice(index, 1);
                },
                clickSelectAccessList: function(){
                    layer.open({
                        type: 2,
                        title: '操作',
                        shadeClose: true,
                        shade: 0.8,
                        area: ['70%', '70%'],
                        content: "{:api_url('/admin/AccessGroup/accessList')}" + '?selected_ids=' + this.selectedItemIds.join(',')
                    });
                },
                updateSelectAccessList: function(accessList){
                    var that = this;
                    if(accessList){
                        accessList.filter(
                            function(element,index, self){
                                for(var j in that.accessGroupItems) {
                                    if(that.accessGroupItems[j]['access_id'] == element['access_id']){
                                        return;
                                    }
                                }
                                that.accessGroupItems.push({
                                    group_id: that.info.id,
                                    app: element.app,
                                    controller: element.controller,
                                    action: element.action,
                                    name: element.name,
                                    access_id: element.id
                                });
                            });
                    }
                },
                clickSave: function(){
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/admin/AccessGroup/doSaveAccessGroupItem')}",
                        type: "post",
                        dataType: "json",
                        data: {
                            group_id: that.info.id,
                            accessGroupItems: that.accessGroupItems,
                        },
                        success: function(res){
                            if(res.status){
                                layer.msg('操作成功！');
                                setTimeout(function(){
                                    window.location.reload();
                                }, 700)
                            }else{
                                layer.msg('操作繁忙，请稍后再试')
                            }
                        }
                    });
                }
            }
        });
    });

    //选择权限回调
    function selectAccessListCallback(accessList){
        if(accessList){
            window.App.updateSelectAccessList(accessList)
        }
    }
</script>

<style>

</style>
