<extend name="../../Admin/View/Common/base_layout"/>

<block name="content">
    <div id="app" style="padding-left: 20px;padding-top: 20px;" v-cloak>
        <h4>权限列表</h4>
        <hr>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>选择</th>
                        <th>名称</th>
                    </tr>
                    </thead>
                    <tbody>
                        <template v-for="(item, index) in accessList">
                            <tr>
                                <th scope="row">{{ item['id'] }}</th>
                                <td><input type="checkbox"  v-model="item.checked"></td>
                                <td>
                                    <template v-for="i in item['level']*4"><span>&nbsp;</span></template>
                                    |—{{ item['name'] }}
                                    ({{ item['app'] }}/{{ item['controller'] }}/{{ item['action'] }})
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div style="position: fixed; bottom: 0;left: 0;right: 0;background: white;padding: 10px 40px;border-top: 1px solid gainsboro;">
                    <button class="btn btn-primary" @click="selectAll">全选</button>
                    <button class="btn btn-primary" @click="unSelectAll">全不选</button>
                    <button class="btn btn-success" @click="confirmSelect">确认选择</button>
                </div>
            </div>
        </div>

    </div>
    <script>
        $(document).ready(function(){
            var App = new Vue({
                el: '#app',
                data: {
                    accessList: [],
                    initSelectIds: []
                },
                computed: {
                    selectedItems: function(){
                        var result = [];
                        if(this.accessList){
                            this.accessList.forEach(function(item){
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
                            url: "{:U('Admin/AccessGroup/getAccessList')}",
                            type: "get",
                            dataType: "json",
                            success: function(res){
                                if(res.status){
                                    res.data.forEach(function(item,index){
                                        if(that.initSelectIds.indexOf('' + item['id']) === -1){
                                            res.data[index]['checked'] = false;
                                        }else{
                                            res.data[index]['checked'] = true;
                                        }

                                    });
                                    that.accessList = res.data;
                                    that.initSelectIds = [];
                                }else{
                                    layer.msg('操作繁忙，请稍后再试')
                                }
                            }
                        })
                    },
                    confirmSelect: function(){
                        console.log(this.selectedItems);
                        if(parent.window.selectAccessListCallback){
                            parent.window.selectAccessListCallback(this.selectedItems)
                        }
                        parent.window.layer.closeAll();
                    },
                    selectAll: function(){
                        var that = this;
                        that.accessList.forEach(function(item, index){
                            that.accessList[index]['checked'] = true;
                        });
                    },
                    unSelectAll: function(){
                        var that = this;
                        that.accessList.forEach(function(item, index){
                            that.accessList[index]['checked'] = false;
                        });
                    },
                    init: function(){
                        var urlObj = this.urlParser(location.href);
                        if(urlObj.search.selected_ids){
                            this.initSelectIds = urlObj.search.selected_ids.split(',')
                        }
                    },
                    urlParser: function(url) {
                        var a = document.createElement('a');
                        a.href = url;

                        var search = function(search) {
                            if(!search) return {};

                            var ret = {};
                            search = search.slice(1).split('&');
                            for(var i = 0, arr; i < search.length; i++) {
                                arr = search[i].split('=');
                                var key = arr[0], value = arr[1];
                                if(/\[\]$/.test(key)) {
                                    ret[key] = ret[key] || [];
                                    ret[key].push(value);
                                } else {
                                    ret[key] = value;
                                }
                            }
                            return ret;
                        };

                        return {
                            protocol: a.protocol,
                            host: a.host,
                            hostname: a.hostname,
                            pathname: a.pathname,
                            search: search(a.search),
                            hash: a.hash
                        }
                    }
                },
                mounted: function(){
                    this.init();
                    this.fetchData();
                }
            })
        });
    </script>
</block>

