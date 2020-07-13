<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="" v-cloak>
        <el-tabs v-model="activeName" type="border-card" @tab-click="handleTabClick">
            <el-tab-pane label="本地上传" name="uploadLocal" style="max-width: 300px">
                <p>支持同时上传 <span style="color: orangered;">{{uploadConfig.max_upload}}</span> 个文件</p>
                <el-upload
                        ref="uploadRef"
                        :limit="uploadConfig.max_upload"
                        :action="uploadConfig.uploadUrl"
                        :accept="uploadConfig.accept"
                        :on-remove="handleRemove"
                        :on-success="handleUploadSuccess"
                        :on-error="handleUploadError"
                        :on-exceed="handleExceed"
                        multiple

                        class="thumb-uploader">

                    <el-button type="primary" >添加文件</el-button>
                </el-upload>
            </el-tab-pane>


        </el-tabs>

        <div class="footer">
            <el-button type="primary" @click="confirm">确定</el-button>
            <el-button type="default" @click="closePanel">关闭</el-button>
        </div>
    </div>

    <style>
        /* 页面架构 */
        body {
            margin: 0;
        }

        .footer {
            margin-top: 10px;
            margin-right: 16px;
            float: right;
        }

        }
        /* 上传图片    */
        .thumb-uploader .el-upload {
            border: 1px dashed #d9d9d9;
            border-radius: 6px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .thumb-uploader .el-upload:hover {
            border-color: #409EFF;
        }

        .el-upload__input {
            display: none !important;
        }

        /* 上传图片    */

        /*图库*/
        .imgListItme {
            width: 82px;
            height: 82px;
            border: 1px dashed #d9d9d9;
            border-radius: 6px;
            display: inline-flex;
            margin-right: 10px;
            margin-bottom: 10px;
            position: relative;
            cursor: pointer;
            vertical-align: top;
        }

        .is_check {
            position: absolute;
            top: 0;
            left: 0;
            width: 80px;
            height: 80px;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 40px;
        }

        /*图库*/
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    activeName: 'uploadLocal',
                    uploadConfig: {
                        uploadUrl: "{:U('Upload/UploadAdminApi/uploadFile')}",
                        max_upload: 6,//同时上传文件数
                        accept: 'file' //接收的文件类型，请看：https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-accept
                    },
                    uploadedLocalFileList: [], //本地上传的文件
                    pagination: {
                        page: 1,
                        limit: 20,
                        total_pages: 0,
                        total_items: 0,
                    },

                },
                watch: {},
                computed: {
                    selectdImageList: function () {
                        var result = [];
                        if (this.activeName == 'uploadLocal') {
                            this.uploadedLocalFileList.forEach(function (file) {
                                result.push({
                                    url: file.url,
                                    name: file.name
                                })
                            })
                        }

                        return result;
                    }
                },
                filters: {
                    formatTime(timestamp) {
                        var date = new Date();
                        date.setTime(parseInt(timestamp) * 1000);
                        return moment(date).format('YYYY-MM-DD HH:mm:ss')
                    }
                },
                methods: {
                    handleTabClick: function () {
                        if (this.activeName == 'uploadLocal') {

                        }


                    },
                    handleRemove: function () {

                    },
                    handleUploadSuccess: function (response, file, fileList) {
                        console.log(response)
                        if (response.status) {
                            this.uploadedLocalFileList.push({
                                name: response.data.name,
                                url: response.data.url,
                            })
                        }
                    },
                    handleUploadError: function () {
                        ELEMENT.Message.error('上传失败');
                    },
                    handleExceed: function(){
                        ELEMENT.Message.error('上传文件数量超限制');
                    },
                    confirm: function(){
                        var event = document.createEvent('CustomEvent');
                        event.initCustomEvent('ZTBCMS_UPLOAD_FILE', true, true, {
                            files: this.selectdImageList
                        });
                        window.parent.dispatchEvent(event)
                        this.closePanel();
                    },
                    closePanel: function(){
                        if(parent.window.layer){
                            parent.window.layer.closeAll();
                        }else{
                            window.close();
                        }
                    }
                },
                mounted: function () {
                    this.uploadConfig.max_upload = parseInt(this.getUrlQuery('max_upload') || this.uploadConfig.max_upload);
                    this.uploadConfig.accept = this.getUrlQuery('accept') || this.uploadConfig.accept;
                }
            })
        })
    </script>
</block>
