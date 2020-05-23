<extend name="../../Admin/View/Common/element_layout"/>

<block name="header">
    <link rel="stylesheet" href="{$config_siteurl}statics/admin/cropper/cropper.css">
    <script src="{$config_siteurl}statics/admin/cropper/cropper.js"></script>
    <style>
        .selectImage{
            border: none;
            padding: 11px 10px;
            font-size: 12px;
            background: #67C23A;
            color: #fff;
            border-radius: 5px;
        }
        .preview{
            width: 263px;
            height: 148px;
            text-align: center;
            overflow: hidden;
        }
        .save_img_url{
            max-width: 200px;
        }
        .img-container{
            width: 100%;
            height: 500px;
            margin: 10px;
        }
    </style>
</block>

<block name="content">
    <!-- Content -->
    <div class="container" id="app" v-cloak>
        <el-row>
            <el-col :span="16">
                <div class="img-container">
                    <img src="" alt="Picture" ref="image" >
                </div>
            </el-col>
            <el-col :span="6" style="margin: 10px;">
                <div>图片预览</div>
                <div class="preview" style="margin-bottom: 10px;"></div>
                <div>
                    <img :src="save_img_url" alt="" class="save_img_url" style="display:none;">
                </div>
                <div>
                    <div>裁剪框宽高比：</div>
                    <el-button-group>
                        <el-button type="primary" size="medium" @click="setAspectRatio(16 / 9)">
                          16:9
                        </el-button>

                        <el-button type="primary" size="medium" @click="setAspectRatio(4 / 3)">
                          4:3
                        </el-button>

                        <el-button type="primary" size="medium" @click="setAspectRatio( 2 / 3)">
                          2:3
                        </el-button>

                        <el-button type="primary" size="medium" @click="setAspectRatio(1 / 1)">
                          1:1
                        </el-button>

                        <el-button type="primary" size="medium" @click="setAspectRatio(NaN)">
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
                    <el-button type="danger"  size="medium" @click="Reset()">
                        重置
                    </el-button>
                </el-button-group>
                <label class="selectImage" for="inputImage">
                    <input class="sr-only" id="inputImage" name="file" type="file" accept="image/*" style="display:none;"
                           v-on:change="changeImg"
                           >
                    <span>
                        <i class="el-icon-upload2"></i>
                        <span>选择图片</span>
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
                el:"#app",
                data:{
                    img_url: "",
                    img_index:"",
                    save_img_url:""
                },
                methods:{
                    //初始化
                    init(){
                        this.myCropper = new Cropper(this.$refs.image, {
                            viewMode: 1,            //限制裁剪框不能超出图片的范围
                            dragMode: 'crop',       // 拖拽图片模式 : 形成新的裁剪框
                            initialAspectRatio: NaN,  // 裁剪框宽高比的初始值
                            preview: '.preview',    //预览
                            background: false,      //是否在容器内显示网格状的背景 默认true
                            autoCropArea: 0.6,      //设置裁剪区域占图片的大小 值为 0-1 默认 0.8 表示 80%的区域
                            zoomOnWheel: false,     //是否可以通过鼠标滚轮缩放图片 默认true
                        })
                    },
                    //放大 缩小
                    Zoom(option){
                        this.myCropper.zoom(option)
                    },
                    //旋转
                    Rotate(option){
                        this.myCropper.rotate(option)
                    },
                    //重置
                    Reset(){
                      this.myCropper.reset();
                    },
                    //保存
                    sureSava(){
                        this.save_img_url = this.myCropper.getCroppedCanvas({
                            imageSmoothingQuality: 'high'
                        }).toDataURL('image/jpeg')
                        //返回裁剪后的图片
                        var event = document.createEvent('CustomEvent');
                        event.initCustomEvent('ZTBCMS_IMAGE_CROP_FILE', true, true, {
                            img_base64: this.save_img_url,
                            img_index:  this.img_index
                        });
                        window.parent.dispatchEvent(event) //触发
                        this.closePanel();
                    },
                    //关闭窗口
                    closePanel: function(){
                        if(parent.window.layer){
                            parent.window.layer.closeAll();
                        }else{
                            window.close();
                        }
                    },
                    //设置裁剪的宽高比
                    setAspectRatio(option){
                        this.myCropper.setAspectRatio(option)
                    },
                    //更换图片
                    changeImg(e){
                        var newsrc= this.getObjectURL(e.target.files[0]);
                        this.myCropper.replace(newsrc,false)
                    },
                    getObjectURL(file) {
                        var url = null ;
                        if (window.createObjectURL!=undefined) { // basic
                            url = window.createObjectURL(file) ;
                        } else if (window.URL!=undefined) { // mozilla(firefox)
                            url = window.URL.createObjectURL(file) ;
                        } else if (window.webkitURL!=undefined) { // webkit or chrome
                            url = window.webkitURL.createObjectURL(file) ;
                        }
                        return url ;
                    }
                },
                mounted: function () {
                    this.init(); // 初始化
                    // 若有传入则更换url，否则为空
                    this.img_url = this.getUrlQuery('url') || "";
                    // 若是编辑模式，则将返回图片index
                    this.img_index = this.getUrlQuery('img_index') || "";
                    if(this.img_url != ""){
                        this.myCropper.replace(this.img_url)
                    }
                }
            })
        })
    </script>
    <!-- Scripts -->
</block>
