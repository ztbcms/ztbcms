<div>
    <div id="app" style="padding: 8px;" v-cloak>
        <div>
            <el-card>
                <h3>前端上传示例</h3>
                <el-upload
                        :limit="9"
                        multiple
                        action="{:api_url('common/upload.api/imageUpload')}"
                        accept="image/*"
                        :on-success="handleUploadSuccess"
                        :on-error="handleUploadError"
                        :on-exceed="handleExceed"
                        :data="uploadData"
                        id="upload_input"
                        ref="upload"
                        :show-file-list="false">
                    <el-button size="small" type="default">上传图片</el-button>
                </el-upload>

                <div style="margin-top: 20px">
                    <el-upload
                            :limit="9"
                            multiple
                            action="{:api_url('common/upload.api/imageUpload')}"
                            accept="image/*"
                            :on-success="handleUploadSuccess"
                            :on-error="handleUploadError"
                            :on-exceed="handleExceed"
                            :data="{is_private:1}"
                            id="upload_input"
                            ref="upload"
                            :show-file-list="false">
                        <el-button size="small" type="danger">上传图片（私有读）</el-button>
                    </el-upload>
                </div>

                <div style="margin-top: 20px">
                    <el-upload
                            :limit="9"
                            multiple
                            action="{:api_url('common/upload.api/videoUpload')}"
                            accept="video/*"
                            :on-success="handleUploadSuccess"
                            :on-error="handleUploadError"
                            :on-exceed="handleExceed"
                            :data="uploadData"
                            id="upload_input"
                            ref="upload"
                            :show-file-list="false">
                        <el-button size="small" type="default">上传视频</el-button>
                    </el-upload>
                </div>
                <div style="margin-top: 20px">
                    <el-upload
                            :limit="9"
                            multiple
                            action="{:api_url('common/upload.api/fileUpload')}"
                            accept=".xls,.doc,.ppt,.xlsx,.docx,.pptx,.pdf"
                            :on-success="handleUploadSuccess"
                            :on-error="handleUploadError"
                            :on-exceed="handleExceed"
                            :data="uploadData"
                            id="upload_input"
                            ref="upload"
                            :show-file-list="false">
                        <el-button size="small" type="default">上传文件</el-button>
                    </el-upload>
                </div>
                <div style="margin-top: 20px">
                    <div>上传结果</div>
                    <div style="word-break: break-all;">
                        {{uploadRes}}
                    </div>
                </div>
            </el-card>
        </div>
        <el-card style="margin-top: 20px;">
            <h3>后台上传示例</h3>
            <div>
                <template v-for="(file, index) in uploadedImageList">
                    <div :key="index" class="imgListItem">
                        <img :src="file.fileurl" :alt="file.filename" style="width: 128px;height: 128px;">
                        <div class="deleteMask">
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-delete"
                                  @click="deleteImageItem(index)"></span>
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-zoom-in"
                                  @click="previewImageItem(index)"></span>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="gotoUploadImage(0)">上传图片</el-button>
            <el-button type="primary" @click="gotoUploadImageByIframe">上传图片（iframe）</el-button>
            <el-button type="danger" @click="gotoUploadImage(1)">上传图片(私有读)</el-button>
            <span style="color: #666;font-size: 14px;">私有读：目前支持阿里云OSS私有读配置(视频，文件同理)，私有读文件需要存文件 aid，每次获取都需要返回临时地址</span>
            <div style="margin-top: 20px">
                <template v-for="(file, index) in uploadedVideoList">
                    <div :key="index" class="imgListItem">
                        <img :src="file.filethumb" style="width: 128px;height: 128px;">
                        <div class="deleteMask">
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-delete"
                                  @click="deleteVideoItem(index)"></span>
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-zoom-in"
                                  @click="previewVideoItem(index)"></span>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="gotoUploadVideo">上传视频</el-button>
            <el-button type="primary" @click="gotoUploadVideoByIframe">上传视频（iframe）</el-button>

            <div style="margin-top: 20px">
                <template v-for="(file, index) in uploadeFileList">
                    <div :key="index" class="imgListItem">
                        <img :src="file.filethumb" style="width: 128px;height: 128px;">
                        <div class="deleteMask">
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-delete"
                                  @click="deleteFileItem(index)"></span>
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-zoom-in"
                                  @click="previewFileItem(index)"></span>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="gotoUploadFile">上传文件</el-button>
            <el-button type="primary" @click="gotoUploadFileByIframe">上传文件（iframe）</el-button>

            <div style="margin-top: 20px;line-height: 0;">
                <ueditor-simplicity></ueditor-simplicity>
            </div>
