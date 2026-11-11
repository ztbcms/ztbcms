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
        <el-button type="primary" @click="onUploadImageChanged('')">选择图片</el-button>
        <p>打开编辑图片示例代码</p>
        <pre>
var url = "{:api_url('/demo/admin.image/cropImage')}";
// 传入图片地址（可为空），width/height 分别指定裁剪框的宽高比
if (img_url) {
    url = url + '?url=' + encodeURIComponent(img_url) + '&width=100&height=100';
}
// 弹窗打开裁剪面板
layer.open({
    type: 2,
    title: '图片裁剪',
    content: url,
    area: ['80%', '75%']
})
// 监听裁剪结果
window.addEventListener('ZTBCMS_IMAGE_CROP_FILE', function (event) {
    console.log(event.detail.name, event.detail.url)
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

    .Mask {
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

    .deleteMask, .editMask {
        position: absolute;
        top: 0;
        width: 64px;
        height: 128px;
        text-align: center;
        background-color: rgba(0, 0, 0, 0.6);
        color: #fff;
        font-size: 40px;
    }

    .deleteMask {
        left: 0;
    }

    .editMask {
        right: 0;
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                uploadedFileCutList: []  // 裁剪后的图片列表
            },
            methods: {
                // 删除
                deleteCutItem: function (index) {
                    this.uploadedFileCutList.splice(index, 1)
                },
                // 编辑
                editCutItem: function (index) {
                    this.onUploadImageChanged(this.uploadedFileCutList[index].url)
                },
                // 打开图片裁剪面板
                onUploadImageChanged: function (img_url) {
                    var url = "{:api_url('/demo/admin.image/cropImage')}"
                    if (img_url) {
                        url = url + '?url=' + encodeURIComponent(img_url) + '&width=100&height=100';
                    }
                    layer.open({
                        type: 2,
                        title: '图片裁剪',
                        content: url,
                        area: ['80%', '75%']
                    })
                },
                // 接收裁剪后的图片回调
                onCropedFile: function (event) {
                    this.uploadedFileCutList.push({
                        name: event.detail.name,
                        url: event.detail.url,
                    })
                },
            },
            mounted: function () {
                window.addEventListener('ZTBCMS_IMAGE_CROP_FILE', this.onCropedFile.bind(this));
            },
        })
    })
</script>
