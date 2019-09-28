<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>列表2</h3>
            <div class="filter-container">
                <el-input v-model="listQuery.name" placeholder="名称搜索" style="width: 200px;"
                          class="filter-item"></el-input>
                <el-button class="filter-item" type="primary" style="margin-left: 10px;"
                           @click="search">
                    筛选
                </el-button>
                <el-button type="primary" @click="openEditNewFrame">添加</el-button>
            </div>
            <el-table
                    :key="tableKey"
                    :data="list"
                    border
                    fit
                    highlight-current-row
                    style="width: 100%;"
            >
                <el-table-column label="分类名称" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.name }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="图片" align="center">
                    <template slot-scope="scope">
                        <el-image :src="scope.row.picture" lazy style="width: 64px;height: 64px;"
                                  :preview-src-list="[scope.row.picture]">
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
                <el-table-column label="排序" prop="order" align="center" width="120">
                    <template slot-scope="scope">
                        <span>{{scope.row.order}}</span>
                        <span class="el-icon-edit"
                              style="margin-left: 4px"
                              @click="orderChange(scope.$index)"></span>
                    </template>
                </el-table-column>
                <el-table-column label="操作" align="center" width="230" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <el-button type="primary" size="mini" @click="openEditNewFrame(scope.$index)">
                            编辑
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
                    dialogFormVisible: false,
                    form: {
                        id: '',
                        order: 1,
                        name: '',
                        show_status: 1
                    },
                    tmp_form: {
                        id: '',
                        order: 1,
                        name: '',
                        show_status: 1
                    },
                    tableKey: 0,
                    list: [],
                    total: 0,
                    listQuery: {
                        page: 1,
                        limit: 20,
                        name: ''
                    },
                },
                watch: {},
                filters: {},
                methods: {
                    search: function () {
                        this.listQuery.page = 1;
                        this.getList();
                    },
                    getList: function () {
                        var that = this;
                        var array = [];
                        for (var i = 1; i < 5; i++) {
                            var tmp = {
                                name: "名称" + i,
                                picture: '/statics/images/logo.gif',
                                show_status: i % 2 === 0 ? "0" : "1",
                                order: i,
                            };
                            array.push(tmp);
                        }
                        that.list = array;
                    },
                    editShowStatus: function (index) {

                    },
                    openEditNewFrame: function (id) {
                        var url = "/Elementui/FormDemo/form2";
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
                    handleDelete: function (index) {
                        var that = this;
                        layer.confirm('是否确定删除该此项内容吗？', {
                            btn: ['确认', '取消'] //按钮
                        }, function () {
                            that.list.splice(index, 1);
                            layer.closeAll();
                        }, function () {
                            layer.closeAll();
                        });
                    },
                    orderChange: function (index) {
                        var that = this;
                        var url = '{:U("Manage/CourseCategory/doAddEditCourseCategory")}';
                        var data = that.list[index];
                        this.$prompt('请输入排序值', '编辑排序', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                        }).then(({value}) => {
                            data['order'] = value;
                            that.httpPost(url, data, function (res) {
                                if (res.status) {
                                    layer.msg(res.msg, {time: 1000}, function () {
                                    });
                                    that.dialogFormVisible = false;
                                    that.getList();
                                } else {
                                    layer.msg(res.msg, {time: 1000});
                                }
                            });
                        }).catch(() => {
                        });
                        this.form = this.tmp_form;
                    }
                },
                mounted: function () {
                    this.getList();
                },

            })
        })
    </script>
</block>

