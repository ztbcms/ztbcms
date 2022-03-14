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
                <p>1) 由于队列无法使用 $_SERVER['HTTP_HOST'] 获取当前域名，所以下载的域名使用的为 站点设置 - 网站访问地址中设置的域名</p>
                <p>2) 队列启动的命令为  php think queue:work --queue downloader （前提 composer require topthink/think-queue ）</p>
                <p>3) 目前支持下载的类型 视频(mp4) 图片(jpg,png,gif) 文件(pdf,docx,txt)</p>
                <p>4) 确保下载路径在写去权限 app()->getRootPath().'public/downloader </p>
            </el-alert>

            <el-alert
                    style="margin-bottom: 15px;"
                    title="计划任务"
                    type="info"
                    :closable="false">
                <p>1) 启动 app\common\cronscript\DownloaderRetryScript 可帮助下载失败的任务进行重启</p>
                <p>2) 启动 app\common\cronscript\DownloaderRetryScript 可帮助队列任务遗漏的未开始任务进行执行</p>
            </el-alert>

            <el-alert
                    style="margin-bottom: 15px;"
                    title="测试连接"
                    type="info"
                    :closable="false">
                <p>1) 测试视频 ：https://vd2.bdstatic.com/mda-kahifai35xn97s75/v1-cae/sc/mda-kahifai35xn97s75.mp4 </p>
                <p>2) 测试图片 ：https://ms.bdimg.com/pacific/0/pic/-186488820_-183993379.png </p>
                <p>3) 测试文件 ：https://wasterecycling.oss-cn-shenzhen.aliyuncs.com/image/20200925/%E8%81%8A%E5%A4%A9%E5%AE%A4%E8%AF%B4%E6%98%8E.docx  </p>
                <p>3) 测试文件 ：https://wasterecycling.oss-cn-shenzhen.aliyuncs.com/image/20200925/%E8%81%8A%E5%A4%A9%E5%AE%A4%E8%AF%B4%E6%98%8E.pdf  </p>
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
                    label="下载状态"
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
                        <a :href="row.file_url" target="_blank">
                            <el-image
                                    style="width: 50px; height: 50px"
                                    :src="row.file_thumb"
                                    :fit="fit">
                            </el-image>
                        </a>
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
                downloader_ids : [],
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
                }, 2 * 1000)
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
                            downloader_ids :  this.downloader_ids,
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
