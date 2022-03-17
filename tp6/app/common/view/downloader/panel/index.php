<div id="app" v-cloak>
    <el-card>
        <div slot="header" class="clearfix">
            <span>下载中心</span>
        </div>

        <div style="margin-bottom: 20px;">

            <el-alert
                    style="margin-bottom: 15px;"
                    title="说明"
                    type="info"
                    :closable="false">
                <p>1) 下载的域名使用的为『下载中心』-『配置』中设置的域名</p>
                <p>2) 启动下载任务队列的命令为 php think queue:work --queue downloader</p>
                <p>3) 目前支持下载的类型 视频(mp4) 图片(jpg,png,gif) 文件(pdf,docx,xls,ppt)</p>
                <p>4) 确保下载路径（public/downloader）有读写权限 </p>
            </el-alert>

            <el-alert
                    style="margin-bottom: 15px;"
                    title="计划任务"
                    type="info"
                    :closable="false">
                <p>1) 【可选设置】app\common\cronscript\DownloaderImplementScript 触发启动下载任务（不推荐以此方式启动）</p>
                <p>2) 【必须设置】app\common\cronscript\DownloaderRetryScript 处理失败下载任务</p>
                <p>3) 【必须设置】app\common\cronscript\DownloaderProcessTimeoutScript 处理超时下载任务</p>
            </el-alert>

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
                    label="状态"
                    min-width="120">
                <template slot-scope="{row}">
                    <span>{{ row.downloader_state_name }}</span>
                    <i v-if="row.downloader_state == 10 || row.downloader_state == 20" class="el-icon-loading">
                    </i>

                    <el-tooltip
                            style="margin-left: 5px;"
                            v-if="row.downloader_result"
                            class="item"
                            effect="dark"
                            :content="row.downloader_result"
                            placement="top-start">
                        <el-link type="danger"> 原因</el-link>
                    </el-tooltip>
                </template>
            </el-table-column>

            <el-table-column label="文件" align="left" min-width="80">
                <template slot-scope="{row}">
                    <div v-if="row.file_url">
                        <el-link :href="row.file_url" target="_blank" type="primary">点击预览</el-link>
                    </div>
                    <span v-else> - </span>
                </template>
            </el-table-column>

            <el-table-column
                    fixed="right"
                    width="150"
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
                downloader_ids: [],
                lists: [],
                totalCount: 0,
                pageSize: 10,
                pageCount: 0,
                currentPage: 1
            },
            mounted: function () {
                var that = this
                that.getList();

                setInterval(function () {
                    that.getList()
                }, 5 * 1000)
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
                            if (res.status) {

                                _this.downloader_ids.push(res.data.downloader_id);

                                _this.getList();
                                _this.url = '';
                            } else {
                                layer.msg(res.msg, {time: 1000});
                            }
                        }
                    })
                },
                getList: function () {
                    window.__GLOBAL_ELEMENT_LOADING_INSTANCE_ENABLE = false;
                    var _this = this
                    $.ajax({
                        url: "{:api_url('/common/downloader.Panel/index')}",
                        data: {
                            downloader_ids: this.downloader_ids,
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
                            if (res.status) {
                                _this.getList();
                                layer.msg(res.msg, {time: 1000});
                            } else {
                                layer.msg(res.msg, {time: 1000});
                            }
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
                            if (res.status) {
                                _this.getList();
                                layer.msg(res.msg, {time: 1000});
                            } else {
                                layer.msg(res.msg, {time: 1000});
                            }
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
