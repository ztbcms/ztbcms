<div id="app" v-cloak>
    <el-card>
        <el-form :inline="true" :model="searchForm">

            <el-form-item label="上传时间">
                <el-date-picker
                        v-model="searchForm.create_time"
                        type="daterange"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期">
                </el-date-picker>
            </el-form-item>

            <el-form-item label="附件名称">
                <el-input v-model="searchForm.filename" placeholder=""></el-input>
            </el-form-item>


            <el-form-item label="">
                <el-button type="primary" @click="search">查询</el-button>
            </el-form-item>
        </el-form>

        <el-tabs v-model="searchForm.tab">
            <el-tab-pane label="全部" name="0"></el-tab-pane>
            <el-tab-pane label="图片" name="1"></el-tab-pane>
            <el-tab-pane label="视频" name="2"></el-tab-pane>
            <el-tab-pane label="文件" name="3"></el-tab-pane>
        </el-tabs>
        <el-table
            :data="lists"
            style="width: 100%"
            fit
            highlight-current-row
            @selection-change="handleSelectionChange"
        >
            <el-table-column
                type="selection"
                width="55"
                label="全选">
            </el-table-column>

            <el-table-column
                align="center"
                label="上传用户"
                min-width="60">
                <template slot-scope="scope">
                    <span>{{ scope.row.user_type }} / {{ scope.row.user_id }}</span>
                </template>
            </el-table-column>

            <el-table-column
                align="center"
                label="附件名称"
                min-width="160">
                <template slot-scope="scope">
                    <span>{{ scope.row.filename }}</span>
                </template>
            </el-table-column>

            <el-table-column
                align="left"
                prop="grade_name"
                label="附件大小"
                min-width="60">
                <template slot-scope="scope">
                    <span>{{ scope.row.filesize }} KB</span>
                </template>
            </el-table-column>


            <el-table-column
                    align="left"
                    prop="grade_name"
                    label="上传时间"
                    min-width="120">
                <template slot-scope="scope">
                    <span>{{ scope.row.create_time }}</span>
                </template>
            </el-table-column>


            <el-table-column
                min-width="190"
                align="center"
                fixed="right"
                label="操作">
                <template slot-scope="scope">
                    <el-button @click="doPreview(scope.row.fileurl)" type="text" size="mini">预览</el-button>
                    <el-button @click="doDelete(scope.row)" type="text" size="mini" style="color:#F56C6C">删除</el-button>
                </template>
            </el-table-column>
        </el-table>

        <div style="margin-top: 15px;">
            <el-button type="danger" @click="doBatchDelete" size="mini">删除</el-button>
        </div>

        <div style="text-align: left;margin-top: 20px">
            <el-pagination
                background
                @current-change="currentPageChange"
                layout="prev, pager, next"
                :current-page="currentPage"
                :page-count="totalCount"
                :page-size="pageSize"
                :total="totalCount">
            </el-pagination>
        </div>
    </el-card>
</div>


<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                searchForm: {
                    create_time: [],
                    filename: "",
                    upload_time: [],
                },
                defaultImage: '/statics/images/member/nophoto.gif',
                multipleSelection: [],
                selectIds: [],
                lists: [],
                totalCount: 0,
                pageSize: 15,
                pageCount: 0,
                currentPage: 1,
            },
            watch: {
                "searchForm.tab": function () {
                    this.getList()
                }
            },
            computed: {
                request_url: function () {
                    return "{:api_Url('/common/upload.Upload/attachments')}"
                }
            },
            mounted: function () {
                this.getList()
            },
            methods: {
                // 全选
                handleSelectionChange: function (val) {
                    this.multipleSelection = val;
                    var selectIds = [];
                    this.multipleSelection.forEach(function (val) {
                        selectIds.push(val.aid);
                    })
                    this.selectIds = selectIds;
                },
                currentPageChange: function (e) {
                    this.currentPage = e;
                    this.getList();
                },
                // 搜索
                search: function () {
                    this.currentPage = 1;
                    this.getList();
                },
                // 获取列表
                getList: function () {
                    var that = this
                    var data = this.searchForm
                    data['_action'] = 'getList'
                    data['page'] = this.currentPage
                    data['limit'] = this.pageSize
                    this.httpGet(this.request_url, data, function (res) {
                        that.lists = res.data.items;
                        that.totalCount = res.data.total_items;
                        that.page = res.data.page;
                        that.limit = res.data.limit;
                        that.page_count = res.data.total_pages;
                    })
                },
                // 预览
                doPreview: function (url) {
                    window.open(url)
                },
                // 删除单个
                doDelete: function (item) {
                    var that = this
                    this.httpPost(this.request_url, {_action:'doDelete', aid: item.aid}, function (res) {
                        if (res.status) {
                            that.$message.success(res.msg)
                            that.getList()
                        } else {
                            that.$message.error(res.msg)
                        }
                    })
                },
                // 批量删除
                doBatchDelete: function () {
                    var that = this
                    this.httpPost(this.request_url, {_action:'doBatchDelete', aids: that.selectIds}, function (res) {
                        if (res.status) {
                            that.$message.success(res.msg)
                            that.getList()
                        } else {
                            that.$message.error(res.msg)
                        }
                    })
                }
            }
        });
    })
</script>
