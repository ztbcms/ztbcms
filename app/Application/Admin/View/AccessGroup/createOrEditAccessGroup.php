<extend name="../../Admin/View/Common/base_layout"/>

<block name="content">
    <div id="app" style="padding-left: 20px;padding-top: 20px;" v-cloak>
        <form class="form-horizontal" action="{:U('Admin/AccessGroup/doCreateAccessGroup')}" method="post">
            <h4>权限信息</h4>
            <hr>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">名称</label>

                <div class="col-sm-5">
                    <input type="text" class="form-control" v-model="name">
                </div>
                <div class="col-sm-5">
                </div>
            </div>

            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">父权限组</label>

                <div class="col-sm-5">
                    <select v-model="parentid" class="form-control" >
                        <volist name="accessGroupTreeArray" id="item">
                            <option value="{$item['id']}" selected="">{:str_repeat('&nbsp;', $item['level']*4);}|—{$item['name']}</option>
                        </volist>
                    </select>
                </div>
                <div class="col-sm-5">
                </div>
            </div>

            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">描述</label>

                <div class="col-sm-5">
                    <input type="text" class="form-control"  placeholder="" v-model="description">
                </div>
                <div class="col-sm-5">
                </div>
            </div>

            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">是否启用</label>

                <div class="col-sm-5"  style="padding-top: 8px;">
                    <input type="radio" name="status" v-bind:value="1" v-model="status" checked> 启用
                    <label> &nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="status" v-bind:value="0" v-model="status"> 禁止</label>
                </div>
                <div class="col-sm-5">
                </div>
            </div>

            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"></label>

                <div class="col-sm-10"  style="padding-top: 8px;">
                    <button class="btn btn-primary" type="button" @click="doSubmit">提交</button>
                </div>
            </div>
        </form>

        <h4>权限列表</h4>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>名称</th>
                            <th>模块</th>
                            <th>控制器</th>
                            <th>方法</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template v-for="(item, index) in accessGroupItems">
                            <tr>
                                <td>{{ item.name }}</td>
                                <td>{{ item.app }}</td>
                                <td>{{ item.controller }}</td>
                                <td>{{ item.action }}</td>
                                <td>
                                    <button class="btn btn-danger" @click="deleteItem(index, item)">删除</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <hr>
                <p>
                    <button class="btn btn-primary" @click="clickAddAccess">添加权限</button>
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
                    id: "{:I('get.id', '')}", //access group id
                    name: "",
                    parentid: "0",
                    description: "",
                    status: "1",
                    accessGroupItems: []
                },
                methods: {
                    fetchData: function(){
                        var that = this;
                        if(that.id){
                            $.ajax({
                                url: "{:U('Admin/AccessGroup/getAccessGroupById')}&id=" + that.id,
                                type: "get",
                                dataType: "json",
                                success: function(res){
                                    if(res.status){
                                        that.id = res.data.id;
                                        that.name = res.data.name;
                                        that.parentid = res.data.parentid;
                                        that.description = res.data.description;
                                        that.status = res.data.status;
                                        that.accessGroupItems = res.data.accessGroupItems;
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
                            title: '权限列表',
                            shadeClose: true,
                            shade: 0.8,
                            area: ['80%', '60%'],
                            content: "{:U('Admin/AccessGroup/accessList')}" //iframe的url
                        });
                    },
                    addAccess: function(accessList){
                        var that = this;
                        if(accessList){
                            accessList.forEach(function(item){
                                that.accessGroupItems.push({
                                    group_id: that.id,
                                    app: item.app,
                                    controller: item.controller,
                                    action: item.action,
                                    name: item.name
                                });
                            })
                        }
                    },
                    clickSave: function(){
                        var that = this;
                        $.ajax({
                            url: "{:U('Admin/AccessGroup/doSaveAccessGroupItem')}",
                            type: "post",
                            dataType: "json",
                            data: {
                                group_id: that.id,
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
                    },
                    deleteItem: function(index, item){
                        this.accessGroupItems.splice(index, 1);
                    }
                },
                mounted: function(){
                    this.fetchData();
                }
            })
        });

        //选择权限回调
        function selectAccessListCallback(accessList){
            if(accessList){
                window.App.addAccess(accessList)
            }
        }
    </script>
</block>

