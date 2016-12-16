<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">

    <style>
        .condition_item, .field_item{
            padding: 6px;
        }

        input:read-only{
            background: gainsboro;
        }
    </style>

    <Admintemplate file="Common/Nav"/>

    <div class="h_a">编辑任务</div>
    <form class="J_ajaxForm"  action="{:U('Transport/Index/task_edit')}" method="post">
        <div class="table_full">
            <table width="100%">
                <col class="th" />
                <col width="400" />
                <col />
                <tr>
                    <th>任务标题</th>
                    <td><input type="text" class="input length_5 mr5" name="title" value="{$title}"></td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr>
                    <th>任务描述</th>
                    <td><input type="text" class="input length_5 mr5" name="description" value="{$description}"></td>
                    <td><div class="fun_tips"></div></td>
                </tr>
                <tr>
                    <th>任务类型</th>
                    <td>
                        <select  name="type" class="mr10">
                            <option value="1" <?php echo ($type == 1?'selected':'')?>>导入任务</option>
                            <option value="2" <?php echo ($type == 2?'selected':'')?>>导出任务</option>
                        </select>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>
                <tr>
                    <th>模型</th>
                    <td>
                        <?php $models = M('Model')->select();?>
                        <select  name="model" class="mr10">
                            <volist name="models" id="model">
                                <option value="{$model['tablename']}" <?php echo ($model == $model['tablename']?'selected':'')?>>{$model['name']}</option>
                            </volist>
                        </select>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr style="display: none;">
                    <th>ID</th>
                    <td>
                        <input type="text" value="{$id}" name="task_id">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>
                <tr>
                    <th>操作</th>
                    <td>
                        <button class="btn btn_submit J_ajax_submit_btn" type="submit">提交</button>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>
            </table>
        </div>
    </form>


    <div class="h_a">设置筛选条件</div>
    <form class="J_ajaxForm"  action="{:U('Transport/Index/task_update_condition')}" method="post" id="condition_form">
        <div class="table_full">
            <table width="100%">
                <col class="th" />
                <tr>
                    <th>新增筛选条件</th>
                    <td>
                        <label>字段: </label>
                        <input type="text" class="input length_3 mr2" name="new_filter" value="">

                        <label>条件:</label>
                        <select name="new_operator" class="select_1">
                            <option value="EQ"> = </option>
                            <option value="NEQ"> != </option>
                            <option value="GT"> > </option>
                            <option value="EGT"> >= </option>
                            <option value="LT"> < </option>
                            <option value="ELT"> <= </option>
                            <option value="LIKE"> LIKE </option>
                        </select>

                        <label>值: </label>
                        <input type="text" class="input length_3 mr2" name="new_value" value="">

                        <a class="btn btn-success" onclick="addCondition()"><i class="iconfont icon-add1"></i>新增</a>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr>
                    <th>当前筛选条件</th>
                    <template id="tpl_condition">
                        <div id="condition_{condition_id}" class="condition_item">
                            <label>字段: </label>
                            <input type="text" class="input length_3 mr2" name="condition_filter[]" value="{new_filter}">

                            <label>条件: </label>
                            <input type="text" class="input length_3 mr2" name="condition_operator[]" value="{new_operator}" readonly>

                            <label>值: </label>
                            <input type="text" class="input length_3 mr2" name="condition_value[]" value="{new_value}">
                            <a  onclick="delete_condition('{condition_id}')" class="btn btn-danger"><i class="iconfont icon-close"></i>删除</a>
                        </div>
                    </template>
                    <td>
                        <div id="conditions_container">
                            <volist name="task_conditions" id="condition">
                                <div id="condition_{$condition['id']}" class="condition_item">
                                    <label>字段: </label>
                                    <input type="text" class="input length_3 mr2" name="condition_filter[]" value="{$condition['filter']}">

                                    <label>条件: </label>
                                    <input type="text" class="input length_3 mr2" name="condition_operator[]" value="{$condition['operator']}" readonly>

                                    <label>值: </label>
                                    <input type="text" class="input length_3 mr2" name="condition_value[]" value="{$condition['value']}">
                                    <a  onclick="delete_condition('{$condition["id"]}')" class="btn btn-danger"><i class="iconfont icon-close"></i>删除</a>
                                </div>
                            </volist>
                        </div>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr style="display: none;">
                    <th>ID</th>
                    <td>
                        <input type="text" value="{$id}" name="task_id">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>


                <tr>
                    <th>操作</th>
                    <td>
                        <button class="btn btn_submit J_ajax_submit_btn" type="submit">提 交</button>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>
            </table>
        </div>
    </form>

    <div class="h_a">设置字段映射</div>
    <form class="J_ajaxForm"  action="{:U('Transport/Index/task_update_field')}" method="post" id="field_form">
        <div class="table_full">
            <table width="100%">
                <col class="th" />
                <col width="1000" />
                <tr>
                    <th>新增字段映射</th>
                    <td>
                        <label>内部字段名: </label>
                        <input type="text" class="input length_3 mr2" name="new_field_name" value="">

                        <label>外部名称: </label>
                        <input type="text" class="input length_3 mr2" name="new_export_name" value="">

                        <label>过滤处理器: </label>
                        <input type="text" class="input length_3 mr2" name="new_filter" value="">

                        <a class="btn btn-success" onclick="add_field()"><i class="iconfont icon-add1"></i>新增</a>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr>
                    <th>当前字段映射</th>
                    <template id="tpl_field">
                        <div id="condition_{field_id}" class="field_item">
                            <label>内部字段名: </label>
                            <input type="text" class="input length_3 mr2" name="field_field_name[]" value="{new_field_name}">

                            <label>外部名称: </label>
                            <input type="text" class="input length_3 mr2" name="field_export_name[]" value="{new_export_name}" >

                            <label>过滤处理器: </label>
                            <input type="text" class="input length_3 mr2" name="field_filter[]" value="{new_filter}">
                            <a  onclick="delete_field('{field_id}')" class="btn btn-danger"><i class="iconfont icon-close"></i>删除</a>
                        </div>
                    </template>
                    <td>
                        <div id="fields_container">
                            <volist name="task_fields" id="field">
                                <div id="field_{$field['id']}" class="field_item">
                                    <label>内部字段名: </label>
                                    <input type="text" class="input length_3 mr2" name="field_field_name[]" value="{$field['field_name']}">

                                    <label>外部名称: </label>
                                    <input type="text" class="input length_3 mr2" name="field_export_name[]" value="{$field['export_name']}" >

                                    <label>过滤处理器: </label>
                                    <input type="text" class="input length_3 mr2" name="field_filter[]" value="{$field['filter']}">
                                    <a  onclick="delete_field('{$field["id"]}')" class="btn btn-danger"><i class="iconfont icon-close"></i>删除</a>
                                </div>
                            </volist>
                        </div>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr style="display: none;">
                    <th>ID</th>
                    <td>
                        <input type="text" value="{$id}" name="task_id">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>


                <tr>
                    <th>操作</th>
                    <td>
                        <button class="btn btn_submit J_ajax_submit_btn" type="submit">提 交</button>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>
            </table>
        </div>
    </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
    (function($){
        //设置筛选条件

        //添加筛选条件
        window.addCondition = function(){
            var $new_filter = $('#condition_form input[name=new_filter]');
            var $new_operator = $('#condition_form select[name=new_operator]');
            var $new_value = $('#condition_form input[name=new_value]');

            var html = $('#tpl_condition').html();

            var id = Date.now();

            html = html.replace('{new_filter}', $new_filter.val()).replace('{new_operator}', $new_operator.val())
                .replace('{new_value}', $new_value.val()).replace('{condition_id}', id).replace('{condition_id}', id);

            $('#conditions_container').append(html);

        }

        window.delete_condition = function(condition_id){
            $('#condition_' + condition_id).remove();
        }
    })(jQuery);
</script>

<script>
    (function($){
        //设置字段映射

        //添加字段映射
        window.add_field = function(){
            var $new_field_name = $('#field_form input[name=new_field_name]');
            var $new_export_name = $('#field_form input[name=new_export_name]');
            var $new_filter = $('#field_form input[name=new_filter]');

            var html = $('#tpl_field').html();

            var id = Date.now();

            html = html.replace('{new_field_name}', $new_field_name.val()).replace('{new_export_name}', $new_export_name.val())
                .replace('{new_filter}', $new_filter.val()).replace('{field_id}', id).replace('{field_id}', id);

            $('#fields_container').append(html);

        }

        window.delete_field = function(field_id){
            $('#field_' + field_id).remove();
        }
    })(jQuery);
</script>
</body>
</html>
