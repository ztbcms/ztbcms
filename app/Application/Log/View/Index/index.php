<extend name="../../Admin/View/Common/base_layout"/>
<block name="content">
    <div id="app" style="padding: 8px;background: white;" v-cloak>
        <h4>日志列表</h4>
        <hr>
        <div class="search_type cc mb10">
            类别：<input type="text" class="input" v-model="where.category" placeholder="支持模糊搜索">
            日志内容：<input type="text" class="input" v-model="where.message" placeholder="支持模糊搜索">
            时间：
            <input type="text" name="start_date" class="input datepicker" >
            -
            <input type="text" name="end_date" class="input datepicker">
            <button class="btn btn-primary" style="margin-left: 8px;" @click="search">搜索</button>
        </div>
        <hr>
        <div class="table_list">
            <table class="table table-bordered table-hover">
                <thead>
                <tr style="background: gainsboro;">
                    <td align="center" width="80">ID</td>
                    <td align="center" width="300">类别</td>
                    <td align="center">日志内容</td>
                    <td align="center" width="160">发布时间</td>
                </tr>
                </thead>
                <tbody>
                <tr v-for="item in logs">
                    <td align="center">{{item.id}}</td>
                    <td align="center">{{item.category}}</td>
                    <td align="center"><div style="overflow-wrap: break-word; max-width: 500px;">{{item.message}}</div></td>
                    <td align="center">{{item.inputtime|getFormatTime}}</td>
                </tr>
                </tbody>
            </table>

            <div style="text-align: center">
                <ul class="pagination pagination-sm no-margin">
                    <button @click="toPage( parseInt(where.page) - 1 )" class="btn btn-primary">上一页</button>
                    {{ where.page }} / {{ total_page }}
                    <button @click="toPage( parseInt(where.page) + 1 )" class="btn btn-primary">下一页</button>
                    <span style="line-height: 30px;margin-left: 10px;"><input id="ipt_page"
                                                                              style="width:50px;text-align: center;"
                                                                              type="text" v-model="temp_page"></span>
                    <span><button class="btn btn-primary" @click="toPage( temp_page )">跳转</button></span>
                </ul>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    where: {
                        category: '',
                        message: '',
                        start_date: '',
                        end_date: '',
                        page: 1,
                        limit: 20
                    },
                    logs: {},
                    temp_page: 1,
                    total_page: 0
                },
                filters: {
                    getFormatTime: function (value) {
                        var time = new Date(parseInt(value * 1000));
                        var y = time.getFullYear();
                        var m = time.getMonth() + 1;
                        var d = time.getDate();
                        var h = time.getHours();
                        var i = time.getMinutes();
                        var res = y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d) + '';
                        res += '  ' + (h < 10 ? '0' + h : h) + ':' + (i < 10 ? '0' + i : i);
                        return res;
                    },

                },
                methods: {
                    /**
                     * 格式化
                     * @param value 千分秒
                     */
                    formatTimeToYMD: function(value){
                        var time = new Date(parseInt(value));
                        var y = time.getFullYear();
                        var m = time.getMonth() + 1;
                        var d = time.getDate();
                        var res = y + '-' + (m < 10 ? '0' + m : m) + '-' + (d < 10 ? '0' + d : d) + '';
                        return res;
                    },
                    getList: function () {
                        var that = this;
                        $.ajax({
                            url: '{:U("Log/Index/getLogs")}',
                            data: that.where,
                            type: 'get',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    that.logs = res.data.items;
                                    that.where.page = res.data.page;
                                    that.where.limit = res.data.limit;
                                    that.temp_page = res.data.page;
                                    that.total_page = res.data.total_page;
                                }
                            }
                        });
                    },
                    toPage: function (page) {
                        this.where.page = page;
                        if (this.where.page < 1) {
                            this.where.page = 1;
                        }
                        if (this.where.page > this.total_page) {
                            this.where.page = this.total_page;
                        }
                        this.getList();
                    },
                    search: function () {
                        this.where.page = 1;
                        this.where.start_date = $('input[name="start_date"]').val();
                        this.where.end_date = $('input[name="end_date"]').val();
                        this.getList();
                    }
                },
                mounted: function () {
                    //默认显示最近7日日志
                    var date_today = new Date();
                    var date_last = new Date(date_today.getTime() - 7 * 24 * 60 * 60 * 1000);
                    $('input[name="start_date"]').val(this.formatTimeToYMD(date_last.getTime()));
                    $('input[name="end_date"]').val(this.formatTimeToYMD(date_today.getTime()));

                    this.search();
                }
            });
        });
    </script>
</block>
