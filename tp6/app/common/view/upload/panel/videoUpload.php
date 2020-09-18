<div>
    <div id="app" style="" v-cloak v-loading="loading">
        <el-card>
            <div slot="header">
                视频上传
            </div>
            <div>
                <el-row>
                    <el-col :span="6">
                        <ul role="menubar" class="el-menu">
                            <li class="el-menu-item-group">
                                <ul class="group_list">
                                    <li class="el-menu-item" style="padding: 0 8px;" @click="selectGroup('all')"
                                        :class="{'group_active' : now_group == 'all'}">
                                        全部
                                    </li>
                                    <li class="el-menu-item" style="padding: 0 8px;" @click="selectGroup(0)"
                                        :class="{'group_active' : now_group == 0}">
                                        未分组
                                    </li>
                                    <template v-for="item in galleryGroupList">
                                        <li class="el-menu-item" style="padding: 0 8px;position: relative;"
                                            :class="{'group_active' : now_group == item.group_id }">
                                            <span @click="selectGroup(item.group_id)"
                                                  style="word-break:break-all; white-space:normal; width:75%;line-height: 20px;vertical-align:middle;display:inline-block;">{{item.group_name}}</span>
                                            <div style="position: absolute;right: 0;top: 0;">
                                                <i class="el-input__icon el-icon-edit group_edit_icon"
                                                   @click="editGroup(item.group_id,item.group_name)"></i>
                                                <i class="el-input__icon el-icon-circle-close group_close"
                                                   @click="handleClose(item.group_id)"></i>
                                            </div>
                                        </li>
                                    </template>
                                </ul>
                                <div class="grid-content" style="padding: 19px;">
                                    <el-button type="primary" @click="addGroup" size="mini">新增分组</el-button>
                                </div>
                            </li>
                        </ul>
                    </el-col>

                    <el-col :span="18">
                        <el-upload
                                :limit="uploadConfig.max_upload"
                                multiple
                                drag
                                :action="uploadConfig.uploadUrl"
                                :accept="uploadConfig.accept"
                                :on-success="handleUploadSuccess"
                                :on-progress="handleUploadProgress"
                                :on-error="handleUploadError"
                                :on-exceed="handleExceed"
                                :data="uploadData"
                                id="upload_input"
                                ref="upload"
                                :show-file-list="false">
                            <div style="display: flex; justify-content: center;align-items: center;line-height: 36px;">
                                <div>
                                    <i class="el-icon-upload"></i>
                                </div>
                                <div style="padding: 0 10px">
                                    <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
                                </div>
                            </div>
                        </el-upload>
                        <div class="grid-content bg-purple-light" style="margin-top: 10px;">
                            <div>
                                <template v-for="(item,index) in galleryList">
                                    <div :key="index"
                                         class="imgListItem">
                                        <el-tooltip class="item" effect="dark" :content="item.filename"
                                                    placement="bottom">
                                            <img :src="item.filethumb"
                                                 style="width:80px;height: 80px;"
                                                 @click="selectImgEvent(index)">
                                        </el-tooltip>
                                        <div v-if="item.is_select" class="is_check" @click="selectImgEvent(index)">
                                            <span style="line-height: 80px;" class="el-icon-check"></span>
                                        </div>
                                    </div>
                                </template>
                                <div>
                                    <el-button v-show="selectdFileList.length > 0" type="danger" size="small"
                                               @click="clickDeleteSelected">删除选中
                                    </el-button>
                                    <el-button v-show="selectdFileList.length > 0" type="primary" size="small"
                                               @click="clickCancelSelected">取消选中
                                    </el-button>
                                    <el-select v-show="selectdFileList.length > 0" v-model="move_group_id"
                                               placeholder="移动至" style="width:130px;margin-left: 10px;" size="small"
                                               @change="moveGroup">
                                        <el-option label="0" value="0">未分组</el-option>
                                        <el-option :label="item.group_name" :value="item.group_id"
                                                   v-for="item in galleryGroupList">{{item.group_name}}
                                        </el-option>
                                    </el-select>
                                </div>
                                <el-pagination
                                        :page-size="pagination.limit"
                                        :current-page.sync="pagination.page"
                                        :total="pagination.total_items"
                                        v-show="pagination.total_items > 0"
                                        background
                                        layout="prev, pager, next"
                                        @current-change="getGalleryByGroupIdList"
                                        style="margin-top: 10px;float: right;padding-right: 50px;"
                                ></el-pagination>
                            </div>
                        </div>
                    </el-col>
                </el-row>
                <div class="footer" style="padding-bottom: 20px;">
                    <el-button type="primary" @click="confirm">确定</el-button>
                    <el-button type="default" @click="closePanel">关闭</el-button>
                </div>
            </div>
        </el-card>
    </div>
    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    uploadConfig: {
                        uploadUrl: "{:urlx('common/upload.panel/videoUpload')}",
                        max_upload: 99,//同时上传文件数
                        accept: 'video/*', //接收文件类型，安全起见只限制文档类型的文件，有需要可以根据需求修改，注意不要不做限制！！
                    },
                    pagination: {
                        page: 1,
                        limit: 15,
                        total_pages: 0,
                        total_items: 0,
                    },
                    callback: "ZTBCMS_UPLOAD_VIDEO",
                    galleryList: [],      //图库
                    galleryGroupList: [], //图库分组
                    now_group: 'all',     // 当前分类ID
                    move_group_id: '',    // 移动至分类ID
                    group_type: "video", // 显示修改分组名称框
                    uploadData: {
                        enable: '0',
                        group_id: 'all'
                    },
                    loading: true,
                    uploadLoading: null
                },
                watch: {},
                computed: {
                    selectdFileList: function () {
                        var result = [];
                        this.galleryList.forEach(function (file) {
                            if (file.is_select) {
                                result.push(file);
                            }
                        });
                        return result;
                    }
                },
                methods: {
                    handleUploadProgress(event, file, fileList) {
                        console.log('handleUploadProgress', event, file, fileList);
                        this.uploadLoading = this.$loading({
                            lock: true,
                            text: '上传中……',
                            spinner: 'el-icon-loading',
                            background: 'rgba(0, 0, 0, 0.7)'
                        })
                    },
                    handleUploadSuccess: function (res, file, fileList) {
                        console.log('handleUploadSuccess', res);
                        this.uploadLoading.close();
                        if (res.status) {
                            this.getGalleryByGroupIdList();
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
                    // 获取分组图片列表
                    getGalleryByGroupIdList: function () {
                        this.loading = true;
                        var that = this;
                        var where = {
                            page: this.pagination.page,
                            limit: this.pagination.limit,
                            group_id: this.now_group,
                            module: this.group_type //视频类型文件
                        };
                        $.ajax({
                            url: "{:urlx('common/upload.panel/getFilesByGroupIdList')}",
                            data: where,
                            dataType: 'json',
                            type: 'get',
                            success: function (res) {
                                that.loading = false;
                                if (res.status) {
                                    var data = res.data;
                                    that.pagination.page = data.current_page;
                                    that.pagination.limit = data.per_page;
                                    that.pagination.total_pages = data.last_page;
                                    that.pagination.total_items = data.total;
                                    var list = [];
                                    data.data.map(function (item) {
                                        item.is_select = false;
                                        list.push(item);
                                    });
                                    that.galleryList = list;
                                }
                            }
                        })
                    },

                    // 获取指定分类的图片
                    selectGroup(group_id) {
                        this.now_group = group_id;
                        this.uploadData.group_id = group_id;
                        // 获取图片列表
                        this.getGalleryByGroupIdList()
                    },
                    // 获取分组列表
                    getGalleryGroup() {
                        var that = this;
                        $.ajax({
                            url: "{:urlx('common/upload.panel/getGalleryGroup')}",
                            dataType: 'json',
                            data: {
                                group_type: that.group_type
                            },
                            type: 'get',
                            success: function (res) {
                                that.galleryGroupList = res.data
                            }
                        })
                    },
                    // 添加分组
                    addGroup() {
                        var that = this;
                        this.$prompt('请输入分组名称', '新增分组', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                        }).then(({value}) => {
                            $.ajax({
                                url: "{:urlx('common/upload.panel/addGalleryGroup')}",
                                dataType: "json",
                                type: "post",
                                data: {
                                    group_name: value,
                                    group_type: that.group_type
                                },
                                success(res) {
                                    if (res.status) {
                                        that.$message({
                                            type: 'success',
                                            message: res.msg
                                        });
                                        that.getGalleryGroup();
                                    } else {
                                        that.$message({
                                            type: 'false',
                                            message: res.msg
                                        });
                                    }
                                }
                            })
                        }).catch(() => {
                        });
                    },
                    // 显示修改分类名称框
                    editGroup(group_id, group_name) {
                        this.$prompt('请输入分组名称', '编辑分组', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            inputValue: group_name
                        }).then(({value}) => {
                            var that = this;
                            $.ajax({
                                url: "{:urlx('common/upload.panel/editGalleryGroup')}",
                                dataType: "json",
                                type: "post",
                                data: {
                                    group_id: group_id,
                                    group_name: value
                                },
                                success(res) {
                                    if (res.status) {
                                        that.$message({
                                            type: 'success',
                                            message: res.msg
                                        });
                                        that.getGalleryGroup();
                                    } else {
                                        that.$message({
                                            type: 'false',
                                            message: res.msg
                                        });
                                    }
                                }
                            })
                        }).catch(() => {
                        });
                    },
                    // 删除分类
                    handleClose(group_id) {
                        var that = this;
                        that.$confirm('是否确定删除该分组吗？').then(() => {
                            $.ajax({
                                url: "{:urlx('common/upload.panel/delGalleryGroup')}",
                                dataType: "json",
                                type: "post",
                                data: {
                                    group_id: group_id
                                },
                                success(res) {
                                    if (res.status) {
                                        that.getGalleryGroup();
                                        that.$message({
                                            type: 'success',
                                            message: res.msg
                                        });
                                    } else {
                                        that.$message({
                                            type: 'false',
                                            message: res.msg
                                        });
                                    }
                                }
                            })
                        }).catch(err => {
                        });
                    },
                    // 移动分组
                    moveGroup: function () {
                        var that = this;
                        var files;
                        for (var i = 0; i < this.selectdFileList.length; i++) {
                            files.push(this.selectdFileList[i])
                        }
                        $.ajax({
                            url: "{:urlx('common/upload.panel/moveGralleryGroup')}",
                            data: {
                                files,
                                group_id: that.move_group_id
                            },
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                layer.msg(res.msg);
                                // 获取图片列表
                                that.getGalleryByGroupIdList();
                                that.move_group_id = ""
                            }
                        })
                    },
                    //选择图片
                    selectImgEvent: function (index) {
                        this.galleryList[index].is_select = !this.galleryList[index].is_select
                    },
                    confirm: function () {
                        console.log('selectdFileList', JSON.stringify(this.selectdFileList));
                        var event = document.createEvent('CustomEvent');
                        event.initCustomEvent(this.callback, true, true, {
                            files: this.selectdFileList
                        });
                        window.parent.dispatchEvent(event);
                        this.closePanel();
                    },
                    closePanel: function () {
                        if (parent.window.layer) {
                            parent.window.layer.closeAll();
                        } else {
                            window.close();
                        }
                    },
                    // 取消选中
                    clickCancelSelected: function () {
                        for (var i = 0; i < this.galleryList.length; i++) {
                            this.galleryList[i].is_select = false
                        }
                    },
                    // 删除选中
                    clickDeleteSelected: function () {
                        this.$confirm('确认删除？', {
                            type: 'warning'
                        }).then(res => {
                            //确认回掉
                            this.doDeleteSelected()
                        }).catch(err => {
                        });
                    },
                    doDeleteSelected: function () {
                        var that = this;
                        var files = [];
                        for (var i = 0; i < this.selectdFileList.length; i++) {
                            files.push(this.selectdFileList[i])
                        }
                        $.ajax({
                            url: "{:urlx('common/upload.panel/deleteFiles')}",
                            data: {
                                files
                            },
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                layer.msg(res.msg);
                                //获取图片列表
                                that.getGalleryByGroupIdList()
                            }
                        })
                    },
                },
                mounted: function () {
                    //获取分组列表
                    this.getGalleryGroup();
                    //获取图片列表
                    this.getGalleryByGroupIdList();

                    this.uploadConfig.max_upload = parseInt(this.getUrlQuery('max_upload') || this.uploadConfig.max_upload);
                    this.callback = this.getUrlQuery('callback') || this.callback
                }
            })
        })
    </script>
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

        /*图库*/
        .imgListItem {
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

        .group_list {
            height: 330px;
            overflow: scroll;
            border-bottom: 1px solid gainsboro;
        }

        .el-menu {
            border: none;
            padding-right: 20px;
        }

        .el-menu-item {
            height: 30px;
            line-height: 30px;
        }

        .el-menu-item:focus {
            outline: none;
            background-color: #ecf5ff;
        }

        .group_close {
            font-size: 15px;
            line-height: 30px;
        }

        .group_edit_icon {
            font-size: 15px;
            line-height: 30px;
        }

        .group_active {
            /*background-color: #409eff;*/
            color: #409eff;
        }

        .group_item {
            width: 85%;
            margin: 3px 10px;
            padding: 4px 10px;
            font-size: 13px;
            background-color: #fff;
            border-color: #b3d8ff;
            color: #409eff;
            height: 36px;
        }

        .el-menu-item i {
            color: #303133;
            opacity: 0;
        }

        .el-menu-item:hover i {
            opacity: 0.9;
        }

        .el-upload-dragger {
            height: 36px;
            line-height: 30px;
            text-align: right;
            padding: 0 2px;
        }

        .el-upload-dragger .el-icon-upload {
            font-size: 18px !important;
            color: #C0C4CC;
            line-height: 22px;
            margin: 0;
        }

    </style>
</div>