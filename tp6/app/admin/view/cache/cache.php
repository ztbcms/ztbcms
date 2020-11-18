
<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <div class="filter-container">
            <h3>缓存更新</h3>
        </div>

        <div>
            <el-button class="filter-item" style="margin-left: 10px;margin-bottom: 15px;" size="small" type="primary" @click="submit('site')">
                更新站点数据缓存
            </el-button>
            <span>修改过站点设置，或者栏目管理，模块安装等时可以进行缓存更新</span>
        </div>

        <div>
            <el-button class="filter-item" style="margin-left: 10px;margin-bottom: 15px;" size="small" type="primary" @click="submit('template')">
                更新站点模板缓存
            </el-button>
            <span>当修改模板时，模板没及时生效可以对模板缓存进行更新</span>
        </div>

        <div>
            <el-button class="filter-item" style="margin-left: 10px;margin-bottom: 15px;" size="small" type="primary" @click="submit('logs')">
                清除网站运行日志
            </el-button>
            <span>网站运行过程中会记录各种错误日志，以文件的方式保存</span>
        </div>





    </el-card>
</div>

<style>
    .filter-container {
        padding-bottom: 10px;
    }

</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {

            },
            watch: {},
            filters: {},
            methods: {
                submit: function (type) {
                    var that = this;
                    var url = '{:api_url("/admin/cache/cache")}';
                    layer.confirm('您确定进行此操作？', {
                        btn: ['确定','取消'] //按钮
                    }, function(){
                        var data = {
                            "type": type
                        };
                        that.httpPost(url, data, function(res){
                            layer.closeAll()
                            if(res.status){
                                ELEMENT.Message.success(res.msg)
                            } else {
                                ELEMENT.Message.error(res.msg)
                            }
                        });
                    });
                }
            },
            mounted: function () {

            }
        })
    })
</script>