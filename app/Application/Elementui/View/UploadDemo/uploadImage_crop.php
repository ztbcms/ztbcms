<extend name="../../Admin/View/Common/element_layout"/>

<block name="header">
    <link rel="stylesheet" type="text/css" href="{$config_siteurl}statics/base64/css/normalize.css" />
    <link rel="stylesheet" type="text/css" href="{$config_siteurl}statics/base64/css/default.css">
    <link href="{$config_siteurl}statics/base64/css/bootstrap.min.css" rel="stylesheet">
    <link href="{$config_siteurl}statics/base64/dist/cropper.css" rel="stylesheet">
    <link href="{$config_siteurl}statics/base64/css/main.css" rel="stylesheet">
</block>

<block name="content">


    <div class="htmleaf-container">
        <!-- Content -->
        <div class="container">
            <div class="row">
                <div class="col-md-9">
                    <!-- <h3 class="page-header">Demo:</h3> -->
                    <div class="img-container">
                        <img src="{$config_siteurl}statics/base64/img/picture.jpg" alt="Picture">
                    </div>
                </div>
                <div class="col-md-3">
                    <!-- <h3 class="page-header">Preview:</h3> -->
                    <div class="docs-preview clearfix">
                        <div class="img-preview preview-lg"></div>
                        <div class="img-preview preview-md"></div>
                        <div class="img-preview preview-sm"></div>
                        <div class="img-preview preview-xs"></div>
                    </div>
                    <div id="imgCanvasRes" style="display: none;">

                    </div>

                </div>
            </div>
            <!--按钮组-->
            <div class="row">
                <div class="col-md-9 docs-buttons">
                    <!-- <h3 class="page-header">Toolbar:</h3> -->
                    <div class="btn-group">

                        <button class="btn btn-primary" data-method="rotate" data-option="-45" type="button"
                                title="Rotate Left">
                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, -45)">
                              <span class="icon icon-rotate-left"></span>
                            </span>
                        </button>
                        <button class="btn btn-primary" data-method="rotate" data-option="45" type="button"
                                title="Rotate Right">
                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;rotate&quot;, 45)">
                              <span class="icon icon-rotate-right"></span>
                            </span>
                        </button>
                    </div>

                    <div class="btn-group">
                        <button class="btn btn-primary" data-method="clear" type="button" title="Clear">
                            <span class="docs-tooltip" data-toggle="tooltip">
                              <span class="icon icon-remove"></span>
                                清除选区
                            </span>
                        </button>
                        <button class="btn btn-primary" data-method="setDragMode" data-option="crop" type="button"
                                title="Crop">
                            <span class="docs-tooltip" data-toggle="tooltip"
                                  title="$().cropper(&quot;setDragMode&quot;, &quot;crop&quot;)">
                              <span class="icon icon-crop"></span>
                                点击开始选区
                            </span>
                        </button>
                        <button class="btn btn-primary" data-method="reset" type="button" title="Reset">
                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;reset&quot;)">
                              <span class="icon icon-refresh"></span>
                                重置
                            </span>
                        </button>
                    </div>

                    <div class="btn-group btn-group-crop">
                        <button class="btn btn-primary" data-method="getCroppedCanvas" type="button">
                            <span class="docs-tooltip" data-toggle="tooltip" title="$().cropper(&quot;getCroppedCanvas&quot;)"
                                  id="getImageCanvas">
                              预览裁剪后
                            </span>
                        </button>
                    </div>

                    <label class="btn btn-success btn-upload" for="inputImage">
                        <input class="sr-only" id="inputImage" name="file" type="file" accept="image/*">
                        <span class="docs-tooltip" data-toggle="tooltip" title="选择图片">
                            <span class="icon icon-upload"></span>
                            <span>选择图片</span>
                        </span>
                    </label>

                    <button class="btn btn-primary" type="button">
                      <span class="docs-tooltip" id="SaveImage">
                          保存
                      </span>
                    </button>

                    <!-- Show the cropped image in modal -->
                    <div class="modal fade docs-cropped" id="getCroppedCanvasModal" aria-hidden="true"
                         aria-labelledby="getCroppedCanvasTitle" role="dialog" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button class="close" data-dismiss="modal" type="button" aria-hidden="true">
                                        &times;
                                    </button>
                                    <h4 class="modal-title" id="getCroppedCanvasTitle">图片预览</h4>
                                </div>
                                <div class="modal-body"></div>
                                <!-- <div class="modal-footer">
                                  <button class="btn btn-primary" data-dismiss="modal" type="button">Close</button>
                                </div> -->
                            </div>
                        </div>
                    </div><!-- /.modal -->


                </div><!-- /.docs-buttons -->

                <!--鼠标左键可以重新选区-->
                <div class="col-md-3 docs-toggles">
                    <!-- <h3 class="page-header">Toggles:</h3> -->
                    <div class="btn-group btn-group-justified" data-toggle="buttons">
                        <label class="btn btn-primary active" data-method="setAspectRatio"
                               data-option="1.7777777777777777" title="Set Aspect Ratio">
                            <input class="sr-only" id="aspestRatio1" name="aspestRatio" value="1.7777777777777777"
                                   type="radio">
                            <span class="docs-tooltip" data-toggle="tooltip"
                                  title="$().cropper(&quot;setAspectRatio&quot;, 16 / 9)">
                              16:9
                            </span>
                        </label>
                        </label>
                        <label class="btn btn-primary" data-method="setAspectRatio" data-option="NaN"
                               title="Set Aspect Ratio">
                            <input class="sr-only" id="aspestRatio5" name="aspestRatio" value="NaN" type="radio">
                            <span class="docs-tooltip" data-toggle="tooltip"
                                  title="$().cropper(&quot;setAspectRatio&quot;, NaN)">
                              自由裁剪
                            </span>
                        </label>
                    </div>

                </div><!-- /.docs-toggles -->
            </div>
        </div>
    </div>

