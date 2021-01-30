/**
 * CMS 上传图片组件
 */
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
                content: '/common/upload.panel/imageUEUpload',
                area: ['720px', '550px'],
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