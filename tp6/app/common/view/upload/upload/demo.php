<div>
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>图片上传示例</h3>
            <div>
                <template v-for="(file, index) in uploadedImageList">
                    <div class="imgListItem">
                        <img :src="file.fileurl" :alt="file.filename" style="width: 128px;height: 128px;">
                        <div class="deleteMask" @click="deleteImageItem(index)">
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-delete"></span>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="gotoUploadImage">上传图片</el-button>
        </el-card>
        <el-card style="margin-top: 10px">
            <h3>视频上传示例</h3>
            <div>
                <template v-for="(file, index) in uploadedVideoList">
                    <div class="imgListItem">
                        <img :src="file.filethumb" style="width: 128px;height: 128px;">
                        <div class="deleteMask" @click="deleteVideoItem(index)">
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-delete"></span>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="gotoUploadVideo">上传图片</el-button>
        </el-card>
        <el-card style="margin-top: 10px">
            <h3>文件上传示例</h3>
            <div>
                <template v-for="(file, index) in uploadeFileList">
                    <div class="imgListItem">
                        <img :src="file.filethumb" style="width: 128px;height: 128px;">
                        <div class="deleteMask" @click="deleteFileItem(index)">
                            <span style="line-height: 128px;font-size: 22px" class="el-icon-delete"></span>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="gotoUploadFile">上传文件</el-button>
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
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    uploadedImageList: [],
                    uploadedVideoList: [],
                    uploadeFileList: []
                },
                watch: {},
                filters: {
                    formatTime(timestamp) {
                        var date = new Date();
                        date.setTime(parseInt(timestamp) * 1000);
                        return moment(date).format('YYYY-MM-DD HH:mm:ss')
                    }
                },
                methods: {
                    gotoUploadFile: function () {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:urlx('common/upload.panel/fileUpload')}",
                            area: ['670px', '550px'],
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
                    deleteFileItem: function (index) {
                        this.uploadeFileList.splice(index, 1)
                    },
                    gotoUploadVideo: function () {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:urlx('common/upload.panel/videoUpload')}",
                            area: ['670px', '550px'],
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
                    deleteVideoItem: function (index) {
                        this.uploadedVideoList.splice(index, 1)
                    },
                    gotoUploadImage: function () {
                        layer.open({
                            type: 2,
                            title: '',
                            closeBtn: false,
                            content: "{:urlx('common/upload.panel/imageUpload')}",
                            area: ['670px', '550px'],
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
                    deleteImageItem: function (index) {
                        this.uploadedImageList.splice(index, 1)
                    }
                },
                mounted: function () {
                    window.addEventListener('ZTBCMS_UPLOAD_IMAGE', this.onUploadedImage.bind(this));
                    window.addEventListener('ZTBCMS_UPLOAD_VIDEO', this.onUploadedVideo.bind(this));
                    window.addEventListener('ZTBCMS_UPLOAD_FILE', this.onUploadedFile.bind(this));
                },
            })
        })
    </script>
</div>