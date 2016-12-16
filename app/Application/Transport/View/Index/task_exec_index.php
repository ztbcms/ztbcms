<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">

    <Admintemplate file="Common/Nav"/>
    <div class="h_a">执行任务</div>
    <form class=""  action="{:U('Transport/Index/task_log_create')}" method="post">
        <div class="table_full">
            <table width="100%">
                <col class="th" />
                <col width="300" />
                <col />
                <tr>
                    <th>任务标题</th>
                    <td>{$title}
                        <input type="hidden" name="title" value="{$title}">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr>
                    <th>任务描述</th>
                    <td>{$description}
                        <input type="hidden" name="remark" value="{$description}">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr>
                    <th>任务类型</th>
                    <td>
                        <if condition="$type EQ 1">导入任务</if>
                        <if condition="$type EQ 2">导出任务</if>

                        <input type="hidden" name="type" value="{$type}">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr>
                    <th>模型</th>
                    <td>
                        <?php $_model = M('Model')->where(['tablename' => $model])->find();?>
                        {$_model['name']}
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <tr style="display: none;">
                    <th>关联任务ID</th>
                    <td>
                        <input type="text" name="task_id" value="{$id}">
                    </td>
                    <td><div class="fun_tips"></div></td>
                </tr>

                <if condition="$type EQ 1">
                    <tr>
                        <th>导入文件</th>
                        <td><input type="file" class="input length_5 mr5" name="filename" value=""></td>
                        <td><div class="fun_tips"></div></td>
                    </tr>
                </if>

                <if condition="$type EQ 2">
                    <tr>
                        <th>导出文件名</th>
                        <td><input type="text" class="input length_5 mr5" name="filename" value=""></td>
                        <td><div class="fun_tips">默认:标题+创建时间</div></td>
                    </tr>
                </if>

            </table>
        </div>
        <div class="">
            <div class="btn_wrap_pd">
                <button class="btn btn_submit " type="submit">执行</button>
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
