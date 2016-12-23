<?php if (!defined('CMS_VERSION'))
    exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap hidden" id="body">
    <Admintemplate file="Common/Nav"/>

    <div class="h_a">{{ operator.name }} - 已有模板</div>

    <div class="table_full">
        <table width="100%" class="table_form contentWrap">
            <tr>
                <th><strong>id</strong></th>
                <th v-for="field in fields">
                    <strong>{{ field.field }}</strong>
                </th>
                <th>
                    操作
                </th>
            </tr>
            <tr v-for="module in modules">
                <th>{{ module.id }}</th>
                <td v-for="field in fields">
                    {{ module[field.field] }}
                </td>
                <td>
                    <a @click="edit" :data-operator="operator.tablename" :data-id="module.id" href="javascript:">修改</a> |
                    <a @click="del" :data-operator="operator.tablename" :data-id="module.id" href="javascript:">删除</a>
                </td>
            </tr>
        </table>
    </div>

    <div class="h_a">{{ operator.name }} - 添加模板</div>
    <form id="form" class="J_ajaxForm">
        <div class="table_full">
            <table width="100%" class="table_form contentWrap">
                <tr v-for="field in fields">
                    <th><strong>{{ field.field }}</strong><br/>{{ field.comment }}</th>
                    <td><input type="text" :name="field.field" style="width:400px;" class="input" value=""></td>
                </tr>
            </table>
            <div style="margin-top:1rem;">
                <input name="operator" type="hidden" :value="operator.tablename"/>
                <button @click="add" class="btn btn_submit mr10 J_ajax_submit_btn" type="button">添加</button>
            </div>
        </div>
    </form>

</div>

<script src="//cdn.bootcss.com/vue/2.1.5/vue.min.js"></script>
<script>
    $.get("{:U('Sms/Index/get_modules',array('operator'=>$_GET['operator']))}", null, function (data) {
        if (data.status){
            new Vue({
                el:"#body",
                data:data.datas,
                methods:{
                    add: function (e) {
                        var data = $('#form').serialize();
                        var url = "{:U('Sms/Index/module_add')}";
                        $.post(url,data,function(data){
                            if (data.status){
                                alert('模板添加成功！');
                                window.location.reload();
                            }else{
                                alert(data.error);
                            }
                        },'json');
                    },
                    edit: function(e){
                        var data = $(e.toElement).data();
                        var url = "{:U('Sms/Index/module_edit')}";
                        window.location.href = url + "&operator=" + data.operator + "&id=" + data.id;
                    },
                    del :function(e){
                        var data = $(e.toElement).data();
                        var url = "{:U('Sms/Index/module_del')}";
                        $.post(url,data,function(data){
                            if (data.status){
                                alert('模板删除成功！');
                                window.location.reload();
                            }else{
                                alert(data.error);
                            }
                        },'json');
                    }
                },
                mounted: function(){
                    var vm = this;
                    $(vm.$options.el).removeClass('hidden');
                }

            });
        }else{
            $('.table_full').text(data.error);
        }
    }, 'json');
</script>
</body>
</html>
