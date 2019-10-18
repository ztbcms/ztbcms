<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>编辑</h3>
            <div class="filter-container">
                <el-form :model="form" label-width="120px">
                    <el-form-item label="输入框" required>
                        <el-input v-model="form.name" placeholder="请输入名称" style="width: 400px"></el-input>
                    </el-form-item>
                    <el-form-item label="上传单张图片" required>
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
                    <el-form-item label="排序">
                        <el-input v-model="form.order" style="width: 400px"></el-input>
                    </el-form-item>
                    <el-form-item label="排序">
                        <el-input-number v-model="form.order" :min="0"></el-input-number>
                    </el-form-item>
                    <el-form-item label="展示状态">
                        <el-switch
                                v-model="form.show_status"
                                active-color="#13ce66"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                    </el-form-item>
                    <el-form-item required>
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

    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    form: {
                        id: '{:I("get.id")}',
                        picture: [],
                        order: 1,
                        show_status: "1",
                    },
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
                },
                mounted: function () {
                    //上传图片监听回调
                    window.addEventListener('ZTBCMS_UPLOAD_FILE', this.onUploadedFile.bind(this));
                },
            })
        })
    </script>
</block>
