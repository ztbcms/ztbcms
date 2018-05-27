<Admintemplate file="Common/Head"/>

<script src="/statics/admin/clipboard/clipboard.min.js"></script>

<body class="J_scroll_fixed">
<div class="wrap J_check_wrap" id="app">
    <Admintemplate file="Common/Nav"/>
    <div class="h_a" >数据字典
        <a style="margin-left: 20px;" href="javascript:history.back()" class="btn btn-success">返回列表</a>
        <!-- Trigger -->
        <button id="btn_copy_text" class="btn btn-success" data-clipboard-target="#previewtext">复制内容</button>
    </div>

    <div class="table_full">
        <pre id="previewtext" style="width: 100%;height: auto">{{ previewText }}</pre>
    </div>
</div>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                exportInfos: []
            },
            mounted: function(){
                this.initClipboard();
                this.getData();
            },
            computed: {
                previewText: function (){
                    var result = '';
                    if(this.exportInfos && this.exportInfos.length > 0){
                        this.exportInfos.forEach(function(exportInfo){
                            result += '## ' + exportInfo.tablename + ' ' + exportInfo.table_name;
                            result += '\n\n';
                            result += '| 字段名 | 字段别名 | 类型 | 说明 \n' +
                                '|:--- |:--- |:--- |:--- | \n';
                            var fields = exportInfo.fields, tips = '';
                            for(var i=0; i < fields.length; i++ ){
                                tips = fields[i]['tips'] ?  fields[i]['tips'] : '/' ;
                                result += '| ' + fields[i]['field'] + ' | ' + fields[i]['name'] + ' | ' + fields[i]['type'] + ' | '+ tips + ' | \n'
                            }

                            result += '\n\n';
                        })
                    }
                    return result;
                }
            },
            methods: {
                initClipboard: function(){
                    var clipboard = new ClipboardJS('#btn_copy_text')
                    clipboard.on('success', function(e) {
                        layer.msg('已复制到剪切板')

                        e.clearSelection();
                    });
                },
                getData: function () {
                    var that = this;
                    var data = {}
                    if(that.getUrlQuery('modelid')){
                        data['modelid'] = that.getUrlQuery('modelid');
                    }
                    $.ajax({
                        url: '{:U("Content/FieldExport/getExportModelFieldsInfo")}',
                        type: 'post',
                        dataType: 'json',
                        data: data,
                        success: function (res) {
                            if(res.status){
                                that.exportInfos = res.data
                            }else{
                                layer.msg(res.msg)
                            }
                        }
                    });
                },
            }
        })
    });
</script>
</body>
</html>
