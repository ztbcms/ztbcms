<div>
    <div id="app" style="padding: 8px;" v-cloak>
        <div>
            <el-card>
                <h3>前端上传示例</h3>
                <el-button type="primary" @click="gotoDirectUploadDemo">打开直传上传示例</el-button>
            </el-card>
        </div>
        <el-card style="margin-top: 20px;">
            <h3>后台上传示例</h3>

            <!-- 上传图片 -->
            <div style="margin-bottom: 20px;">
                <h4>上传图片</h4>
                <div style="margin-bottom: 10px;">
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
                <el-button type="primary" @click="gotoUploadImageSingle">上传图片（1张）</el-button>
                <el-button type="success" @click="gotoUploadImageMultiple">上传图片（5张）</el-button>
                <el-button type="danger" @click="gotoUploadImage(1)">上传图片（私有读）</el-button>
            </div>

            <!-- 上传视频 -->
            <div style="margin-bottom: 20px;">
                <h4>上传视频</h4>
                <div style="margin-bottom: 10px;">
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
                <el-button type="primary" @click="gotoUploadVideoSingle">上传视频（1个）</el-button>
                <el-button type="success" @click="gotoUploadVideoMultiple">上传视频（5个）</el-button>
            </div>

            <!-- 上传文件 -->
            <div style="margin-bottom: 20px;">
                <h4>上传文件</h4>
                <div style="margin-bottom: 10px;">
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
                <el-button type="primary" @click="gotoUploadFileSingle">上传文件（1个）</el-button>
                <el-button type="success" @click="gotoUploadFileMultiple">上传文件（5个）</el-button>
            </div>
        </el-card>
    </div>


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
        $(document).ready(function() {
            new Vue({
                el: '#app',
                data: {
                    show_file: false,
                    is_private: false,
                    show_image: false,
                    uploadedImageList: [],
                    uploadedVideoList: [],
                    uploadeFileList: []
                },
                methods: {
                    gotoDirectUploadDemo: function() {
                        var title = '直传上传示例';
                        var url = "{:api_url('common/upload.upload/directUploadDemo')}";
                        var ztbcms = window.Ztbcms || (window.parent && window.parent.Ztbcms) || (window.top && window.top.Ztbcms);
                        if (ztbcms && typeof ztbcms.openNewIframeByUrl === 'function') {
                            ztbcms.openNewIframeByUrl(title, url);
                            return;
                        }
                        window.open(url);
                    },
                    gotoUploadFile: function() {
                        console.log('gotoUploadFile')
                        this.is_private = 0
                        this.show_file = true
                    },
                    confirmFile: function(files) {
                        console.log(files);
                        if (files) {
                            files.map(item => {
                                this.uploadeFileList.push(item)
                            })
                        }
                    },
                    deleteFileItem: function(index) {
                        this.uploadeFileList.splice(index, 1)
                    },
                    deleteVideoItem: function(index) {
                        this.uploadedVideoList.splice(index, 1)
                    },
                    gotoUploadImage: function(isPrivate) {
                        console.log('gotoUploadImage')
                        this.is_private = isPrivate
                        this.show_image = true
                    },
                    confirmImage: function(files) {
                        console.log('confirmImage', files);
                        if (files) {
                            files.map((item) => {
                                this.uploadedImageList.push(item)
                            })
                        }
                    },
                    deleteImageItem: function(index) {
                        this.uploadedImageList.splice(index, 1)
                    },
                    // 预览图片
                    previewImageItem: function(index) {
                        window.open(this.uploadedImageList[index]['fileurl'])
                    },
                    // 预览视频
                    previewVideoItem: function(index) {
                        window.open(this.uploadedVideoList[index]['fileurl'])
                    },
                    // 预览文件
                    previewFileItem: function(index) {
                        window.open(this.uploadeFileList[index]['fileurl'])
                    },
                    // 上传图片 - 单张
                    gotoUploadImageSingle: function() {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:api_url('common/upload.panel/index')}?module=image&max_select=1",
                            area: ['80%', '90%'],
                        })
                    },
                    // 上传图片 - 多张
                    gotoUploadImageMultiple: function() {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:api_url('common/upload.panel/index')}?module=image&max_select=5",
                            area: ['80%', '90%'],
                        })
                    },
                    onUploadedImage: function(event) {
                        var that = this;
                        console.log(event);
                        var files = event.detail.files;
                        console.log(files);
                        if (files) {
                            files.map(function(item) {
                                that.uploadedImageList.push(item)
                            })
                        }
                    },
                    // 上传视频 - 单个
                    gotoUploadVideoSingle: function() {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:api_url('common/upload.panel/index')}?module=video&max_select=1",
                            area: ['80%', '90%'],
                        })
                    },
                    // 上传视频 - 多个
                    gotoUploadVideoMultiple: function() {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:api_url('common/upload.panel/index')}?module=video&max_select=5",
                            area: ['80%', '90%'],
                        })
                    },
                    onUploadedVideo: function(event) {
                        var that = this;
                        console.log(event);
                        var files = event.detail.files;
                        console.log(files);
                        if (files) {
                            files.map(function(item) {
                                that.uploadedVideoList.push(item)
                            })
                        }
                    },
                    // 上传文件 - 单个
                    gotoUploadFileSingle: function() {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:api_url('common/upload.panel/index')}?module=file&max_select=1",
                            area: ['80%', '90%'],
                        })
                    },
                    // 上传文件 - 多个
                    gotoUploadFileMultiple: function() {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:api_url('common/upload.panel/index')}?module=file&max_select=5",
                            area: ['80%', '90%'],
                        })
                    },
                    onUploadedFile: function(event) {
                        var that = this;
                        console.log(event);
                        var files = event.detail.files;
                        console.log(files);
                        if (files) {
                            files.map(function(item) {
                                that.uploadeFileList.push(item)
                            })
                        }
                    },
                },
                mounted: function() {
                    // 弹框模式
                    window.addEventListener('ZTBCMS_UPLOAD_IMAGE', this.onUploadedImage.bind(this));
                    window.addEventListener('ZTBCMS_UPLOAD_VIDEO', this.onUploadedVideo.bind(this));
                    window.addEventListener('ZTBCMS_UPLOAD_FILE', this.onUploadedFile.bind(this));
                },
            })
        })
    </script>
</div>