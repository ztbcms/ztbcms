<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <div class="filter-container">
            <h3>模块列表</h3>
        </div>

        <el-table
                :key="tableKey"
                :data="list"
                highlight-current-row
                style="width: 100%;"
        >
            <el-table-column label="模块别名/名称" align="center">
                <template slot-scope="scope">
                    <span>{{ scope.row.module }}</span> | <span>{{ scope.row.modulename }}</span>
                </template>
            </el-table-column>

            <el-table-column label="介绍" align="">
                <template slot-scope="scope">
                    <span>{{ scope.row.introduce }}</span>
                </template>
            </el-table-column>

            <el-table-column label="作者" align="">
                <template slot-scope="scope">
                    <span>{{ scope.row.author }}</span>
                </template>
            </el-table-column>

            <el-table-column label="版本" align="">
                <template slot-scope="scope">
                    <span>{{ scope.row.version }}</span>
                </template>
            </el-table-column>

            <el-table-column label="最低后台版本" align="">
                <template slot-scope="scope">
                    <span>{{ scope.row.adaptation }}</span>
                </template>
            </el-table-column>
            <el-table-column label="安装时间" align="">
                <template slot-scope="scope">
                    <span v-if="scope.row.install_time === ''">未安装</span>
                    <span v-else>{{ scope.row.install_time }}</span>
                </template>
            </el-table-column>

            <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
                <template slot-scope="scope">
                    <template v-if="scope.row.install_time === ''">
                        <el-button type="primary" size="mini" @click="toInstall(scope.row)">安装</el-button>
                    </template>
                    <template v-else>
<!--                        <el-button type="danger" size="mini">禁用</el-button>-->
                        <el-button type="danger" size="mini"  @click="toInstall(scope.row)">卸载</el-button>
                    </template>
                </template>
            </el-table-column>
        </el-table>

        <div class="pagination-container">
            <el-pagination
                    background
                    layout="prev, pager, next, jumper"
                    :total="listQuery.total"
                    v-show="listQuery.total > 0"
                    :current-page.sync="listQuery.page"
                    :page-size.sync="listQuery.limit"
                    @current-change="getList"
            >
            </el-pagination>
        </div>

    </el-card>
</div>

<style>
    .filter-container {
        padding-bottom: 10px;
    }

    .pagination-container {
        padding: 32px 16px;
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                tableKey: 0,
                list: [],
                total: 0,
                listQuery: {
                    page: 1,
                    limit: 20,
                    total: 0,
                    keyword: ''
                }
            },
            watch: {},
            filters: {},
            methods: {
                getList: function () {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/admin/Module/getModuleList')}",
                        type: "get",
                        dataType: "json",
                        data: that.listQuery,
                        success: function (res) {
                            if (res.status) {
                                that.list = res.data.items;
                                that.listQuery.total = res.data.total_items;
                                that.listQuery.page = res.data.page;
                                that.listQuery.limit = res.data.limit;
                            }
                        }
                    })
                },
                // 安装
                toInstall: function(moduleInfo){
                    var that = this
                    var url = "{:api_url('/admin/module/install')}"+'?module=' + moduleInfo.module
                    layer.open({
                        type: 2,
                        title: '安装',
                        content: url,
                        area: ['670px', '550px'],
                        end: function(){
                            that.getList()
                        }
                    })
                },
            },
            mounted: function () {
                this.getList();
            },
        })
    })
</script>
