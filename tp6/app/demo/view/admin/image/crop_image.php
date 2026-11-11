<link rel="stylesheet" href="/statics/admin/cropper/cropper.css">
<script src="/statics/admin/cropper/cropper.js"></script>
<style>
    .selectImage {
        border: none;
        padding: 11px 10px;
        font-size: 12px;
        background: #67C23A;
        color: #fff;
        border-radius: 5px;
    }

    .preview {
        width: 263px;
        height: 148px;
        text-align: center;
        overflow: hidden;
    }

    .save_img_url {
        max-width: 200px;
    }

    .img-container {
        width: 98%;
        height: 500px;
    }
</style>

<!-- Content -->
<div class="container" id="app" v-cloak>
    <el-row>
        <el-col :span="16" style="margin:10px;">
            <div class="img-container">
                <img src="/statics/admin/cropper/white.png" alt="" ref="image">
            </div>
        </el-col>
        <el-col :span="6">
            <div>图片预览</div>
            <div class="preview" style="margin: 10px 0;"></div>
            <div>
                <img :src="save_img_url" alt="" class="save_img_url" style="display:none;">
            </div>
            <div>
                <div>裁剪框宽高比：</div>
                <div class="">
                    <el-input placeholder="" v-model="input_width" style="margin: 10px 0;width: 263px;"
                              @input="listenInputWidth">
                        <template slot="prepend">宽</template>
                    </el-input>
                    <br>
                    <el-input placeholder="" v-model="input_height" style="margin-bottom: 10px;width: 263px;"
                              @input="listenInputHeight">
                        <template slot="prepend">高</template>
                    </el-input>
                </div>
                <el-button-group>
                    <el-button type="primary" size="medium" @click="setAspectRatio(16,9)">
                        16:9
                    </el-button>

                    <el-button type="primary" size="medium" @click="setAspectRatio(4,3)">
                        4:3
                    </el-button>

                    <el-button type="primary" size="medium" @click="setAspectRatio(1,1)">
                        1:1
                    </el-button>

                    <el-button type="primary" size="medium" @click="setAspectRatio(NaN,NaN)">
                        自由
                    </el-button>
                </el-button-group>
            </div>
        </el-col>
    </el-row>
    <el-col :span="15" style="text-align: center;margin-top: 20px;">
        <el-button-group>
            <el-button type="primary" size="medium" @click="Zoom(0.1)">
                放大
            </el-button>
            <el-button type="primary" size="medium" @click="Zoom(-0.1)">
                缩小
            </el-button>
        </el-button-group>

        <el-button-group>
            <el-button type="primary" size="medium" @click="Rotate(45)">
                向右旋转45°
            </el-button>
        </el-button-group>

        <el-button-group>
            <el-button type="danger" size="medium" @click="Reset()">
                重置
            </el-button>
        </el-button-group>
        <label class="selectImage" for="inputImage">
                <span @click="gotoUploadImage">
                    <i class="el-icon-upload2" style="font-size: 14px;"></i>
                    <span style="font-size: 14px;">选择图片</span>
                </span>
        </label>
        <el-button-group>
            <el-button type="success" size="medium" @click="sureSava">
                保存
            </el-button>
        </el-button-group>
    </el-col>
</div>

