<div id="app" class="cache-page" v-cloak>
    <el-card class="cache-panel" shadow="never">
        <div class="page-heading">
            <div class="heading-icon">
                <i class="el-icon-s-operation"></i>
            </div>
            <div>
                <h2>缓存与日志维护</h2>
                <p>按用途清理平台运行期间生成的缓存和日志文件，操作完成后目录会在后续请求中自动重建</p>
            </div>
        </div>

        <el-alert
            class="maintenance-notice"
            title="以下操作会立即删除对应目录内的文件，请先核对清理范围"
            type="warning"
            :closable="false"
            show-icon>
        </el-alert>

        <div class="action-list">
            <section class="action-card action-card--data">
                <div class="action-marker">
                    <i class="el-icon-coin"></i>
                </div>
                <div class="action-content">
                    <div class="action-title-row">
                        <h3>清理站点数据缓存</h3>
                    </div>
                    <p class="action-description">站点设置、栏目管理或模块配置变更后，清理后台配置与公共文件缓存</p>
                    <div class="scope-list">
                        <div class="scope-item">
                            <span class="scope-index">01</span>
                            <div>
                                <strong>后台配置缓存</strong>
                                <span>缓存标签 <code>AdminConfig</code></span>
                            </div>
                        </div>
                        <div class="scope-item">
                            <span class="scope-index">02</span>
                            <div>
                                <strong>公共文件缓存</strong>
                                <span>目录 <code>tp6/runtime/cache/</code> 内的全部文件</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="action-control">
                    <el-button type="primary" icon="el-icon-delete" @click="submit('site', '清理站点数据缓存')">
                        立即清理
                    </el-button>
                </div>
            </section>

            <section class="action-card action-card--template">
                <div class="action-marker">
                    <i class="el-icon-brush"></i>
                </div>
                <div class="action-content">
                    <div class="action-title-row">
                        <h3>清理站点模板缓存</h3>
                    </div>
                    <p class="action-description">模板文件修改后页面未及时生效时，清理各应用生成的视图模板编译缓存</p>
                    <div class="scope-list">
                        <div class="scope-item">
                            <span class="scope-index">01</span>
                            <div>
                                <strong>所有应用的模板编译缓存</strong>
                                <span>目录 <code>tp6/runtime/*/temp/</code> 内的全部文件</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="action-control">
                    <el-button type="success" icon="el-icon-delete" @click="submit('template', '清理站点模板缓存')">
                        立即清理
                    </el-button>
                </div>
            </section>

            <section class="action-card action-card--logs">
                <div class="action-marker">
                    <i class="el-icon-document-delete"></i>
                </div>
                <div class="action-content">
                    <div class="action-title-row">
                        <h3>清理网站运行日志</h3>
                    </div>
                    <p class="action-description">清理平台及各应用记录的系统运行日志和错误日志，执行前请确认日志无需留存</p>
                    <div class="scope-list">
                        <div class="scope-item">
                            <span class="scope-index">01</span>
                            <div>
                                <strong>公共运行日志</strong>
                                <span>目录 <code>tp6/runtime/log/</code> 内的全部文件</span>
                            </div>
                        </div>
                        <div class="scope-item">
                            <span class="scope-index">02</span>
                            <div>
                                <strong>所有应用的运行日志</strong>
                                <span>目录 <code>tp6/runtime/*/log/</code> 内的全部文件</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="action-control">
                    <el-button type="danger" plain icon="el-icon-delete" @click="submit('logs', '清理网站运行日志')">
                        立即清理
                    </el-button>
                </div>
            </section>
        </div>
    </el-card>
</div>

