<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="" v-cloak>
        <el-tabs v-model="activeName" type="border-card" @tab-click="handleTabClick">
            <el-tab-pane label="本地上传" name="uploadLocal">
                <p>支持同时上传{{uploadConfig.limit}}个文件 支持格式：jpg,jpeg,gif,png,bmp</p>
                <el-upload
                        ref="uploadRef"
                        :limit="uploadConfig.limit"
                        :action="uploadConfig.uploadUrl"
                        :accept="uploadConfig.accept"
                        :on-remove="handleRemove"
                        :on-success="handleUploadSuccess"
                        :on-error="handleUploadError"
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
                            <img
                                    :src="img.url"
                                    style="width:80px;height: 80px;"
                                    alt="img.name"
                                    @click="selectImgEvent(index)">
                            <div v-if="img.is_select" class="is_check" @click="selectImgEvent(index)">
                                <span style="line-height: 80px;" class="el-icon-check"></span>
                            </div>

                        </div>
                    </template>
                    <el-pagination
                            :page-size="pagination.limit"
                            :current-page.sync="pagination.page"
                            :total="pagination.total_items"
                            background
                            layout="prev, pager, next"
                            @current-change="getGalleryList"
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
                        uploadUrl: "{:U('Upload/UploadAdminApi/uploadByAdmin')}",
                        limit: 3,
                        accept: 'image/*'
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
                        console.log(response)
                        if (response.status) {
                            this.uploadedLocalFileList.push({
                                name: response.data.name,
                                url: response.data.url,
                            })
                        }
                    },
                    handleUploadError: function () {
                        ELEMENT.Message.success('上传失败');
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
                                console.log(res)
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
                        event = new CustomEvent('ZTBCMS_UPLOAD_FILE', {
                            detail: {
                                files: this.selectdImageList
                            }
                        })
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

                }
            })
        })
    </script>
</block>
