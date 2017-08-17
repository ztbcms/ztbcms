<extend name="../../Admin/View/Common/base_layout"/>

<block name="content">
    <div id="app" style="padding-left: 20px;padding-top: 20px;" v-cloak>
        <form class="form-horizontal" action="{:U('Admin/AccessGroup/doCreateAccessGroup')}" method="post">
            <h4>权限信息</h4>
            <hr>
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">名称</label>

                <div class="col-sm-10">
                    <input type="text" class="form-control" v-model="name">
                </div>
            </div>

            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">父权限组</label>

                <div class="col-sm-10">
                    <select v-model="parentid" class="form-control" >
                        <volist name="accessGroupTreeArray" id="item">
                            <option value="{$item['id']}" selected="">{:str_repeat('&nbsp;', $item['level']*4);}|—{$item['name']}</option>
                        </volist>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">描述</label>

                <div class="col-sm-10">
                    <input type="text" class="form-control"  placeholder="" v-model="description">
                </div>
            </div>

            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">是否启用</label>

                <div class="col-sm-10"  style="padding-top: 8px;">
                    <input type="radio" name="status" v-bind:value="1" v-model="status" checked> 启用
                    <label> &nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" name="status" v-bind:value="0" v-model="status"> 禁止</label>

                </div>
            </div>

            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label"></label>

                <div class="col-sm-10"  style="padding-top: 8px;">
                    <button class="btn btn-primary" type="button" @click="doSubmit">提交</button>
                </div>
            </div>

        </form>
    </div>
    <script>
        $(document).ready(function(){
            var App = new Vue({
                el: '#app',
                data: {
                    id: "<?php echo ($accessGroup ? $accessGroup['id'] : '');?>",
                    name: "<?php echo ($accessGroup ? $accessGroup['name'] : '0');?>",
                    parentid: "<?php echo ($accessGroup ? $accessGroup['parentid'] : '0');?>",
                    description: "<?php echo ($accessGroup ? $accessGroup['description'] : '0');?>",
                    status: "<?php echo ($accessGroup ? $accessGroup['status'] : '0');?>"
                },
                methods: {
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
                    }
                },
                mounted: function(){

                }
            })
        });
    </script>
</block>

