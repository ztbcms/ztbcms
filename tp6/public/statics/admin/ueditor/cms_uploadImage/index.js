/**
 * CMS 上传图片组件
 */
UE.registerUI('cms_uploadImage', function (editor, uiName) {
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
                    '<img src="' + files[i]['url'] + '">'
                );
            }
        }
    }

    var btn = new UE.ui.Button({
        name: 'cms-uploadImage',
        title: '内置图片上传',
        onclick: function () {
            window.addEventListener('ZTBCMS_UPLOAD_FILE', onUploadedFile);
            layer.open({
                type: 2,
                title: '上传图片',
                content: "/Upload/UploadCenter/imageUploadPanel",//max_upload
                area: ['80%', '70%'],
                end: function () {
                    // 销毁监听
                    window.removeEventListener('ZTBCMS_UPLOAD_FILE', onUploadedFile);
                }
            })
        }
    });

    return btn;
});