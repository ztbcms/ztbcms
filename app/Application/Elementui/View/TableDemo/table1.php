<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>列表1</h3>
            <div class="filter-container">
                <el-input v-model="listQuery.course_name" placeholder="名称" style="width: 200px;"
                          class="filter-item"></el-input>
                <template>
                    <el-select v-model="listQuery.select_id" clearable placeholder="请选择分类">
                        <el-option
                                v-for="item in selectList"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id">
                        </el-option>
                    </el-select>
                </template>
                <el-select v-model="listQuery.recommend_status" clearable placeholder="选择推荐">
                    <el-option
                            key="1"
                            label="推荐"
                            value="1">
                    </el-option>
                    <el-option
                            key="0"
                            label="未推荐"
                            value="0">
                    </el-option>
                </el-select>
                <el-button class="filter-item" type="primary" style="margin-left: 10px;"
                           @click="search">
                    筛选
                </el-button>
                <el-button type="primary" @click="clickAddItem">添加</el-button>
            </div>
            <el-table
                    :key="tableKey"
                    :data="list"
                    border
                    fit
                    highlight-current-row
                    style="width: 100%;"
            >
                <el-table-column label="名称" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.name }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="分类名称" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.select_name }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="上传时间" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.input_time}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="是否显示" align="center">
                    <template slot-scope="scope">
                        <el-switch
                                v-model="scope.row.show_status"
                                active-value="1"
                                inactive-value="0"
                                @change="editShowStatus(scope.$index)">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="浏览量" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.page_views}}</span>
                    </template>
                </el-table-column>
                <el-table-column label="排序" align="center" width="120">
                    <template slot-scope="scope">
                        <span>{{scope.row.order}}</span>
                        <span class="el-icon-edit"
                              style="margin-left: 4px"
                              @click="orderChange(scope.$index)"></span>
                    </template>
                </el-table-column>
                <el-table-column label="推荐" align="center">
                    <template slot-scope="scope">
                        <el-switch
                                v-model="scope.row.recommend_status"
                                active-value="1"
                                inactive-value="0"
                                @change="editRecommendStatus(scope.$index)">
                        </el-switch>
                    </template>
                </el-table-column>
                <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <el-button type="primary" size="mini" @click="editItem(scope.row.id)">
                            编辑
                        </el-button>
                        <el-button size="mini" type="danger"
                                   @click="handleDelete(scope.row.id)">
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
                    form: {
                        id: '',
                        order: 1,
                        name: '',
                        show_status: 1
                    },
                    tableKey: 0,
                    list: [],
                    total: 0,
                    selectList: [],
                    listQuery: {
                        course_category_id: '',
                        page: 1,
                        limit: 20,
                        name: ''
                    },
                },
                watch: {},
                filters: {
                    parseTime: function (time, format) {
                        return Ztbcms.formatTime(time, format)
                    },
                },
                methods: {
                    search: function () {
                        this.listQuery.page = 1;
                        this.getList();
                    },
                    getSelectList: function () {
                        var that = this;
                        that.selectList = [
                            {
                                id: 1,
                                name: "选项1"
                            },
                            {
                                id: 2,
                                name: "选项2"
                            }
                        ]
                    },
                    getList: function () {
                        var that = this;
                        var array = [];
                        for (var i = 1; i < 5; i++) {
                            var tmp = {
                                name: "名称" + i,
                                select_name: "选项" + i,
                                input_time: "2019-09-01 22:00:00",
                                show_status: i % 2 === 0 ? "0" : "1",
                                page_views: i,
                                order: i,
                                recommend_status: i % 2 === 0 ? "1" : "0"
                            };
                            array.push(tmp);
                        }
                        that.list = array;
                    },
                    clickAddItem: function () {
                        this.openEditItem()
                    },
                    editItem: function (id) {
                        this.openEditItem(id)
                    },
                    //打开编辑框
                    openEditItem: function (id = 0) {
                        var url = "/Elementui/FormDemo/form1";
                        if (id) {
                            url += '?id=' + id
                        }
                        var that = this;
                        layer.open({
                            type: 2,
                            title: '编辑',
                            content: url,
                            area: ['100%', '100%'],
                            end: function () {
                                that.getList()
                            }
                        })
                    },
                    editShowStatus: function (index) {

                    },
                    editRecommendStatus: function (index) {

                    },
                    handleDelete: function (id) {
                        var that = this;
                        layer.confirm('是否确定删除该内容吗？', {
                            btn: ['确认', '取消'] //按钮
                        }, function () {
                            layer.closeAll();
                        }, function () {
                            layer.closeAll();
                        });
                    },
                    orderChange: function (index) {
                        var that = this;
                        var url = '{:U("Manage/Course/order")}';
                        this.$prompt('请输入排序值', '编辑排序', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                        }).then(({value}) => {
                            that.list[index]['order'] = value;
                        }).catch(() => {
                        });
                    }
                },
                mounted: function () {
                    this.getList();
                    this.getSelectList();
                },

            })
        })
    </script>
</block>