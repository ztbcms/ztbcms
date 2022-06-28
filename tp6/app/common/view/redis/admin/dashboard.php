<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>概览</span>
        </div>

        <div class="filter-container" style="margin-top: 15px;">
            <el-button @click="getList" type="primary" size="mini">刷新</el-button>
        </div>

        <el-tabs value="1">
            <el-tab-pane :label="'Redis 链接（'+total+'）'" name="1"></el-tab-pane>
        </el-tabs>

        <el-table size="small"
                  :data="lists"
                  fit
                  highlight-current-row
                  style="width: 100%;"
        >
            <el-table-column label="Connection" align="left" min-width="80">
                <template slot-scope="{row}">
                    {{ row.connection }}
                </template>
            </el-table-column>
            <el-table-column label="运行状态" align="left" min-width="80">
                <template slot-scope="{row}">
                    <span v-if="row.running"><span style="color: green">运行中</span></span>
                    <span v-else><span style="color: red">停止</span></span>
                </template>
            </el-table-column>

            <el-table-column label="版本" align="left" min-width="80">
                <template slot-scope="{row}">
                    {{ row.info.redis_version }}
                </template>
            </el-table-column>

            <el-table-column label="已启动天数" align="left" min-width="80">
                <template slot-scope="{row}">
                    {{ row.info.uptime_in_days }}
                </template>
            </el-table-column>

            <el-table-column label="分配的内存总量" align="left" min-width="80">
                <template slot-scope="{row}">
                    {{ row.info.used_memory_human }}
                </template>
            </el-table-column>

            <el-table-column label="内存消耗峰值" align="left" min-width="80">
                <template slot-scope="{row}">
                    {{ row.info.used_memory_peak_human }}
                </template>
            </el-table-column>

            <el-table-column label="当前连接数" align="left" min-width="80">
                <template slot-scope="{row}">
                    {{ row.info.connected_clients }}
                </template>
            </el-table-column>

            <el-table-column label="累计连接总数" align="left" min-width="80">
                <template slot-scope="{row}">
                    {{ row.info.total_connections_received }}
                </template>
            </el-table-column>



        </el-table>

    </el-card>
</div>
<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                total: 0,
                lists: [],
            },
            computed: {},
            watch: {},
            filters: {},
            methods: {
                doSearch: function () {
                    var that = this;
                    that.getList();
                },
                getList: function () {
                    let that = this;
                    let url = '{:api_url("/common/redis.Admin/dashboard")}';
                    let data = {};
                    data._action = 'getDashboard';
                    that.httpGet(url, data, function (res) {
                        if (res.status) {
                            that.lists = res.data;
                            that.total = that.lists.length
                        } else {
                            layer.msg(res.msg, {time: 1000});
                        }
                    });
                }
            },
            mounted: function () {
                this.getList();
            }
        })
    })
</script>