<?php if (!defined('CMS_VERSION'))
    exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap hidden" id="body">
    <Admintemplate file="Common/Nav"/>
    <div class="h_a">{{ operator.name }} - 更新模板</div>
    <form id="form" class="J_ajaxForm">
        <div class="table_full">
            <table width="100%" class="table_form contentWrap">
                <tr v-for="field in fields">
                    <th><strong>{{ field.field }}</strong><br/>{{ field.comment }}</th>
                    <td><input :name="field.field" :value="modules[field.field]" type="text"></td>
                </tr>
            </table>
            <div style="margin-top:1rem;">
                <input type="hidden" name="id" :value="modules.id">
                <input name="operator" type="hidden" :value="operator.tablename"/>
                <button @click="edit" class="btn btn_submit mr10 J_ajax_submit_btn" type="button">修改</button>
            </div>
        </div>
    </form>

</div>

<script src="//cdn.bootcss.com/vue/2.1.5/vue.min.js"></script>
<script>
    $.get("{:U('Sms/Index/get_modules',array('operator'=>$_GET['operator'],'id' => $_GET['id']))}&XDEBUG_SESSION_START=13788", null, function (data) {
        if (data.status){
            new Vue({
                el:"#body",
                data:data.datas,
                methods:{
                    edit: function(e){
                        var data = $('#form').serialize();
                        var url = "{:U('Sms/Index/module_edit')}";
                        $.post(url,data,function(data){
                            if (data.status){
                                alert('模板更新成功！');
                            }else{
                                alert(data.error);
                                window.location.reload();
                            }
                        },'json');
                    }
                },
                mounted: function(){
                    var vm = this;
                    $(vm.$options.el).removeClass('hidden');
                }

            })
        }else{
            $('.table_full').text(data.error);
        }
    }, 'json');
</script>
</body>
</html>
