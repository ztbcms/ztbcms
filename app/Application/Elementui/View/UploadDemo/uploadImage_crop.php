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
                    uploadedFileCutList:[]
                },
                watch: {},
                filters: {},
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
                        var url = "Upload/UploadCropImage/cropImage";
                        //传入图片地址
                        if(img_url != 0){
                            url = url + '?url=' + img_url;
                        }
                        //直接打开新页面
                        layer.open({
                            type: 2,
                            title: '图片裁剪',
                            content: url,
                            area: ['75%', '80%']
                        })
                    },
                    //接收截图后文件回调
                    onUploadedFile(event){
                        var img_base64 = event.detail.img_base64
                        if (img_base64) {
                            this.doUpload(img_base64)
                        }
                    },
                    //上传图片
                    doUpload(img_base64){
                        var that = this;
                        var img_blob = this.dataURItoBlob(img_base64);
                        var form = new FormData();
                        let fileOfBlob = new File([img_blob], new Date()+'.jpg'); // 重命名
                        form.append("file", fileOfBlob);
                        $.ajax({
                            url:"{:U('Upload/UploadAdminApi/uploadImage')}",
                            data: form,
                            dataType:"json",
                            type:"post",
                            processData:false,
                            contentType: false,
                            success(res){
                                if(res.status == true){
                                    var backUrl =  res.data.url;  //返回保存图片地址
                                    that.uploadedFileCutList.push({
                                        name: res.data.savename,
                                        url: backUrl,
                                    })
                                }else{
                                    layer.msg('保存失败', {time: 1000})
                                }
                            }
                        })
                    },
                    // base64 转 blob
                    dataURItoBlob(base64Data) {
                        var byteString;
                        if(base64Data.split(',')[0].indexOf('base64') >= 0)
                            byteString = atob(base64Data.split(',')[1]);//base64 解码
                        else{
                            byteString = unescape(base64Data.split(',')[1]);
                        }
                        var mimeString = base64Data.split(',')[0].split(':')[1].split(';')[0];//mime类型 -- image/png
                        var ia = new Uint8Array(byteString.length);//创建视图
                        for(var i = 0; i < byteString.length; i++) {
                            ia[i] = byteString.charCodeAt(i);
                        }
                        var blob = new Blob([ia], {
                            type: mimeString
                        });
                        return blob;
                    }
                },
                mounted: function () {
                    window.addEventListener('ZTBCMS_IMAGE_CROP_FILE', this.onUploadedFile.bind(this));
                },

            })
        })
    </script>
</block>
