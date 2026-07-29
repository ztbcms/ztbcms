<div>
    <div id="app" style="" v-cloak v-loading="loading">
        <el-card>
            <div slot="header">
                {$panelTitle}
            </div>
            <div>
                <el-row>
                    <el-col :span="6">
                        <div class="sidebar-container">
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
                                </li>
                            </ul>
                            <div class="sidebar-footer">
                                <el-button type="primary" @click="addGroup" size="mini">新增分组</el-button>
                            </div>
                        </div>
                    </el-col>

                    <el-col :span="18">
                        <el-upload
                            :limit="uploadConfig.max_upload"
                            multiple
                            drag
                            action="#"
                            :http-request="doDirectUpload"
                            :accept="uploadConfig.accept"
                            :on-progress="handleUploadProgress"
                            :on-error="handleUploadError"
                            :on-exceed="handleExceed"
                            id="upload_input"
                            ref="upload"
                            :show-file-list="false">
                            <div class="uploader-area">
                                <div>
                                    <i class="el-icon-upload"></i>
                                </div>
                                <div style="padding: 0 10px">
                                    <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
                                </div>
                            </div>
                        </el-upload>
                        <div class="grid-content bg-purple-light" style="margin-top: 10px;">
                            <el-table
                                ref="fileTable"
                                :data="galleryList"
                                size="small"
                                row-key="aid"
                                @selection-change="handleSelectionChange"
                                style="width: 100%;">
                                <el-table-column type="selection" width="50"></el-table-column>
                                <el-table-column v-if="max_select == 1" label="操作" width="70" align="center">
                                    <template slot-scope="scope">
                                        <el-link type="primary" @click="quickSelect(scope.row)" :underline="false" icon="el-icon-check">选择</el-link>
                                    </template>
                                </el-table-column>
                                <el-table-column label="缩略图" width="90" align="center">
                                    <template slot-scope="scope">
                                        <img class="thumb-image" :src="scope.row.preview_url || scope.row.filethumb" @click="previewFile(scope.row)">
                                    </template>
                                </el-table-column>
                                <el-table-column label="名称" min-width="220" prop="filename">
                                    <template slot-scope="scope">
                                        <el-tooltip :content="scope.row.filename" placement="bottom">
                                            <span>{{ scope.row.filename }}</span>
                                        </el-tooltip>
                                    </template>
                                </el-table-column>
                                <el-table-column label="大小" width="120" align="center">
                                    <template slot-scope="scope">
                                        {{ formatFileSize(scope.row.filesize) }}
                                    </template>
                                </el-table-column>
                                <el-table-column label="上传时间" width="170" align="center">
                                    <template slot-scope="scope">
                                        {{ scope.row.create_time }}
                                    </template>
                                </el-table-column>
                            </el-table>
                            <div style="margin-top: 8px">
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
                                style="margin-top: 10px;"></el-pagination>
                        </div>
                    </el-col>
                </el-row>
                <!-- 占位元素，防止内容被固定底栏遮挡 -->
                <div class="footer-placeholder"></div>
            </div>
        </el-card>
        <!-- 固定底部操作栏 -->
        <div class="fixed-footer">
            <el-button type="primary" @click="confirm">确定</el-button>
            <el-button type="default" @click="closePanel">关闭</el-button>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            new Vue({
                el: '#app',
                data: {
                    uploadConfig: {
                        uploadUrl: '',
                        max_upload: "{$maxUpload}",
                        accept: '{$accept}',
                    },
                    pagination: {
                        page: 1,
                        limit: 8,
                        total_pages: 0,
                        total_items: 0,
                    },
                    callback: "{$callbackDefault}",
                    module: '{$module}',
                    galleryList: [], //图库
                    selectedFileList: [], //当前页勾选
                    galleryGroupList: [], //图库分组
                    now_group: 'all', // 当前分类ID
                    move_group_id: '', // 移动至分类ID
                    group_type: '{$groupType}',
                    uploadData: {
                        is_private: '{$isPrivate}', //是否私有配置
                        group_id: 'all'
                    },
                    jwtToken: '',
                    loading: true,
                    uploadLoading: null,
                    max_select: 1 // 最大选择数量，默认1
                },
                watch: {},
                computed: {
                    selectdFileList: function() {
                        return this.selectedFileList;
                    }
                },
                methods: {
                    handleUploadProgress: function(event, file, fileList) {
                        console.log('handleUploadProgress', event, file, fileList);
                        if (!this.uploadLoading) {
                            this.uploadLoading = this.$loading({
                                lock: true,
                                text: '上传中……',
                                spinner: 'el-icon-loading',
                                background: 'rgba(0, 0, 0, 0.7)'
                            })
                        }
                    },
                    handleUploadError: function() {
                        if (this.uploadLoading) {
                            this.uploadLoading.close();
                            this.uploadLoading = null;
                        }
                        ELEMENT.Message.error('上传失败');
                    },
                    handleExceed: function() {
                        ELEMENT.Message.error('上传文件数量超限制');
                    },
                    // 获取分组图片列表
                    getGalleryByGroupIdList: function() {
                        this.loading = true;
                        var that = this;
                        var where = {
                            page: this.pagination.page,
                            limit: this.pagination.limit,
                            group_id: this.now_group,
                            module: this.group_type //视频类型文件
                        };
                        $.ajax({
                            url: "{:api_url('common/upload.panel/getFilesByGroupIdList')}",
                            data: where,
                            dataType: 'json',
                            type: 'get',
                            success: function(res) {
                                that.loading = false;
                                if (res.status) {
                                    let {
                                        file_list = {}, setting = {}
                                    } = res.data
                                    that.pagination.page = file_list.current_page;
                                    that.pagination.limit = file_list.per_page;
                                    that.pagination.total_pages = file_list.last_page;
                                    that.pagination.total_items = file_list.total;
                                    that.uploadConfig = {
                                        ...that.uploadConfig,
                                        ...setting
                                    }
                                    console.log('that.uploadConfig', that.uploadConfig)
                                    var list = [];
                                    file_list.data.map(function(item) {
                                        item.preview_url = that.getPreviewUrl(item);
                                        list.push(item);
                                    });
                                    that.galleryList = list;
                                    that.selectedFileList = [];
                                    that.$nextTick(function() {
                                        if (that.$refs.fileTable) {
                                            that.$refs.fileTable.clearSelection();
                                        }
                                    });
                                }
                            }
                        })
                    },
                    handleSelectionChange: function(rows) {
                        var that = this;
                        if (rows.length > this.max_select) {
                            this.$message.warning('最多只能选择 ' + this.max_select + ' 个文件');
                            // 取消最后选择的项
                            var lastRow = rows[rows.length - 1];
                            this.$nextTick(function() {
                                that.$refs.fileTable.toggleRowSelection(lastRow, false);
                            });
                            return;
                        }
                        this.selectedFileList = rows || [];
                    },
                    getPreviewUrl: function(item) {
                        if (this.group_type === 'image') {
                            return item.fileurl;
                        }
                        return item.filethumb;
                    },
                    previewFile: function(item) {
                        if (item && item.fileurl) {
                            window.open(item.fileurl);
                        }
                    },
                    formatFileSize: function(bytes) {
                        var value = parseInt(bytes, 10);
                        if (!value || value <= 0) {
                            return '-';
                        }
                        var units = ['B', 'KB', 'MB', 'GB'];
                        var index = Math.min(Math.floor(Math.log(value) / Math.log(1024)), units.length - 1);
                        var size = value / Math.pow(1024, index);
                        return size.toFixed(size >= 10 ? 0 : 1) + ' ' + units[index];
                    },
                    formatDateTime: function(timestamp) {
                        var timeValue = parseInt(timestamp, 10);
                        if (!timeValue || timeValue <= 0) {
                            return '-';
                        }
                        var date = new Date(timeValue * 1000);
                        var year = date.getFullYear();
                        var month = String(date.getMonth() + 1).padStart(2, '0');
                        var day = String(date.getDate()).padStart(2, '0');
                        var hour = String(date.getHours()).padStart(2, '0');
                        var minute = String(date.getMinutes()).padStart(2, '0');
                        var second = String(date.getSeconds()).padStart(2, '0');
                        return year + '-' + month + '-' + day + ' ' + hour + ':' + minute + ':' + second;
                    },
                    getFileExt: function(fileName) {
                        if (!fileName || fileName.indexOf('.') === -1) {
                            return '';
                        }
                        return fileName.split('.').pop().toLowerCase();
                    },
                    fetchUploadToken: function() {
                        var that = this;
                        if (this.jwtToken) {
                            return Promise.resolve(this.jwtToken);
                        }
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: "{:api_url('common/upload.upload/getUploadToken')}",
                                type: 'GET',
                                success: function(res) {
                                    if (res.status && res.data && res.data.token) {
                                        that.jwtToken = res.data.token;
                                        resolve(res.data.token);
                                    } else {
                                        reject(new Error(res.msg || '获取上传凭证失败'));
                                    }
                                },
                                error: function() {
                                    reject(new Error('获取上传凭证请求失败'));
                                }
                            });
                        });
                    },
                    fetchDirectCredential: function(fileName) {
                        var that = this;
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: "{:api_url('common/upload.api/getDirectUploadCredential')}",
                                type: 'GET',
                                headers: {
                                    'Authorization': 'Bearer ' + that.jwtToken
                                },
                                data: {
                                    filename: fileName
                                },
                                success: function(res) {
                                    if (res.status && res.data) {
                                        resolve(res.data);
                                    } else {
                                        reject(new Error(res.msg || '获取直传凭证失败'));
                                    }
                                },
                                error: function() {
                                    reject(new Error('获取直传凭证请求失败'));
                                }
                            });
                        });
                    },
                    uploadByCredential: function(file, credential, onProgress) {
                        var that = this;
                        if (credential.driver === 'Local') {
                            return new Promise(function(resolve, reject) {
                                var formData = new FormData();
                                formData.append('file', file);
                                formData.append('module', that.group_type);

                                var xhr = new XMLHttpRequest();
                                xhr.open('POST', credential.upload_url, true);
                                xhr.setRequestHeader('Authorization', 'Bearer ' + that.jwtToken);
                                xhr.upload.onprogress = function(e) {
                                    if (e.lengthComputable && typeof onProgress === 'function') {
                                        onProgress({
                                            percent: Math.round((e.loaded / e.total) * 100)
                                        });
                                    }
                                };
                                xhr.onload = function() {
                                    if (xhr.status === 200) {
                                        var res = JSON.parse(xhr.responseText || '{}');
                                        if (res.status) {
                                            resolve(res.data || {});
                                        } else {
                                            reject(new Error(res.msg || '上传失败'));
                                        }
                                    } else {
                                        reject(new Error('上传失败: ' + xhr.statusText));
                                    }
                                };
                                xhr.onerror = function() {
                                    reject(new Error('上传请求失败'));
                                };
                                xhr.send(formData);
                            });
                        }

                        if (credential.driver === 'Aliyun') {
                            return new Promise(function(resolve, reject) {
                                var client = new OSS({
                                    region: credential.region,
                                    accessKeyId: credential.accessKeyId,
                                    accessKeySecret: credential.accessKeySecret,
                                    stsToken: credential.stsToken,
                                    bucket: credential.bucket,
                                });
                                var key = credential.object_key;
                                client.multipartUpload(key, file, {
                                    progress: function(progress) {
                                        if (typeof onProgress === 'function') {
                                            onProgress({
                                                percent: Math.round(progress * 100)
                                            });
                                        }
                                    }
                                }).then(function() {
                                    resolve({
                                        filepath: key,
                                        fileurl: 'https://' + credential.bucket + '.' + credential.endpoint.replace('https://', '') + '/' + key
                                    });
                                }).catch(function(err) {
                                    reject(err);
                                });
                            });
                        }

                        if (credential.driver === 'Qiniu') {
                            return new Promise(function(resolve, reject) {
                                var formData = new FormData();
                                var key = credential.object_key;
                                formData.append('file', file);
                                formData.append('token', credential.token);
                                formData.append('key', key);

                                var xhr = new XMLHttpRequest();
                                xhr.open('POST', credential.upload_url, true);
                                xhr.upload.onprogress = function(e) {
                                    if (e.lengthComputable && typeof onProgress === 'function') {
                                        onProgress({
                                            percent: Math.round((e.loaded / e.total) * 100)
                                        });
                                    }
                                };
                                xhr.onload = function() {
                                    if (xhr.status === 200) {
                                        var res = JSON.parse(xhr.responseText || '{}');
                                        resolve({
                                            filepath: res.key,
                                            fileurl: credential.domain + '/' + res.key
                                        });
                                    } else {
                                        reject(new Error('上传失败: ' + xhr.statusText));
                                    }
                                };
                                xhr.onerror = function() {
                                    reject(new Error('上传请求失败'));
                                };
                                xhr.send(formData);
                            });
                        }

                        return Promise.reject(new Error('不支持的驱动: ' + credential.driver));
                    },
                    saveDirectUploadRecord: function(result, file, credential) {
                        var that = this;
                        return new Promise(function(resolve, reject) {
                            $.ajax({
                                url: "{:api_url('common/upload.api/saveDirectUploadRecord')}",
                                type: 'POST',
                                headers: {
                                    'Authorization': 'Bearer ' + that.jwtToken
                                },
                                data: {
                                    filepath: result.filepath,
                                    filename: file.name,
                                    filesize: file.size,
                                    fileext: (credential && credential.fileext) ? credential.fileext : that.getFileExt(file.name),
                                    module: that.group_type,
                                    group_id: that.uploadData.group_id === 'all' ? 0 : that.uploadData.group_id,
                                    is_private: that.uploadData.is_private,
                                    driver: credential && credential.driver ? credential.driver : ''
                                },
                                success: function(res) {
                                    if (res.status) {
                                        resolve(res.data || {});
                                    } else {
                                        reject(new Error(res.msg || '保存上传记录失败'));
                                    }
                                },
                                error: function() {
                                    reject(new Error('保存上传记录请求失败'));
                                }
                            });
                        });
                    },
                    doDirectUpload: async function(uploadOption) {
                        var file = uploadOption.file;
                        try {
                            this.handleUploadProgress({}, file, []);
                            await this.fetchUploadToken();
                            var credential = await this.fetchDirectCredential(file.name);
                            var uploadResult = await this.uploadByCredential(file, credential, uploadOption.onProgress);
                            await this.saveDirectUploadRecord(uploadResult, file, credential);
                            this.$message.success('上传成功');
                            this.getGalleryByGroupIdList();
                            if (typeof uploadOption.onSuccess === 'function') {
                                uploadOption.onSuccess({
                                    status: true
                                }, file);
                            }
                        } catch (e) {
                            this.$message.error(e.message || '上传失败');
                            if (typeof uploadOption.onError === 'function') {
                                uploadOption.onError(e);
                            }
                        } finally {
                            if (this.uploadLoading) {
                                this.uploadLoading.close();
                                this.uploadLoading = null;
                            }
                        }
                    },

                    // 获取指定分类的图片
                    selectGroup: function(group_id) {
                        this.now_group = group_id;
                        this.uploadData.group_id = group_id;
                        // 获取图片列表
                        this.getGalleryByGroupIdList()
                    },
                    // 获取分组列表
                    getGalleryGroup: function() {
                        var that = this;
                        $.ajax({
                            url: "{:api_url('common/upload.panel/getGalleryGroup')}",
                            dataType: 'json',
                            data: {
                                group_type: that.group_type
                            },
                            type: 'get',
                            success: function(res) {
                                that.galleryGroupList = res.data
                            }
                        })
                    },
                    // 添加分组
                    addGroup: function() {
                        var that = this;
                        that.$prompt('请输入分组名称', '新增分组', {
                            confirmButtonText: '保存',
                            cancelButtonText: '取消',
                            inputValue: '',
                            roundButton: true,
                            closeOnClickModal: false,
                            beforeClose: function(action, instance, done) {
                                if (action == 'confirm') {
                                    $.ajax({
                                        url: "{:api_url('common/upload.panel/addGalleryGroup')}",
                                        dataType: "json",
                                        type: "post",
                                        data: {
                                            group_name: instance.inputValue,
                                            group_type: that.group_type
                                        },
                                        success: function(res) {
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
                                            done();
                                        }
                                    })
                                } else {
                                    done();
                                }
                            }
                        }).then(function(e) {}).catch(function() {});
                    },
                    // 显示修改分类名称框
                    editGroup: function(group_id, group_name) {


                        var that = this;
                        that.$prompt('请输入分组名称', '编辑分组', {
                            confirmButtonText: '保存',
                            cancelButtonText: '取消',
                            inputValue: group_name,
                            roundButton: true,
                            closeOnClickModal: false,
                            beforeClose: function(action, instance, done) {
                                if (action == 'confirm') {
                                    $.ajax({
                                        url: "{:api_url('common/upload.panel/editGalleryGroup')}",
                                        dataType: "json",
                                        type: "post",
                                        data: {
                                            group_id: group_id,
                                            group_name: instance.inputValue
                                        },
                                        success: function(res) {
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
                                            done();
                                        }
                                    })
                                } else {
                                    done();
                                }
                            }
                        }).then(function(e) {}).catch(function() {});
                    },
                    // 删除分类
                    handleClose: function(group_id) {
                        var that = this;
                        var deleteClose = layer.confirm('是否确定删除该分组吗？', {
                            btn: ['确定', '取消'] //按钮
                        }, function() {
                            var data = {
                                group_id: group_id
                            };
                            $.ajax({
                                url: "{:api_url('common/upload.panel/delGalleryGroup')}",
                                dataType: 'json',
                                type: 'post',
                                data: data,
                                success: function(res) {
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
                                    layer.close(deleteClose);
                                }
                            });
                        });
                    },
                    // 移动分组
                    moveGroup: function() {
                        var that = this;
                        var files = [];
                        for (var i = 0; i < this.selectdFileList.length; i++) {
                            files.push(this.selectdFileList[i])
                        }
                        $.ajax({
                            url: "{:api_url('common/upload.panel/moveGralleryGroup')}",
                            data: {
                                files: files,
                                group_id: this.move_group_id
                            },
                            dataType: 'json',
                            type: 'post',
                            success: function(res) {
                                layer.msg(res.msg);
                                // 获取图片列表
                                that.getGalleryByGroupIdList();
                                that.move_group_id = ""
                            }
                        })
                    },
                    // 快速选择单个文件（仅当max_select=1时使用）
                    quickSelect: function(row) {
                        this.selectedFileList = [row];
                        this.confirm();
                    },
                    confirm: function() {
                        console.log('selectdFileList', JSON.stringify(this.selectdFileList));
                        var event = document.createEvent('CustomEvent');
                        event.initCustomEvent(this.callback, true, true, {
                            files: this.selectdFileList
                        });
                        window.parent.dispatchEvent(event);
                        this.closePanel();
                    },
                    closePanel: function() {
                        if (parent.window.layer) {
                            parent.window.layer.closeAll();
                        } else {
                            window.close();
                        }
                    },
                    // 取消选中
                    clickCancelSelected: function() {
                        this.selectedFileList = [];
                        if (this.$refs.fileTable) {
                            this.$refs.fileTable.clearSelection();
                        }
                    },
                    // 删除选中
                    clickDeleteSelected: function() {
                        this.$confirm('确认删除？', {
                            type: 'warning'
                        }).then(res => {
                            //确认回掉
                            this.doDeleteSelected()
                        }).catch(err => {});
                    },
                    doDeleteSelected: function() {
                        var that = this;
                        var files = [];
                        for (var i = 0; i < this.selectdFileList.length; i++) {
                            files.push(this.selectdFileList[i])
                        }
                        $.ajax({
                            url: "{:api_url('common/upload.panel/deleteFiles')}",
                            data: {
                                files: files
                            },
                            dataType: 'json',
                            type: 'post',
                            success: function(res) {
                                layer.msg(res.msg);
                                //获取图片列表
                                that.getGalleryByGroupIdList()
                            }
                        })
                    },
                },
                mounted: function() {
                    //获取分组列表
                    this.getGalleryGroup();
                    //获取图片列表
                    this.getGalleryByGroupIdList();

                    var query = new URLSearchParams(window.location.search);
                    var maxUpload = parseInt(query.get('max_upload') || this.uploadConfig.max_upload);
                    if (!isNaN(maxUpload) && maxUpload > 0) {
                        this.uploadConfig.max_upload = maxUpload;
                    }
                    this.callback = query.get('callback') || this.callback;

                    // 获取max_select参数，默认为1
                    var maxSelect = parseInt(query.get('max_select'));
                    if (!isNaN(maxSelect) && maxSelect > 0) {
                        this.max_select = maxSelect;
                    }
                }
            })
        })
    </script>
    <style>
        /* 页面架构 */
        body {
            margin: 0;
        }

        #app {
            padding-bottom: 70px;
            /* 为固定底栏留出空间 */
        }

        /* 固定底部操作栏 */
        .fixed-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #fff;
            padding: 15px 20px;
            text-align: center;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .footer-placeholder {
            height: 20px;
        }

        /* 左侧分组栏容器 */
        .sidebar-container {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 180px);
            min-height: 400px;
        }

        .sidebar-container .el-menu {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .sidebar-container .el-menu-item-group {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-footer {
            padding: 15px;
            border-top: 1px solid #e6e6e6;
            background: #fff;
        }

        /* 上传图片 */
        .uploader-area {
            display: flex;
            justify-content: center;
            align-items: center;
            line-height: 36px;
        }

        .el-upload__input {
            display: none !important;
        }

        .thumb-image {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
        }

        .group_list {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            border-bottom: none;
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