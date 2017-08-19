<extend name="../../Admin/View/Common/base_layout"/>

<block name="content">
    <div id="app" style="padding-left: 20px;padding-top: 20px;" v-cloak>
        <form class="form-horizontal" action="" method="post">
            <h4>角色</h4>
            <hr>
            <div class="form-group">
                <label class="col-sm-1 control-label">角色</label>

                <div class="col-sm-5">
                    <input type="text" class="form-control" value="{$role['name']}" disabled>
                </div>
                <div class="col-sm-6">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-1 control-label">备注</label>

                <div class="col-sm-5">
                    <input type="text" class="form-control"  placeholder="" value="{$role['remark']}" disabled>
                </div>
                <div class="col-sm-6">
                </div>
            </div>
        </form>

        <h4>权限组</h4>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>名称</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-for="(item, index) in accessGroupList">
                        <tr>
                            <td>{{ item.group_name }}</td>
                            <td>
                                <button class="btn btn-danger" @click="deleteItem(index, item)">删除</button>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>
                <hr>
                <p>
                    <button class="btn btn-primary" @click="clickAddAccess">添加权限组</button>
                    <button class="btn btn-primary" @click="clickSave">保存</button>
                </p>

            </div>
        </div>

    </div>
    <script>
        $(document).ready(function(){
            window.App = new Vue({
                el: '#app',
                data: {
                    role_id: "{:I('get.role_id', '')}", //access group id
                    name: "",
                    parentid: "0",
                    description: "",
                    status: "1",
                    accessGroupList: []
                },
                methods: {
                    fetchData: function(){
                        var that = this;
                        if(that.role_id){
                            $.ajax({
                                url: "{:U('Admin/AccessGroup/getRoleAccessGroup')}&role_id=" + that.role_id,
                                type: "get",
                                dataType: "json",
                                success: function(res){
                                    if(res.status){
                                        that.accessGroupList = res.data;
                                    }else{
                                        layer.msg('操作繁忙，请稍后再试')
                                    }
                                }
                            })
                        }

                    },
                    doSubmit: function(){
                        var that = this;
                        var data = {
                            id: that.id,
                            name: that.name,
                            parentid: that.parentid,
                            description: that.description,
                            status: that.status
                        };
                        var req_url = "{:U('Admin/AccessGroup/doCreateAccessGroup')}";
                        if(that.id !== ''){
                            req_url = "{:U('Admin/AccessGroup/doEditAccessGroup')}"
                        }
                        $.ajax({
                            url: req_url,
                            type: "post",
                            dataType: "json",
                            data: data,
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
                        })
                    },
                    clickAddAccess: function(){
                        layer.open({
                            type: 2,
                            title: '权限',
                            shadeClose: true,
                            shade: 0.8,
                            area: ['80%', '60%'],
                            content: "{:U('Admin/AccessGroup/selectAccessGroupList')}" //iframe的url
                        });
                    },
                    addAccessGroup: function(accessList){
                        var that = this;
                        if(accessList){
                            accessList.forEach(function(item){
                                that.accessGroupList.push({
                                    group_id: item.id,
                                    group_name: item.name,
                                    role_id: that.role_id,
                                    group_parentid: item.parentid,
                                });
                            })
                        }
                    },
                    clickSave: function(){
                        var that = this;
                        $.ajax({
                            url: "{:U('Admin/AccessGroup/doSaveAccessGroupRole')}",
                            type: "post",
                            dataType: "json",
                            data: {
                                role_id: that.role_id,
                                accessGroupList: that.accessGroupList,
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
                    },
                    deleteItem: function(index, item){
                        this.accessGroupList.splice(index, 1);
                    }
                },
                mounted: function(){
                    this.fetchData();
                }
            })
        });

        //选择权限回调
        function selectAccessGroupListCallback(accessGroupList){
            console.log(accessGroupList)
            if(accessGroupList){
                window.App.addAccessGroup(accessGroupList)
            }
        }
    </script>
</block>

