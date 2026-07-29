<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>图片上传裁剪示例</h3>
            <div>
                <template v-for="(file, index) in uploadedFileCutList">
                    <div class="imgListItem">
                        <img :src="file.url" :alt="file.name" style="width: 128px;height: 128px;">
                        <div class="Mask">
                            <div class="deleteMask" @click="deleteCutItem(index)">
                                <span style="line-height: 128px;font-size: 22px" class="el-icon-delete"></span>
                            </div>
                            <div class="editMask" @click="editCutItem(index)">
                                <span style="line-height: 128px;font-size: 22px" class="el-icon-edit"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <el-button type="primary" @click="onUploadImageChanged(0)">选择图片</el-button>
            <p>打开编辑图片示例代码</p>
            <pre>
var url = "/Upload/UploadCropImage/cropImage";
//传入图片地址
if(img_url != 0){
    // url 指定图片，可以为空 width/height分别指定需要裁剪的宽高比
    url = url + '?url=' + encodeURIComponent(img_url) + '&width=100&height=100';
}
//直接打开新页面
layer.open({
    type: 2,
    title: '图片裁剪',
    content: url,
    area: ['80%', '75%']
})
            </pre>
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

        .Mask{
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
        .Mask:hover {
            opacity: 1;
        }
        .deleteMask{
            position: absolute;
            top: 0;
            left: 0;
            width: 64px;
            height: 128px;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 40px;
        }
        .editMask{
            position: absolute;
            top: 0;
            right: 0;
            width: 64px;
            height: 128px;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 40px;
        }
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    uploadedFileCutList:[]  // 上传图片列表
                },
                methods: {
                    //删除
                    deleteCutItem: function (index) {
                        this.uploadedFileCutList.splice(index, 1)
                    },
                    //编辑
                    editCutItem:function(index){
                        this.onUploadImageChanged(this.uploadedFileCutList[index].url)
                    },
                    //图片裁剪框
                    onUploadImageChanged(img_url){
                        var url = "{:api_url('/demo/admin.image/cropImage')}"
                        //传入图片地址
                        if(img_url != 0){
                            url = url + '?url=' + encodeURIComponent(img_url) + '&width=100&height=100';
                        }
                        //直接打开新页面
                        layer.open({
                            type: 2,
                            title: '图片裁剪',
                            content: url,
                            area: ['80%', '75%']
                        })
                    },
                    //接收截图后的图片回调
                    onCropedFile(event){
                        this.uploadedFileCutList.push({
                            name: event.detail.savename, // 名称
                            url: event.detail.url, // 图片地址
                        })
                    },
                },
                mounted: function () {
                    window.addEventListener('ZTBCMS_IMAGE_CROP_FILE', this.onCropedFile.bind(this));
                },
            })
        })
    </script>
</block>
