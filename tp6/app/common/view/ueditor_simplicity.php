<script type="text/javascript" charset="utf-8"
        src="/statics/admin/ueditor/ueditor.config.simplicity.js"></script>
<script type="text/javascript" charset="utf-8"
        src="/statics/admin/ueditor/ueditor.all.js"></script>
<script type="text/javascript" charset="utf-8"
        src="/statics/admin/ueditor/lang/zh-cn/zh-cn.js"></script>
<script>
    var _UEDITOR_CONFIG = {
        rowspacingtop: ['0', '5', '10', '15', '20', '25'],
        rowspacingbottom: ['0', '5', '10', '15', '20', '25'],
        lineheight: ['0', '1', '1.5', '1.75', '2', '3', '4', '5']
    }
</script>

<!--  CMS上传图片组件,不需要时，可以直接注释  -->
<link rel="stylesheet" href="/statics/admin/ueditor/cms_uploadImage/index.css">

<script>
    UE.registerUI('cms_uploadImage', function (editor, uiName) {
        function onUploadedFile(event) {
            var files = event.detail.files;
            console.log('onUploadedFile');
            console.log(files);
            if (files) {
                for (var i = 0; i < files.length; i++) {
                    editor.focus();
                    editor.execCommand(
                        "inserthtml",
                        '<img src="' + files[i]['fileurl'] + '" style="width: 100%;">'
                    );
                }
            }
        }

        var btn = new UE.ui.Button({
            name: 'cms-uploadImage',
            title: '内置图片上传',
            onclick: function () {
                window.addEventListener('ZTBCMS_UPLOAD_UE_IMAGE', onUploadedFile);
                layer.open({
                    type: 2,
                    title: '',
                    closeBtn: false,
                    content: '{:api_url("common/upload.panel/imageUEUpload")}',
                    area: ['670px', '550px'],
                    end: function () {
                        console.log(0)
                        // 销毁监听
                        window.removeEventListener('ZTBCMS_UPLOAD_UE_FILE', onUploadedFile);
                    }
                })
            }
        });

        return btn;
    });
</script>
<!--  CMS上传图片组件  -->

<!--  CMS上传视频组件,不需要时，可以直接注释  -->
<!--<link rel="stylesheet" href="/statics/admin/ueditor/cms_uploadVideo/index.css">-->
<!--<script src="/statics/admin/ueditor/cms_uploadVideo/index.js"></script>-->
<!--  CMS上传图片组件  -->