<?php if (!defined('CMS_VERSION'))
    exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap hidden" id="body">
    <Admintemplate file="Common/Nav"/>

    <div class="h_a">{{ operator.name }} - 添加字段</div>
    <form class="J_ajaxForm" id="add_field">
        <div class="table_full">
            <table width="100%" class="table_form contentWrap">
                <tr>
                    <th width="25%"><font color="red">*</font> <strong>字段名</strong><br/>
                        只能由英文字母组成
                    </th>
                    <td><input type="text" name="name" style="width:400px;" class="input"></td>
                </tr>

                <tr>
                    <th><strong>字段默认值</strong></th>
                    <td><input type="text" name="default" style="width:400px;" class="input"></td>
                </tr>

                <tr>
                    <th><strong>字段描述</strong></th>
                    <td><input type="text" name="comment" style="width:400px;" class="input"></td>
                </tr>

            </table>
        </div>

        <div class="btn_wrap_pd">
            <a @click="add" class="btn btn_submit mr10 J_ajax_submit_btn" href="javascript:">添加</a>
            <input name="operator" type="hidden" :value="operator.tablename"/>
        </div>
    </form>

    <div class="h_a">{{ operator.name }} - 模型管理</div>
    <form class="J_ajaxForm">
        <div class="table_full">
            <table width="100%" class="table_form contentWrap">
                <tr>
                    <th width="30">字段名</th>
                    <th width="50">描述</th>
                    <th width="20">操作</th>
                </tr>
                <tr v-for="field in fields" :field="field" :operator="operator">
                    <td><strong>{{ field.field }}</strong></td>
                    <td>{{ field.comment }}</td>
                    <td>
                        <a @click="del"
                           :data-operator="operator.tablename" :data-field="field.field"
                           href="javascript:">删除</a>
                    </td>
                </tr>
            </table>
        </div>
    </form>

</div>

<script src="//cdn.bootcss.com/vue/2.1.5/vue.min.js"></script>
<script>
    $.get(
        "{:U('Sms/Index/get_fields', array('operator' => $_GET['operator']))}&XDEBUG_SESSION_START=12932",
        null,
        function (data) {
            if (data.status) {
                new Vue({
                    el: '#body',
                    data: data.datas,
                    mounted: function(){
                        var vm = this;
                        $(vm.$options.el).removeClass('hidden');
                    },
                    methods: {
                        del: function (e) {
                            var data = {
                                operator: $(e.toElement).data('operator'),
                                field: $(e.toElement).data('field')
                            };

                            $.post("{:U('Index/field_del')}", data, function (data) {
                                if (data.status) {
                                    alert('删除成功');
                                    window.location.reload();
                                } else {
                                    alert('删除失败');
                                }
                            }, 'json');
                        },

                        add: function (e) {
                            var data = $('#add_field').serialize();
                            $.post("{:U('Sms/Index/field_add')}", data, function (data) {
                                if (data.status) {
                                    alert('添加成功');
                                    window.location.reload();
                                } else {
                                    alert('添加失败');
                                }
                            }, 'json');
                        }
                    }
                });
            } else {

            }
        },
        'json')
</script>
</body>
</html>