<style>
    .cache-page {
        padding: 16px;
        color: #263238;
    }

    .cache-panel {
        border: 1px solid #e6e9ed;
        border-radius: 8px;
    }

    .cache-panel > .el-card__body {
        padding: 28px;
    }

    .page-heading {
        display: flex;
        align-items: center;
    }

    .heading-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 48px;
        height: 48px;
        margin-right: 16px;
        border-radius: 12px;
        color: #fff;
        background: #25364a;
        font-size: 23px;
        box-shadow: 0 8px 18px rgba(37, 54, 74, .16);
    }

    .page-heading h2 {
        margin: 0 0 7px;
        color: #1f2d3d;
        font-size: 21px;
        line-height: 1.2;
    }

    .page-heading p {
        margin: 0;
        color: #7b8794;
        font-size: 13px;
        line-height: 1.6;
    }

    .maintenance-notice {
        margin-top: 24px;
    }

    .action-list {
        margin-top: 20px;
        border: 1px solid #e7ebf0;
        border-radius: 8px;
        overflow: hidden;
    }

    .action-card {
        position: relative;
        display: flex;
        align-items: flex-start;
        padding: 24px;
        background: #fff;
        transition: background-color .2s ease;
    }

    .action-card + .action-card {
        border-top: 1px solid #edf0f3;
    }

    .action-card:hover {
        background: #fafbfd;
    }

    .action-marker {
        display: flex;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        margin-right: 18px;
        border-radius: 10px;
        font-size: 20px;
    }

    .action-card--data .action-marker {
        color: #2f78c4;
        background: #eaf3fc;
    }

    .action-card--template .action-marker {
        color: #2d8b69;
        background: #e9f7f1;
    }

    .action-card--logs .action-marker {
        color: #c45656;
        background: #fdf0f0;
    }

    .action-content {
        flex: 1;
        min-width: 0;
    }

    .action-title-row {
        display: flex;
        align-items: center;
        min-height: 28px;
    }

    .action-title-row h3 {
        margin: 0 12px 0 0;
        color: #263445;
        font-size: 16px;
    }

    .action-description {
        margin: 6px 0 14px;
        color: #77818c;
        font-size: 13px;
        line-height: 1.6;
    }

    .scope-list {
        display: flex;
        flex-wrap: wrap;
        margin: -4px;
    }

    .scope-item {
        display: flex;
        align-items: flex-start;
        min-width: 280px;
        margin: 4px;
        padding: 10px 13px;
        border: 1px solid #e8ecf1;
        border-radius: 6px;
        background: #f8fafc;
    }

    .scope-index {
        margin: 2px 11px 0 0;
        color: #a4adb7;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .5px;
    }

    .scope-item strong,
    .scope-item span {
        display: block;
    }

    .scope-item strong {
        margin-bottom: 4px;
        color: #46515d;
        font-size: 12px;
        font-weight: 600;
    }

    .scope-item div > span {
        color: #7c8792;
        font-size: 12px;
        line-height: 1.5;
    }

    .scope-item code {
        padding: 1px 5px;
        border-radius: 3px;
        color: #43566b;
        background: #e8edf3;
        font-family: Menlo, Monaco, Consolas, monospace;
        font-size: 11px;
    }

    .action-control {
        flex: 0 0 116px;
        align-self: center;
        margin-left: 28px;
        text-align: right;
    }

    .action-control .el-button {
        min-width: 108px;
    }

    @media (max-width: 900px) {
        .cache-panel > .el-card__body {
            padding: 20px;
        }

        .action-card {
            flex-wrap: wrap;
        }

        .action-content {
            width: calc(100% - 60px);
        }

        .action-control {
            flex-basis: 100%;
            margin: 18px 0 0 60px;
            text-align: left;
        }
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {},
            watch: {},
            filters: {},
            methods: {
                submit: function (type, actionName) {
                    var that = this;
                    var url = '{:api_url("/admin/cache/cache")}';
                    layer.confirm('将执行“' + actionName + '”，对应缓存或日志文件会被立即删除，是否继续？', {
                        title: '确认清理',
                        btn: ['确定', '取消']
                    }, function (confirmIndex) {
                        var data = {
                            "type": type
                        };
                        layer.close(confirmIndex);
                        that.httpPost(url, data, function (res) {
                            if (res.status) {
                                ELEMENT.Message.success(res.msg);
                            } else {
                                ELEMENT.Message.error(res.msg);
                            }
                        });
                    });
                }
            },
            mounted: function () {

            }
        });
    });
</script>
