<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">

    <Admintemplate file="Common/Nav"/>
    <div class="h_a">添加任务</div>
    <form class="J_ajaxForm"  action="{:U('Transport/Index/task_create')}" method="post">
        <div class="table_full">
            <table width="100%">
                <col class="th" />
                <col width="400" />
                <col />
                <tr>
                    <th>任务标题</th>
                    <td><input type="text" class="input length_5 mr5" name="title" value=""></td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr>
                    <th>任务描述</th>
                    <td><input type="text" class="input length_5 mr5" name="description" value=""></td>
                    <td><div class="fun_tips"></div></td>
                </tr>
                <tr>
                    <th>任务类型</th>
                    <td>
                        <select  name="type" class="mr10">
                            <option value="1">导入任务</option>
                            <option value="2">导出任务</option>
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
                                <option value="{$model['tablename']}">{$model['name']}</option>
                            </volist>
                        </select>
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

            </table>
        </div>
        <div class="">
            <div class="btn_wrap_pd">
                <button class="btn btn_submit J_ajax_submit_btn" type="submit">提交</button>
            </div>
        </div>
    </form>
    <!--结束-->
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
    (function($){

    })(jQuery);
</script>
</body>
</html>
