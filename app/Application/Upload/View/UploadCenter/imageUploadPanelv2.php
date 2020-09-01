<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="" v-cloak v-loading="loading">
        <el-card>
            <div slot="header">
                图片上传
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
                                            @click="selectGroup(item.group_id)"
                                            :class="{'group_active' : now_group == item.group_id }">
                                            <span style="word-break:break-all; white-space:normal; width:75%;line-height: 20px;vertical-align:middle;display:inline-block;">{{item.group_name}}</span>

                                            <i class="el-input__icon el-icon-edit group_edit_icon"
                                               @click="showEditGroupDialog(item.group_id,item.group_name)"></i>
                                            <i class="el-input__icon el-icon-circle-close group_close"
                                               @click="handleClose(item.group_id)"></i>
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
                                :action="uploadConfig.uploadUrl"
                                :accept="uploadConfig.accept"
                                :on-success="handleUploadSuccess"
                                :on-error="handleUploadError"
                                :on-exceed="handleExceed"
                                :data="watermarkConfig"
                                id="upload_input"
                                ref="upload"
                                multiple
                                :show-file-list="false"
                                accept="image/*">
                            <el-button size="small" type="default"><i class="el-icon-plus"></i>点击上传</el-button>
                        </el-upload>
                        <div class="grid-content bg-purple-light" style="margin-top: 10px;">
                            <div>
                                <template v-for="(img,index) in galleryList">
                                    <div :key="index"
                                         class="imgListItem">
                                        <img :src="img.url"
                                             style="width:80px;height: 80px;"
                                             @click="selectImgEvent(index)">
                                        <div v-if="img.is_select" class="is_check" @click="selectImgEvent(index)">
                                            <span style="line-height: 80px;" class="el-icon-check"></span>
                                        </div>
                                    </div>
                                </template>
                                <div>
                                    <el-button v-show="selectdImageList.length > 0" type="danger" size="small"
                                               @click="clickDeleteSelected">删除选中
                                    </el-button>
                                    <el-button v-show="selectdImageList.length > 0" type="primary" size="small"
                                               @click="clickCancelSelected">取消选中
                                    </el-button>
                                    <el-select v-show="selectdImageList.length > 0" v-model="move_group_id"
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
        <el-dialog title="修改分组名称" :visible.sync="showEditGroup">
            <el-form label-width="80px">
                <el-form-item label="分组名称">
                    <el-input v-model="edit_group_name" autocomplete="off"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="mini" @click="showEditGroup = false">取消</el-button>
                <el-button size="mini" type="primary" @click="editGroup">确定</el-button>
            </div>
        </el-dialog>
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

    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    uploadConfig: {
                        uploadUrl: "{:U('Upload/UploadAdminApi/uploadImage')}",
                        max_upload: 9,//同时上传文件数
                        accept: 'image/*', //接收的文件类型，请看：https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-accept
                    },
                    pagination: {
                        page: 1,
                        limit: 15,
                        total_pages: 0,
                        total_items: 0,
                    },
                    galleryList: [],      //图库
                    galleryGroupList: [], //图库分组

                    now_group: 'all',     // 当前分类ID
                    move_group_id: '',    // 移动至分类ID
                    showEditGroup: false, // 显示修改分组名称框
                    edit_group_id: 0,     // 当前修改的id
                    edit_group_name: '',  // 当前修改的名称

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
                    },
                    loading: true
                },
                watch: {},
                computed: {
                    selectdImageList: function () {
                        var result = [];
                        this.galleryList.forEach(function (file) {
                            if (file.is_select) {
                                result.push({
                                    aid: file.aid,
                                    url: file.url,
                                    name: file.name
                                })
                            }
                        });
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
                    handleUploadSuccess: function (response, file, fileList) {
                        console.log('handleUploadSuccess', response);
                        this.getGalleryByGroupIdList();
                    },
                    handleUploadError: function () {
                        ELEMENT.Message.error('上传失败');
                    },
                    handleExceed: function () {
                        ELEMENT.Message.error('上传文件数量超限制');
                    },
                    // 获取分组列表
                    getGalleryGroup() {
                        var that = this;
                        $.ajax({
                            url: "{:U('Upload/UploadAdminApi/getGalleryGroup')}",
                            dataType: 'json',
                            type: 'get',
                            success: function (res) {
                                that.galleryGroupList = res.data
                            }
                        })
                    },
                    // 获取分组图片列表
                    getGalleryByGroupIdList: function () {
                        this.loading = true;
                        var that = this;
                        var where = {
                            page: this.pagination.page,
                            limit: this.pagination.limit,
                            group_id: this.now_group,
                        };
                        $.ajax({
                            url: "{:U('Upload/UploadAdminApi/getGalleryByGroupIdList')}",
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
                                });
                                that.galleryList = list;
                                that.loading = false
                            }
                        })
                    },

                    // 获取指定分类的图片
                    selectGroup(group_id) {
                        this.now_group = group_id;
                        // 获取图片列表
                        this.getGalleryByGroupIdList()
                    },

                    // 添加分组
                    addGroup() {
                        this.$prompt('请输入分组名称', '新增分组', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                        }).then(({value}) => {
                            var that = this;
                            $.ajax({
                                url: "{:U('Upload/UploadAdminApi/addGalleryGroup')}",
                                dataType: "json",
                                type: "post",
                                data: {
                                    group_name: value
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
                        }).catch(() => {
                        });
                    },
                    // 显示修改分类名称框
                    showEditGroupDialog(group_id, group_name) {
                        this.edit_group_id = group_id;
                        this.edit_group_name = group_name;
                        this.showEditGroup = true
                    },
                    // 修改分类名称
                    editGroup() {
                        var that = this;
                        $.ajax({
                            url: "{:U('Upload/UploadAdminApi/editGalleryGroup')}",
                            dataType: "json",
                            type: "post",
                            data: {
                                group_id: that.edit_group_id,
                                group_name: that.edit_group_name,
                            },
                            success(res) {
                                if (res.status) {
                                    that.getGalleryGroup();
                                    that.$message({
                                        type: 'success',
                                        message: res.msg
                                    });
                                    that.showEditGroup = false

                                } else {
                                    that.$message({
                                        type: 'false',
                                        message: res.msg
                                    });
                                }
                            }
                        })
                    },

                    // 删除分类
                    handleClose(group_id) {
                        var that = this;
                        layer.confirm('是否确定删除该分组吗？', {
                            btn: ['确认', '取消'] //按钮
                        }, function () {
                            that.toDeleteGroup(group_id);
                            layer.closeAll();
                        }, function () {
                            layer.closeAll();
                        });
                    },
                    // 删除分类
                    toDeleteGroup(group_id) {
                        var that = this;
                        $.ajax({
                            url: "{:U('Upload/UploadAdminApi/delGalleryGroup')}",
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
                    },

                    // 移动分组
                    moveGroup: function () {
                        var that = this;
                        var form = {
                            files: [],
                            group_id: this.move_group_id
                        };
                        for (var i = 0; i < this.selectdImageList.length; i++) {
                            form['files'].push(this.selectdImageList[i])
                        }

                        $.ajax({
                            url: "{:U('Upload/UploadAdminApi/moveGralleryGroup')}",
                            data: form,
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                layer.msg(res.msg);
                                // 获取图片列表
                                that.getGalleryByGroupIdList()
                                that.move_group_id = ""
                            }
                        })
                    },
                    //选择图片
                    selectImgEvent: function (index) {
                        this.galleryList[index].is_select = !this.galleryList[index].is_select
                    },
                    confirm: function () {
                        var event = document.createEvent('CustomEvent');
                        event.initCustomEvent('ZTBCMS_UPLOAD_FILE', true, true, {
                            files: this.selectdImageList
                        });
                        window.parent.dispatchEvent(event)
                        this.closePanel();
                    },
                    closePanel: function () {
                        if (parent.window.layer) {
                            parent.window.layer.closeAll();
                        } else {
                            window.close();
                        }
                    },
                    //获取水印配置
                    getWatermarkConfig: function () {
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
                        var form = {
                            files: []
                        };
                        for (var i = 0; i < this.selectdImageList.length; i++) {
                            form['files'].push(this.selectdImageList[i])
                        }

                        $.ajax({
                            url: "{:U('Upload/UploadAdminApi/deleteFiles')}",
                            data: form,
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
                    //获取水印设置
                    this.getWatermarkConfig();
                    //获取分组列表
                    this.getGalleryGroup();
                    //获取图片列表
                    this.getGalleryByGroupIdList();

                    this.uploadConfig.max_upload = parseInt(this.getUrlQuery('max_upload') || this.uploadConfig.max_upload);
                }
            })
        })
    </script>
</block>
