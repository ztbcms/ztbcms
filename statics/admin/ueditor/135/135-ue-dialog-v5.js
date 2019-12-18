/**
 * Created by shunchen_yang on 16/10/25.
 */
UE.registerUI('135editor', function (editor, uiName) {
    var btn = new UE.ui.Button({
        name   : '135-connect',
        title  : '135编辑器',
        onclick: function () {
            var dialog = new UE.ui.Dialog({
                iframeUrl: '/statics/admin/ueditor/135-ue-dialog-v5.html',
                editor   : editor,
                name     : '135-connect',
                title    : "135编辑器",
                cssRules : "width: " + (window.innerWidth - 60) + "px;" + "height: " + (window.innerHeight - 60) + "px;",
            });
            dialog.render();
            dialog.open();
        }
    });
    return btn;
});