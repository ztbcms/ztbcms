<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>多文件上传示例</h3>

            <div>
                <template v-for="(file, index) in uploadedFileList">
                    <p>{{file.name}} <span style="font-size: 22px" class="el-icon-delete" @click="deleteItem(index)"></span></p>
                </template>
            </div>

            <el-button type="primary" @click="gotoUploadFile">上传图片</el-button>
        </el-card>


    </div>

    <style>
        .imgListItem {
            height: 40px;
            border: 1px dashed #d9d9d9;
            border-radius: 6px;
            display: inline-flex;
            margin-right: 10px;
            margin-bottom: 10px;
            position: relative;
            cursor: pointer;
            vertical-align: top;
        }

    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    uploadedFileList: [
                        // {
                        //     name: "屏幕快照 2019-02-14 14.32.36.png",
                        //     url: "/d/file/module_upload_images/2019/03/5c7e4cf7dd1cd.png"
                        // },
                    ]
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
                            title: '上传图片',
                            content: "{:U('Upload/UploadCenter/fileUploadPanel', ['max_upload' => 9])}",
                            area: ['60%', '50%'],
                        })
                    },
                    onUploadedFile: function (event) {
                        var that = this;
                        console.log(event)
                        files = event.detail.files
                        console.log(files)
                        if (files) {

                            files.map(function (item) {
                                that.uploadedFileList.push(item)
                            })
                        }
                    },
                    deleteItem: function (index) {
                        this.uploadedFileList.splice(index, 1)
                    }
                },
                mounted: function () {
                    window.addEventListener('ZTBCMS_UPLOAD_FILE', this.onUploadedFile.bind(this));
                },

            })
        })
    </script>
</block>
