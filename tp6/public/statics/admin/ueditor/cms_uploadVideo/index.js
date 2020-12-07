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
            window.addEventListener('ZTBCMS_UPLOAD_FILE', onUploadedFile);
            layer.open({
                type: 2,
                title: '上传视频',
                content: "/Upload/UploadCenter/fileUploadPanel?max_upload=1&accept=video/mp4",//max_upload
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