<!--            <div style="margin-top: 20px;line-height: 0;">-->
<!--                <textarea id="editor_content" style="height: 500px;width: 390px;"></textarea>-->
<!--            </div>-->
        </el-card>
        <select-image :show="show_image" :is_private="is_private" @confirm="confirmImage"
                      @close="show_image=false"></select-image>
        <select-video :show="show_video" :is_private="is_private" @confirm="confirmVideo"
                      @close="show_video=false"></select-video>
        <select-file :show="show_file" :is_private="is_private" @confirm="confirmFile"
                     @close="show_file=false"></select-file>
    </div>

    <!-- 引入UEditor   -->
    {ztbcms:include file="common/@/components/ueditor-simplicity"}
    {ztbcms:include file="common/@/components/select-image"}
    {ztbcms:include file="common/@/components/select-video"}
    {ztbcms:include file="common/@/components/select-file"}
    <style>
        .imgListItem {
            height: 128px;
            border: 1px dashed #d9d9d9;
            border-radius: 6px;
            display: inline-flex;
            margin-right: 10px;
            margin-bottom: 10px;
            position: relative;
            cursor: pointer;
            vertical-align: top;
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
    </style>

    <script>
        $(document).ready(function () {
            var ueditorInstance = UE.getEditor('editor_content');
            new Vue({
                el: '#app',
                data: {
                    show_file: false,
                    show_video: false,
                    is_private: false,
                    show_image: false,
                    uploadData: {},
                    uploadedImageList: [],
                    uploadedVideoList: [],
                    uploadeFileList: [],
                    uploadRes: ""
                },
                methods: {
                    handleUploadSuccess: function (res, file, fileList) {
                        console.log('handleUploadSuccess', res);
                        if (res.status) {
                            this.uploadRes = JSON.stringify(res);
                        } else {
                            this.$message({
                                type: 'error',
                                message: res.msg
                            });
                        }
                    },
                    handleUploadError: function () {
                        ELEMENT.Message.error('上传失败');
                    },
                    handleExceed: function () {
                        ELEMENT.Message.error('上传文件数量超限制');
                    },
                    gotoUploadFile: function () {
                        console.log('gotoUploadFile')
                        this.is_private = 0
                        this.show_file = true
                    },
                    confirmFile: function (files) {
                        console.log(files);
                        if (files) {
                            files.map(item => {
                                this.uploadeFileList.push(item)
                            })
                        }
                    },
                    deleteFileItem: function (index) {
                        this.uploadeFileList.splice(index, 1)
                    },
                    gotoUploadVideo: function () {
                        console.log('onUploadedVideo')
                        this.is_private = 0
                        this.show_video = true
                    },
                    confirmVideo: function (files) {
                        console.log(files);
                        if (files) {
                            files.map(item => {
                                this.uploadedVideoList.push(item)
                            })
                        }
                    },
                    deleteVideoItem: function (index) {
                        this.uploadedVideoList.splice(index, 1)
                    },
                    gotoUploadImage: function (isPrivate) {
                        console.log('gotoUploadImage')
                        this.is_private = isPrivate
                        this.show_image = true
                    },
                    confirmImage: function (files) {
                        console.log('confirmImage', files);
                        if (files) {
                            files.map((item) => {
                                this.uploadedImageList.push(item)
                            })
                        }
                    },
                    deleteImageItem: function (index) {
                        this.uploadedImageList.splice(index, 1)
                    },
                    // 预览图片
                    previewImageItem: function (index) {
                        window.open(this.uploadedImageList[index]['fileurl'])
                    },
                    // 预览视频
                    previewVideoItem: function (index) {
                        window.open(this.uploadedVideoList[index]['fileurl'])
                    },
                    // 预览文件
                    previewFileItem: function (index) {
                        window.open(this.uploadeFileList[index]['fileurl'])
                    },
                    gotoUploadImageByIframe: function () {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:api_url('common/upload.panel/imageUpload')}",
                            area: ['720px', '550px'],
                        })
                    },
                    onUploadedImage: function (event) {
                        var that = this;
                        console.log(event);
                        var files = event.detail.files;
                        console.log(files);
                        if (files) {
                            files.map(function (item) {
                                that.uploadedImageList.push(item)
                            })
                        }
                    },
                    gotoUploadVideoByIframe: function () {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:api_url('common/upload.panel/videoUpload')}",
                            area: ['720px', '550px'],
                        })
                    },
                    onUploadedVideo: function (event) {
                        var that = this;
                        console.log(event);
                        var files = event.detail.files;
                        console.log(files);
                        if (files) {
                            files.map(function (item) {
                                that.uploadedVideoList.push(item)
                            })
                        }
                    },
                    gotoUploadFileByIframe: function () {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:api_url('common/upload.panel/fileUpload')}",
                            area: ['720px', '550px'],
                        })
                    },
                    onUploadedFile: function (event) {
                        var that = this;
                        console.log(event);
                        var files = event.detail.files;
                        console.log(files);
                        if (files) {
                            files.map(function (item) {
                                that.uploadeFileList.push(item)
                            })
                        }
                    },
                },
                mounted: function () {
                    // 弹框模式
                    window.addEventListener('ZTBCMS_UPLOAD_IMAGE', this.onUploadedImage.bind(this));
                    window.addEventListener('ZTBCMS_UPLOAD_VIDEO', this.onUploadedVideo.bind(this));
                    window.addEventListener('ZTBCMS_UPLOAD_FILE', this.onUploadedFile.bind(this));
                },
            })
        })
    </script>
</div>