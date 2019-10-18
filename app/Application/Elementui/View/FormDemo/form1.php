<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>编辑</h3>
            <div class="filter-container">
                <el-form :model="form">
                    <el-form-item label="输入框" label-width="120px" required>
                        <el-input v-model="form.name" style="width: 400px" placeholder="请输入内容"></el-input>
                    </el-form-item>
                    <el-form-item label="下拉框" label-width="120px">
                        <template>
                            <el-select v-model="form.select_id" clearable placeholder="请选择">
                                <el-option
                                        v-for="item in select"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </template>
                    </el-form-item>
                    <el-form-item label="下拉框多选样式1" label-width="120px">
                        <template>
                            <el-select v-model="value1" multiple placeholder="请选择" clearable>
                                <el-option
                                        v-for="item in options"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                        </template>
                    </el-form-item>
                    <el-form-item label="下拉框多选样式2" label-width="120px">
                        <template>
                            <el-select
                                    v-model="value2"
                                    multiple
                                    collapse-tags
                                    clearable
                                    placeholder="请选择">
                                <el-option
                                        v-for="item in options"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                        </template>
                    </el-form-item>
                    <el-form-item label="上传单张图片" label-width="120px" required>
                        <span>图片尺寸：50*100px，支持png\jpg 格式</span>
                        <div>
                            <template v-for="(file, index) in form.picture">
                                <div class="imgListItem">
                                    <img :src="file.url" :alt="file.name" style="width: 128px;height: 128px;">
                                    <div class="deleteMask" @click="deleteItem(index)">
                                        <span style="line-height: 128px;font-size: 22px" class="el-icon-delete"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <el-button v-if="form.picture[0]" type="primary" @click="gotoUploadFile">更换</el-button>
                        <el-button v-else type="primary" @click="gotoUploadFile">上传缩略图</el-button>
                    </el-form-item>
                    <el-form-item label="课程类型" label-width="120px" required>
                        <el-select v-model="form.type">
                            <el-option label="视频课程" value="1"></el-option>
                            <el-option label="图文课程" value="2"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item v-if="form.type==1" label="课程视频" label-width="120px" required>
                        <div>
                            <template v-for="(file, index) in form.videos">
                                <div class="imgListItem">
                                    <img :src="file.cover_url" :alt="file.cover_url"
                                         style="width: 128px;height: 128px;">
                                    <div class="deleteMask" @click="deleteItemVideo(index)">
                                        <span style="line-height: 128px;font-size: 22px" class="el-icon-delete"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <el-button v-if="form.videos[0]" type="primary" @click="uploadAliyunVideo">更换
                        </el-button>
                        <el-button v-else type="primary" @click="uploadAliyunVideo">上传视频</el-button>
                    </el-form-item>
                    <el-form-item v-else label="轮播图" label-width="120px" required>
                        <span>图片尺寸：50*100px，支持png\jpg 格式</span>
                        <div>
                            <template v-for="(file, index) in form.banner">
                                <div class="imgListItem">
                                    <img :src="file.url" :alt="file.name" style="width: 128px;height: 128px;">
                                    <div class="deleteMask">
                                        <span class="el-icon-delete delete-icon"
                                              @click="deleteItemBanner(index)"></span>
                                        <div class="position-ctrl">
                                            <span class="pre-icon el-icon-caret-left"
                                                  @click="changePosition(index,-1)"></span>
                                            <span class="next-icon el-icon-caret-right"
                                                  @click="changePosition(index,1)"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <el-button type="primary" @click="gotoUploadBanner">上传</el-button>
                    </el-form-item>
                    <el-form-item label="详细介绍" label-width="120px" required>
                        <div style="line-height: 0;">
                            <textarea id="editor_content" style="height: 400px;width: 375px"></textarea>
                        </div>
                    </el-form-item>
                    <el-form-item label-width="120px" required>
                        <el-button type="primary" @click="doEdit">保存</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>
    </div>

    <style>
        .filter-container {
            padding-bottom: 10px;
        }

        .imgListItem {
            height: 128px;
            border: 1px dashed #d9d9d9;
            border-radius: 6px;
            display: inline-flex;
            margin-right: 10px;
            margin-bottom: 10px;
            position: relative;
            cursor: pointer;
            vertical-align: middle;
        }

        .deleteMask {
            position: absolute;
            top: 0;
            left: 0;
            width: 128px;
            height: 128px;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 40px;
            opacity: 0;
        }

        .deleteMask:hover {
            opacity: 1;
        }

        .delete-icon {
            line-height: 128px;
            font-size: 20px;
        }

        .position-ctrl {
            position: absolute;
            font-size: 20px;
            width: 100%;
            justify-content: space-between;
            bottom: 0;
            left: 0;
        }

        .pre-icon {
            position: absolute;
            left: 10px;
            bottom: 0;
        }

        .next-icon {
            position: absolute;
            right: 10px;
            bottom: 0;
        }

    </style>
    <!-- 引入UEditor   -->
    <include file="../../Admin/View/Common/ueditor"/>
    <script>
        $(document).ready(function () {
            var ueditorInstance = UE.getEditor('editor_content');
            new Vue({
                el: '#app',
                data: {
                    form: {
                        id: '{:I("get.id")}',
                        select_id: '',
                        type: '2',
                        name: '',
                        videos: [],
                        banner: [],
                        picture: [],
                    },
                    select: [
                        {
                            id: 1,
                            name: '选项1'
                        },
                        {
                            id: 2,
                            name: '选项2'
                        }
                    ],
                    options: [{
                        value: '选项1',
                        label: '黄金糕'
                    }, {
                        value: '选项2',
                        label: '双皮奶'
                    }, {
                        value: '选项3',
                        label: '蚵仔煎'
                    }, {
                        value: '选项4',
                        label: '龙须面'
                    }],
                    value1: [],
                    value2: [],
                    tableKey: 0,
                    pictureUploadStatus: 1
                },
                watch: {},
                filters: {},
                methods: {
                    doEdit: function () {
                        layer.msg("保存成功", {time: 1000}, function () {

                        });
                        if (window !== window.parent) {
                            setTimeout(function () {
                                window.parent.layer.closeAll();
                            }, 1000);
                        }
                    },
                    gotoUploadFile: function () {
                        this.pictureUploadStatus = 1;
                        layer.open({
                            type: 2,
                            title: '上传图片',
                            content: "{:U('Upload/UploadCenter/imageUploadPanel', ['max_upload' => 1])}",
                            area: ['60%', '50%'],
                        })
                    },
                    gotoUploadBanner: function () {
                        this.pictureUploadStatus = 2;
                        layer.open({
                            type: 2,
                            title: '上传图片',
                            content: "{:U('Upload/UploadCenter/imageUploadPanel', ['max_upload' => 9])}",
                            area: ['60%', '50%'],
                        })
                    },
                    //上传缩略图处理
                    onUploadedFile: function (event) {
                        var that = this;
                        var files = event.detail.files;
                        if (this.pictureUploadStatus == 1) {
                            that.form.picture = [];
                            that.form.picture.push(files[0]);
                        } else if (this.pictureUploadStatus == 2) {
                            if (files) {

                                files.map(function (item) {
                                    that.form.banner.push(item)
                                })
                            }
                        }
                    },
                    deleteItem: function (index) {
                        this.form.picture.splice(index, 1);
                    },
                    deleteItemBanner: function (index) {
                        this.form.banner.splice(index, 1);
                    },
                    //上传视频处理
                    uploadAliyunVideo: function () {
                        //type 的字段可自行添加或编辑
                        var url = "{:U('Aliyunvideo/VideoPanel/fileUploadPanel',['type'=>1])}";
                        url += "&is_group=1";  //是否开启分组功能 1为开启
                        url += "&is_delete=1"; //是否开启删除功能 1为开启
                        layer.open({
                            type: 2,
                            title: '选择使用视频',
                            content: url,
                            area: ['80%', '595px;']
                        })
                    },
                    deleteItemVideo: function (index) {
                        this.form.videos.splice(index, 1)
                    }, onUploadedFileVideo: function (event) {
                        //获取视频
                        var that = this;
                        var files = event.detail.files;
                        $.ajax({
                            url: "{:U('Aliyunvideo/VideoDemo/aliyunVideoPlay')}",
                            data: {
                                video_id: files
                            }, dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                that.form.videos = [];
                                that.form.videos.push(res.data);
                                console.log(that.form.videos);
                            }
                        });
                    },
                    //交换图片位置
                    changePosition: function (index, exchange) {
                        var that = this;
                        var exchangePosition = index + exchange;
                        if (that.form.banner[exchangePosition]) {
                            var tmp = that.form.banner[index];
                            that.form.banner.splice(index, 1, that.form.banner[exchangePosition]);
                            that.form.banner.splice(exchangePosition, 1, tmp);
                        }
                    }
                },
                mounted: function () {
                    //上传图片监听回调
                    window.addEventListener('ZTBCMS_UPLOAD_FILE', this.onUploadedFile.bind(this));
                    //上传视频回调
                    window.addEventListener('ZTBCMS_ALIYUNVIDEO_VIDEO_FILE', this.onUploadedFileVideo.bind(this));
                },
            })
        })
    </script>
</block>

