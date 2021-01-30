/**
 * CMS 上传图片组件
 */
UE.registerUI('cms_uploadVideo', function (editor, uiName) {
    function onUploadedFile(event) {
        var that = this;
        var files = event.detail.files
        console.log('onUploadedFile')
        console.log(files)
        if (files) {
            for (var i = 0; i < files.length; i++) {
                editor.focus();
                editor.execCommand(
                    "inserthtml",
                    '<video class="edui-upload-video vjs-default-skin video-js" controls="" preload="none" width="420" height="280" src="' + files[i]['url'] + '" data-setup="{}">\
                    <source src="' + files[i]['url'] + '" type="video/mp4"/>\
                    </video>'
                );
            }
        }
    }

    var btn = new UE.ui.Button({
        name: 'cms-uploadVideo',
        title: '内置视频上传',
        onclick: function () {
            window.addEventListener('ZTBCMS_UPLOAD_VIDEO', onUploadedFile);
            layer.open({
                type: 2,
                title: '',
                content: "/common/upload.panel/videoUpload",
                closeBtn: false,
                area: ['720px', '550px'],
                end: function () {
                    // 销毁监听
                    window.removeEventListener('ZTBCMS_UPLOAD_VIDEO', onUploadedFile);
                }
            })
        }
    });

    return btn;
});