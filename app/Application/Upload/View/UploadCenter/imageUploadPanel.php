<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="" v-cloak>
        <el-tabs v-model="activeName" type="border-card" @tab-click="handleTabClick">
            <el-tab-pane label="本地上传" name="uploadLocal">
                <p>支持同时上传 <span style="color: orangered;">{{uploadConfig.max_upload}}</span> 个文件 支持格式：<span style="color: orangered;">jpg,jpeg,gif,png,bmp</span></p>
                <p><el-checkbox v-model="watermarkConfig.enable" true-label="1" false-label="0">添加水印</el-checkbox></p>
                <el-upload
                        ref="uploadRef"
                        :limit="uploadConfig.max_upload"
                        :action="uploadConfig.uploadUrl"
                        :accept="uploadConfig.accept"
                        :on-remove="handleRemove"
                        :on-success="handleUploadSuccess"
                        :on-error="handleUploadError"
                        :on-exceed="handleExceed"
                        :data="watermarkConfig"
                        multiple
                        list-type="picture-card"
                        class="thumb-uploader">
                    <span class="el-icon-plus" style="font-size: 27px;color: #909399;"></span>
                </el-upload>
            </el-tab-pane>

            <el-tab-pane label="图库" name="gallery">
                <div>
                    <template v-for="(img,index) in galleryList">
                        <div :key="index"
                             class="imgListItme">
                            <img :src="img.url"
                                    style="width:80px;height: 80px;"
                                    alt="img.name"
                                    @click="selectImgEvent(index)">
                            <div v-if="img.is_select" class="is_check" @click="selectImgEvent(index)">
                                <span style="line-height: 80px;" class="el-icon-check"></span>
                            </div>

                        </div>
                    </template>
                    <div>
                        <el-button v-show="selectdImageList.length > 0" type="danger" size="small"  @click="clickDeleteSelected">删除选中</el-button>
                        <el-button v-show="selectdImageList.length > 0" type="primary" size="small" @click="clickCancelSelected">取消选中</el-button>
                    </div>
                    <el-pagination
                            :page-size="pagination.limit"
                            :current-page.sync="pagination.page"
                            :total="pagination.total_items"
                            background
                            layout="prev, pager, next"
                            @current-change="getGalleryList"
                            style="margin-top: 10px"
                    ></el-pagination>
                </div>
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
                        uploadUrl: "{:U('Upload/UploadAdminApi/uploadImage')}",
                        max_upload: 6,//同时上传文件数
                        accept: 'image/*', //接收的文件类型，请看：https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-accept
                    },
                    uploadedLocalFileList: [], //本地上传的文件
                    pagination: {
                        page: 1,
                        limit: 20,
                        total_pages: 0,
                        total_items: 0,
                    },
                    galleryList: [], //图库
                    form: {
                        search_date: [],
                        uid: '',
                        ip: '',
                        start_time: '',
                        end_time: '',
                        status: '',
                        sort_time: '',//排序：时间
                    },
                    watermarkConfig: {
                        enable: '0'
                    }
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

                        if (this.activeName == 'gallery') {
                            this.galleryList.forEach(function (file) {
                                if (file.is_select) {
                                    result.push({
                                        aid: file.aid,
                                        url: file.url,
                                        name: file.name
                                    })
                                }
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

                        if (this.activeName == 'gallery') {
                            this.getGalleryList();
                        }
                    },
                    handleRemove: function () {

                    },
                    handleUploadSuccess: function (response, file, fileList) {
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
                    getGalleryList: function () {
                        var that = this;
                        var where = {
                            page: this.pagination.page,
                            limit: this.pagination.limit,
                        };
                        $.ajax({
                            url: "{:U('Upload/UploadAdminApi/getGalleryList')}",
                            data: where,
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                var data = res.data;
                                that.pagination.page = data.page;
                                that.pagination.limit = data.limit;
                                that.pagination.total_pages = data.total_pages;
                                that.pagination.total_items = data.total_items;
                                var list = [];
                                data.items.map(function (item) {
                                    item.is_select = false;
                                    list.push(item);
                                })
                                that.galleryList = list
                            }
                        })
                    },
                    selectImgEvent: function (index) {
                        this.galleryList[index].is_select = !this.galleryList[index].is_select
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
                    },
                    //获取水印配置
                    getWatermarkConfig: function(){
                        var that = this;
                        $.ajax({
                            url: "{:U('Upload/UploadAdminApi/getWatermarkConfig')}",
                            data: {},
                            dataType: 'json',
                            type: 'get',
                            success: function (res) {
                                that.watermarkConfig.enable = res.data.watermarkenable + ''
                            }
                        })
                    },
                    // 取消选中
                    clickCancelSelected: function(){
                        for (var i = 0; i < this.galleryList.length; i++) {
                            this.galleryList[i].is_select = false
                        }
                    },
                    // 删除选中
                    clickDeleteSelected: function(){
                        var that = this
                        layer.confirm('确认删除？', {
                            title: '提示',
                            btn: ['确认', '取消'] //按钮
                        }, function () {
                            //确认回掉
                            that.doDeleteSelected()
                        }, function () {
                            //取消回掉
                        });
                    },
                    doDeleteSelected: function(){
                        var that = this;
                        var form = {
                            files: []
                        }
                        for (var i = 0; i < this.selectdImageList.length; i++) {
                            form['files'].push(this.selectdImageList[i])
                        }

                        $.ajax({
                            url: "{:U('Upload/UploadAdminApi/deleteFiles')}",
                            data: form,
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                layer.msg(res.msg)
                                that.getGalleryList()
                            }
                        })
                    }
                },
                mounted: function () {
                    this.getWatermarkConfig()
                    this.uploadConfig.max_upload = parseInt(this.getUrlQuery('max_upload') || this.uploadConfig.max_upload);
                }
            })
        })
    </script>
</block>