<script>
    $(document).ready(function () {
        new Vue({
            el: "#app",
            data: {
                img_url: "",
                save_img_url: "",
                input_width: "",
                input_height: "",
            },
            methods: {
                //初始化
                init() {
                    this.myCropper = new Cropper(this.$refs.image, {
                        viewMode: 1,                //限制裁剪框不能超出图片的范围
                        dragMode: 'crop',           //拖拽图片模式 : 形成新的裁剪框
                        initialAspectRatio: NaN,    //裁剪框宽高比的初始值
                        preview: '.preview',        //预览
                        background: true,           //是否在容器内显示网格状的背景 默认true
                        autoCropArea: 0.6,          //设置裁剪区域占图片的大小 值为 0-1 默认 0.8 表示 80%的区域
                        zoomOnWheel: false,         //是否可以通过鼠标滚轮缩放图片 默认true
                    })
                },
                //宽比例
                listenInputWidth() {
                    this.setAspectRatio(this.input_width, this.input_height)
                },
                //高比例
                listenInputHeight() {
                    this.setAspectRatio(this.input_width, this.input_height)
                },
                //放大 缩小
                Zoom(option) {
                    this.myCropper.zoom(option)
                },
                //旋转
                Rotate(option) {
                    this.myCropper.rotate(option)
                },
                //重置
                Reset() {
                    this.myCropper.reset();
                },
                //保存
                sureSava() {
                    var that = this;
                    var canvas = this.myCropper.getCroppedCanvas({
                        imageSmoothingQuality: 'high'
                    });
                    if (!canvas) {
                        layer.msg('请先选择图片')
                        return
                    }
                    try {
                        canvas.toBlob(function (blob) {
                            that.doUpload(blob)
                        }, 'image/png');
                    } catch (e) {
                        // 跨域图片且未配置 CORS 时画布会被污染，无法导出
                        layer.msg('无法导出跨域图片，请确认图片域名已配置 CORS')
                    }
                },
                //关闭窗口
                closePanel: function () {
                    if (parent.window.layer) {
                        parent.window.layer.close(parent.window.layer.getFrameIndex(window.name))
                    } else {
                        window.close()
                    }
                },
                //设置裁剪的宽高比
                setAspectRatio(width, height) {
                    var option = width / height
                    this.input_width = width
                    this.input_height = height
                    this.myCropper.setAspectRatio(option)
                },
                //获取上传凭证（后台管理员临时 JWT）
                getUploadToken() {
                    return new Promise(function (resolve, reject) {
                        $.ajax({
                            url: "{:api_url('common/upload.upload/getUploadToken')}",
                            type: 'GET',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status && res.data && res.data.token) {
                                    resolve(res.data.token)
                                } else {
                                    reject(new Error(res.msg || '获取上传凭证失败'))
                                }
                            },
                            error: function () {
                                reject(new Error('获取上传凭证请求失败'))
                            }
                        })
                    })
                },
                //上传图片
                doUpload(blob) {
                    var that = this;
                    var form = new FormData();
                    form.append("file", new File([blob], 'crop_' + Date.now() + '.png', {type: 'image/png'}));
                    this.getUploadToken().then(function (token) {
                        $.ajax({
                            url: "{:api_url('common/upload.api/upload')}",
                            data: form,
                            dataType: "json",
                            type: "post",
                            processData: false,
                            contentType: false,
                            headers: {
                                'Authorization': 'Bearer ' + token
                            },
                            success(res) {
                                if (res.status) {
                                    layer.msg('保存成功')
                                    that.save_img_url = res.data.fileurl;  //返回保存图片地址
                                    //返回裁剪后的图片
                                    window.parent.dispatchEvent(new CustomEvent('ZTBCMS_IMAGE_CROP_FILE', {
                                        detail: {
                                            name: res.data.filename,
                                            url: that.save_img_url,
                                        }
                                    }));
                                    that.closePanel()
                                } else {
                                    layer.msg(res.msg)
                                }
                            },
                            error: function () {
                                layer.msg('上传请求失败')
                            }
                        })
                    }).catch(function (err) {
                        layer.msg(err.message)
                    })
                },
                // 选择图片
                gotoUploadImage: function () {
                    layer.open({
                        type: 2,
                        title: '',
                        closeBtn: false,
                        content: "{:api_url('common/upload.panel/index')}?module=image",
                        area: ['670px', '550px'],
                    })
                },
                // 选择图片回调
                onUploadedFile: function (event) {
                    var files = event.detail.files
                    if (files) {
                        this.img_url = files[0].fileurl
                        this.myCropper.replace(this.img_url)
                    }
                },
            },
            mounted: function () {
                window.addEventListener('ZTBCMS_UPLOAD_IMAGE', this.onUploadedFile.bind(this))

                // 初始化
                this.init();
                // 传入指定图片url
                this.img_url = decodeURIComponent(this.getUrlQuery('url') || "")
                if (this.img_url != "") {
                    this.myCropper.replace(this.img_url)
                }
                // 设置宽高比
                var width = this.getUrlQuery('width') || "",
                    height = this.getUrlQuery('height') || ""
                this.setAspectRatio(width, height)
            }
        })
    })
</script>
<!-- Scripts -->
