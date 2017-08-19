<extend name="../../Admin/View/Common/base_layout"/>

<block name="content">
    <div id="app" style="padding-left: 20px;padding-top: 20px;" v-cloak>
        <h4>权限组 <a class="btn btn-success" href="{:U('Admin/AccessGroup/createAccessGroup')}">添加</a></h4>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>名称</th>
                        <th>启用</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <template v-for="(item, index) in accessGroupList">
                        <tr>
                            <th scope="row" style="width: 40px;">{{ item['id'] }}</th>
                            <td>
                                <template v-for="i in item['level']*4"><span>&nbsp;</span></template>
                                |—{{ item['name'] }}
                            </td>
                            <td>
                                <template v-if="item.status == 1">
                                    <span style="color: green;">√</span>
                                </template>
                                <template v-if="item.status != 1">
                                    <span style="color: red;">X</span>
                                </template>
                            </td>
                            <td>
                                <a class="btn btn-primary" :href="'{:U('Admin/AccessGroup/editAccessGroup')}&id=' + item.id">编辑</a>
                                <button class="btn btn-danger" @click="clickDelteItem(index, item)">删除</button>
                            </td>
                        </tr>
                    </template>
                    </tbody>
                </table>

                <div style="position: fixed; bottom: 0;left: 0;right: 0;background: white;padding: 10px 40px;border-top: 1px solid gainsboro;">
<!--                    <button class="btn btn-primary" @click="selectAll">全选</button>-->
<!--                    <button class="btn btn-primary" @click="unSelectAll">全不选</button>-->
<!--                    <button class="btn btn-success" @click="confirmSelect">确认选择</button>-->
                </div>
            </div>
        </div>

    </div>
    <script>
        $(document).ready(function(){
            var App = new Vue({
                el: '#app',
                data: {
                    accessGroupList: []
                },
                computed: {
                    selectedItems: function(){
                        var result = [];
                        if(this.accessGroupList){
                            this.accessGroupList.forEach(function(item){
                                if(item.checked){
                                    result.push(item);
                                }
                            });
                        }
                        return result;
                    }
                },
                methods: {
                    fetchData: function(){
                        var that = this;
                        $.ajax({
                            url: "{:U('Admin/AccessGroup/getAccessGroupList')}",
                            type: "get",
                            dataType: "json",
                            success: function(res){
                                if(res.status){
                                    res.data.forEach(function(item,index){
                                        res.data[index]['checked'] = false;
                                    });
                                    that.accessGroupList = res.data;
                                }else{
                                    layer.msg('操作繁忙，请稍后再试')
                                }
                            }
                        })
                    },
                    confirmSelect: function(){
                        if(parent.window.selectAccessGroupListCallback){
                            parent.window.selectAccessGroupListCallback(this.selectedItems)
                        }
                        parent.window.layer.closeAll();
                    },
                    selectAll: function(){
                        var that = this;
                        that.accessGroupList.forEach(function(item, index){
                            that.accessGroupList[index]['checked'] = true;
                        });
                    },
                    unSelectAll: function(){
                        var that = this;
                        that.accessGroupList.forEach(function(item, index){
                            that.accessGroupList[index]['checked'] = false;
                        });
                    },
                    clickDelteItem: function(index, item){
                        var that = this;

                        layer.confirm('确认要删除?', function(index){
                            $.ajax({
                                url: "{:U('Admin/AccessGroup/deleteAccessGroup')}",
                                type: "post",
                                data:{
                                    group_id: item.id
                                },
                                dataType: "json",
                                success: function(res){
                                    layer.close(index);

                                    if(res.status){
                                        layer.msg('操作成功');
                                        setTimeout(function(){
                                            window.location.reload();
                                        }, 700);
                                    }else{
                                        layer.msg('操作繁忙，请稍后再试')
                                    }
                                }
                            })
                        });
                    }
                },
                mounted: function(){
                    this.fetchData();
                }
            })
        });
    </script>
</block>

