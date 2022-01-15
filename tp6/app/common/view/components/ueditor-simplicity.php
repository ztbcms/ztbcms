<script type="text/javascript" src="/statics/admin/ueditor/ueditor.config.simplicity.js"></script>
<script type="text/javascript" src="/statics/admin/ueditor/ueditor.all.js"></script>
<script type="text/javascript" src="/statics/admin/ueditor/lang/zh-cn/zh-cn.js"></script>
<script>
    var _UEDITOR_CONFIG = {
        rowspacingtop: ['0', '5', '10', '15', '20', '25'],
        rowspacingbottom: ['0', '5', '10', '15', '20', '25'],
        lineheight: ['0', '1', '1.5', '1.75', '2', '3', '4', '5']
    }
</script>
<!--  CMS上传图片组件,不需要时，可以直接注释  -->
<link rel="stylesheet" href="/statics/admin/ueditor/cms_uploadImage/index.css">
<!--  CMS上传图片组件  -->

<!--  CMS上传视频组件,不需要时，可以直接注释 默认不显示  -->
<!--<link rel="stylesheet" href="/statics/admin/ueditor/cms_uploadVideo/index.css">-->
<!--<script src="/statics/admin/ueditor/cms_uploadVideo/index.js"></script>-->
<!--  CMS上传图片组件  -->

<script type="text/x-template" id="ueditor-simplicity">
    <div>
        <div>
            <div style="line-height: 0;">
                <textarea id="editor_content" :style="`height: `+height+`px;width: `+width+`px;`"></textarea>
            </div>
        </div>
        <div>
            <select-ue-image :show="show_image" :is_private="0" @confirm="confirmImage"
                             @close="show_image=false"></select-ue-image>
        </div>
    </div>
</script>

{ztbcms:include file="common/@/components/select-ue-image"}
<script>
    $(function () {
        var ueditorInstance = UE.getEditor('editor_content');
        Vue.component('ueditor-simplicity', {
            template: '#ueditor-simplicity',
            props: {
                height: 500,
                width: 390,
            },
            data() {
                return {
                    show_image: false,
                }
            },
            watch: {},
            computed: {},
            methods: {
                confirmImage(files) {
                    console.log('confirmImage', files)
                    if (files) {
                        for (var i = 0; i < files.length; i++) {
                            ueditorInstance.focus();
                            ueditorInstance.execCommand(
                                "inserthtml",
                                '<img src="' + files[i]['fileurl'] + '" style="width: 100%;font-size:0;line-height:0;vertical-align:top;outline-width:0px;">'
                            );
                        }
                    }
                }
            },
            mounted() {
                UE.registerUI('cms_uploadImage', (editor, uiName) => {
                    var btn = new UE.ui.Button({
                        name: 'cms-uploadImage',
                        title: '内置图片上传',
                        onclick: () => {
                            this.show_image = true
                        }
                    });
                    return btn;
                });
            }
        });
    })
</script>