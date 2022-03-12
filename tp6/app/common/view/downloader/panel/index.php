<div id="app" v-cloak>
    <el-card>
        <div style="margin-bottom: 20px;">
            <el-input placeholder="请填写需要下载的URL" style="width: 300px;" v-model="url" size="mini">

            </el-input>

            <el-button @click="createDownloaderTask" type="primary" size="mini">
                下载
            </el-button>
        </div>


        <el-table
                :data="lists"
                highlight-current-row
                style="width: 100%">

            <el-table-column
                    prop="downloader_url"
                    label="下载链接"
                    min-width="200">
            </el-table-column>

            <el-table-column
                    prop="downloader_state_name"
                    label="下载状态"
                    min-width="80">
            </el-table-column>

            <el-table-column
                    prop="file_url"
                    label="访问地址"
                    min-width="80">
            </el-table-column>

            <el-table-column
                    width="180"
                    prop="create_time"
                    label="创建时间">
            </el-table-column>

            <el-table-column
                    fixed="right"
                    width="300"
                    align="center"
                    label="操作">
                <template slot-scope="props">
                    <el-button
                            v-if="props.row.downloader_state != 30"
                            @click="implementDownloaderTask(props.row.downloader_id)"
                            type="text" size="mini">
                        立即执行
                    </el-button>

                    <el-button @click="deleteDownloaderTask(props.row.downloader_id)"
                               type="text" size="mini"
                               style="color: #F56C6C">
                        删除
                    </el-button>
                </template>
            </el-table-column>
        </el-table>
        <div style="text-align: center;margin-top: 20px">
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
                url: '',
                lists: [],
                totalCount: 0,
                pageSize: 10,
                pageCount: 0,
                currentPage: 1
            },
            mounted: function () {
                this.getList();
            },
            methods: {
                //创建下载任务
                createDownloaderTask: function () {
                    var _this = this
                    $.ajax({
                        url: "{:api_url('/common/downloader.Panel/index')}",
                        data: {
                            url: _this.url,
                            _action: 'submit'
                        },
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            layer.msg(res.msg)
                            _this.getList();
                        }
                    })
                },
                getList: function () {
                    var _this = this
                    $.ajax({
                        url: "{:api_url('/common/downloader.Panel/index')}",
                        data: {
                            page: this.currentPage,
                            _action: 'list'
                        },
                        dataType: 'json',
                        type: 'get',
                        success: function (res) {
                            var data = res.data;
                            _this.lists = data.data;
                            _this.totalCount = data.total;
                            _this.pageSize = data.per_page;
                            _this.pageCount = data.last_page;
                            _this.currentPage = data.current_page;
                        }
                    })
                },
                implementDownloaderTask: function (downloader_id) {
                    var _this = this
                    $.ajax({
                        url: "{:api_url('/common/downloader.Panel/index')}",
                        data: {
                            downloader_id: downloader_id,
                            _action: 'implement'
                        },
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            layer.msg(res.msg)
                            _this.getList();
                        }
                    })
                },
                deleteDownloaderTask: function (downloader_id) {
                    var _this = this
                    layer.confirm('是否确认删除?', {title: '提示'}, function () {
                        _this.doDeleteDownloaderTask(downloader_id)
                    })
                },
                doDeleteDownloaderTask: function (downloader_id) {
                    var _this = this
                    $.ajax({
                        url: "{:api_url('/common/downloader.Panel/index')}",
                        data: {
                            downloader_id: downloader_id,
                            _action: 'delete'
                        },
                        dataType: 'json',
                        type: 'post',
                        success: function (res) {
                            layer.msg(res.msg)
                            _this.getList();
                        }
                    })
                },
                currentPageChange: function (e) {
                    this.currentPage = e;
                    this.getList();
                },
            }
        });
    })
</script>
