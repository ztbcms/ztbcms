<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>列表3</h3>
            <div class="filter-container">
                <el-input v-model="listQuery.title" placeholder="关键词" style="width: 200px;"
                          class="filter-item"></el-input>
                <el-input v-model="listQuery.user_name" placeholder="用户名" style="width: 200px;"
                          class="filter-item"></el-input>
                <el-date-picker
                        v-model="input_date"
                        type="daterange"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        :default-time="['00:00:00', '23:59:59']">
                </el-date-picker>
                <el-button class="filter-item" type="primary" style="margin-left: 10px;"
                           @click="search">
                    筛选
                </el-button>
            </div>
            <div class="filter-container">
                <template>
                    <el-tabs v-model="listQuery.tab" lazy>
                        <el-tab-pane v-for="(item,index) in tab" :key="index" :label="item.name"
                                     :name="item.id"></el-tab-pane>
                    </el-tabs>
                </template>
            </div>
            <el-table
                    :key="tableKey"
                    :data="list"
                    border
                    fit
                    highlight-current-row
                    style="width: 100%;"
            >
                <el-table-column label="内容" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.content }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="图片" align="center">
                    <template slot-scope="scope">
                        <el-image :src="scope.row.avatar" lazy style="width: 64px;height: 64px;"
                                  :preview-src-list="[scope.row.avatar]">
                    </template>
                </el-table-column>
                <el-table-column label="用户名" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.user_name }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="时间" prop="input_time" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.input_time!=0">{{ scope.row.input_time}}</span>
                        <span v-else>——</span>
                    </template>
                </el-table-column>
                <el-table-column label="是否公开" align="center">
                    <template slot-scope="scope">
                        <div v-if="scope.row.open_status == 1" class="el-icon-success"
                             style="color: #409EFF;font-size: 1.5rem"></div>
                        <div v-else class="el-icon-error" style="font-size: 1.5rem"></div>
                    </template>
                </el-table-column>
                <el-table-column label="状态" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.reply_status==1">
                            已回复
                        </span>
                        <span v-else>
                            未回复
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <el-button type="primary" size="mini" @click="openDetail(scope.row.id)">
                            查看
                        </el-button>
                        <el-button size="mini" type="danger"
                                   @click="handleDelete(scope.$index)">
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
                        v-show="total>0"
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
                    tab: [
                        {
                            id: "1",
                            name: "标签1"
                        },
                        {
                            id: "2",
                            name: "标签2"
                        },
                        {
                            id: "3",
                            name: "标签3"
                        }
                    ],
                    input_date: ['', ''],
                    listQuery: {
                        page: 1,
                        tab: '',
                        limit: 20,
                        start_time: '',
                        end_time: '',
                        user_name: '{$user_name}',
                        title: ''
                    }
                },
                watch: {},
                filters: {},
                methods: {
                    search: function () {
                        this.getList();
                    },
                    openDetail: function (id) {
                        var url = "/Elementui/TableDemo/detail";
                        if (id !== 0) {
                            url += '?id=' + id
                        }
                        layer.open({
                            type: 2,
                            title: '详情',
                            content: url,
                            area: ['100%', '100%'],
                        })
                    },
                    getList: function () {
                        var that = this;
                        var array = [];
                        for (var i = 1; i < 5; i++) {
                            var tmp = {
                                content: "内容" + i,
                                avatar: "/statics/images/logo.gif",
                                user_name: "用户" + i,
                                input_time: "2019-09-01 22:00:00",
                                open_status: i % 2 === 0 ? "0" : "1",
                                reply_status: i % 2 === 0 ? "1" : "0",
                            };
                            array.push(tmp);
                        }
                        that.list = array;
                    },
                    handleClick: function () {
                        this.getList();
                    },
                    handleDelete: function (index) {
                        var that = this;
                        layer.confirm('是否确定删除该内容吗？', {
                            btn: ['确认', '取消'] //按钮
                        }, function () {
                            that.list.splice(index, 1);
                            layer.closeAll();
                        }, function () {
                            layer.closeAll();
                        });
                    },
                },
                mounted: function () {
                    this.getList();
                },
            })
        })
    </script>
</block>