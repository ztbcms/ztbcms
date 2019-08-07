<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>日志列表</h3>

            <div class="filter-container">
                <el-input v-model="where.category" placeholder="类别" style="width: 200px;"
                          class="filter-item"></el-input>

                <el-input v-model="where.message" placeholder="日志内容" style="width: 200px;"
                          class="filter-item"></el-input>
                <el-date-picker
                        v-model="s_e_date"
                        type="daterange"
                        align="right"
                        unlink-panels
                        clearable
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        value-format="yyyy-MM-dd">
                </el-date-picker>
                <el-button class="filter-item" type="primary" @click="search">
                    搜索
                </el-button>
                <el-button class="filter-item" type="primary" @click="clickAddLog">
                    添加日志
                </el-button>
            </div>
            <el-table
                    :key="tableKey"
                    :data="logs"
                    border
                    fit
                    highlight-current-row
                    style="width: 100%;"
            >
                <el-table-column label="ID" prop="id"  align="center" width="80">
                    <template slot-scope="scope">
                        <span>{{ scope.row.id }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="类别" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.category }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="日志内容" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.message }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="发布时间" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.input_time | parseTime('{y}-{m}-{d} {h}:{i}:{s}') }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
                    <template slot-scope="{row}">
                        <el-button size="mini" type="danger"
                                   @click="deleteLog(row.id)">
                            删除
                        </el-button>
                    </template>
                </el-table-column>

            </el-table>

            <div class="pagination-container">
                <el-pagination
                        background
                        layout="prev, pager, next, jumper"
                        :total="total"
                        v-show="total > 0"
                        :current-page.sync="where.page"
                        :page-size.sync="where.limit"
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
                    form: {},
                    tableKey: 0,
                    logs: [],
                    total: 0,
                    s_e_date: [
                        '{$data["start_date"]}',
                        '{$data["end_date"]}'
                    ],
                    where: {
                        category: '',
                        message: '',
                        start_date: '{$data["start_date"]}',
                        end_date: '{$data["end_date"]}',
                        page: 1,
                        limit: 20,
                    },
                },
                watch: {
                    's_e_date': function(newValue, oldValue){
                        if (newValue instanceof Array) {
                            this.where.start_date = newValue[0];
                            this.where.end_date = newValue[1];
                        } else {
                            this.where.start_date = this.where.end_date = '';
                        }
                    }
                },
                filters: {
                    parseTime: function (time, format) {
                        return Ztbcms.formatTime(time, format)
                    },
                },
                methods: {
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
                                    that.total = res.data.total_items;
                                    that.where.page = res.data.page;
                                }
                            }
                        });
                    },
                    search: function () {
                        this.where.page = 1;
                        this.getList();
                    },
                    handleFilter: function () {
                        this.where.page = 1
                        this.getList()
                    },
                    deleteLog: function (id) {
                        var that = this
                        layer.confirm('确认删除？', {title:'提示'}, function(index){
                            //do something
                            that.doDeleteLog(id)

                            layer.close(index);
                        });
                    },
                    doDeleteLog: function (id){
                        var that = this
                        $.ajax({
                            url: '{:U("Log/Index/deleteLog")}',
                            data: {
                                'id': id
                            },
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    that.getList()
                                    that.$message.success(res.msg);
                                } else {
                                    that.$message.error(res.msg);
                                }
                            }
                        });
                    },
                    //以窗口形式打开链接
                    openArticleLink: function (url) {
                        layer.open({
                            type: 2,
                            title: '预览',
                            content: url,
                            area: ['60%', '70%'],
                        })
                    },
                    clickAddLog: function (){
                        var url = "{:U('Log/Index/addLog')}"
                        var that = this
                        layer.open({
                            type: 2,
                            title: '编辑',
                            content: url,
                            area: ['60%', '70%'],
                            end: function(){
                                that.getList()
                            }
                        })
                    }
                },
                mounted: function () {
                    this.getList();
                },

            })
        })
    </script>
</block>