</block>
<block name="footer">
    <script src="{$config_siteurl}statics/base64/js/jquery.min.js"></script>
    <script src="{$config_siteurl}statics/base64/js/bootstrap.min.js"></script>
    <script src="{$config_siteurl}statics/base64/dist/cropper.js"></script>
    <script src="{$config_siteurl}statics/base64/js/main.js"></script>
    <!--触发getImageCanvas是后台保存图片  inputImage是更换图片按钮-->
    <!--返回的是base64格式图片-->
    <script>
        var backUrl = "";

        //将base64转换为file
        function dataURLtoFile(dataurl, filename) {
            var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
            while(n--){
                u8arr[n] = bstr.charCodeAt(n);
            }
            return new File([u8arr], filename, {type:mime});
        }

        // base64 转 blob
        function dataURItoBlob(base64Data) {
            //console.log(base64Data);//data:image/png;base64,
            var byteString;
            if(base64Data.split(',')[0].indexOf('base64') >= 0)
                byteString = atob(base64Data.split(',')[1]);//base64 解码
            else{
                byteString = unescape(base64Data.split(',')[1]);
            }
            var mimeString = base64Data.split(',')[0].split(':')[1].split(';')[0];//mime类型 -- image/png

            // var arrayBuffer = new ArrayBuffer(byteString.length); //创建缓冲数组
            // var ia = new Uint8Array(arrayBuffer);//创建视图
            var ia = new Uint8Array(byteString.length);//创建视图
            for(var i = 0; i < byteString.length; i++) {
                ia[i] = byteString.charCodeAt(i);
            }
            var blob = new Blob([ia], {
                type: mimeString
            });
            return blob;
        }

        // 保存图片
        $("#SaveImage").on("click",function () {
            var $image = $('.img-container > img');
            var result = $image.cropper('getCroppedCanvas', "");
            $('#imgCanvasRes').html(result);
            var obj = $('#imgCanvasRes').find('canvas')[0]
            var saveImage =  obj.toDataURL(); // 得到base64数据
            var b64 = saveImage.substring(22);

            var imgFile = dataURItoBlob(saveImage,'upload_img');

            // 构建Form
            var form = new FormData();
            let fileOfBlob = new File([imgFile], new Date()+'.jpg'); // 重命名
            form.append("file", fileOfBlob);
            // 上传后台
            $.ajax({
                url:"{:U('Upload/UploadPublicApi/uploadImage')}",
                data: form,
                dataType:"json",
                type:"post",
                processData:false,
                contentType: false,
                success(res){
                    if(res.status == true){
                        layer.msg('保存成功，图片地址为'+res.data.url, {time: 3000})
                        backUrl =  res.data.url;
                    }else{
                        layer.msg('保存失败', {time: 1000})
                    }
                }
            })

        });

        var callbackdata = function () {
            var data = {
                name : new Date()+'.jpg',
                url: backUrl
            };
            return data;
        }
    </script>
</block>