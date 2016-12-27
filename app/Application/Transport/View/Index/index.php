<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">

    <Admintemplate file="Common/Nav"/>

    <div class="table_list">
        <table width="100%">
            <thead>
            <tr>
                <td>标题</td>
                <td>描述</td>
                <td>类型</td>
                <td>创建时间</td>
                <td>操作</td>
            </tr>
            </thead>

            <volist name="data" id="r">
                <?php
                $modified = $r['modified_time'] ? date("Y-m-d H:i",$r['modified_time']) : '-';
                $next = $r['next_time'] ? date("Y-m-d H:i",$r['next_time']) : '-';
                ?>
                <tr>
                    <td>{$r.title}</td>
                    <td>{$r.description}</td>
                    <td>
                        <if condition="$r['type'] EQ 1">导入任务</if>
                        <if condition="$r['type'] EQ 2">导出任务</if>
                    </td>
                    <td>{:date('Y-m-d H:i:s', $r['inputtime'])}</td>

                    <td>
                        <a href="{:U('Transport/Index/task_edit_index',array('id'=>$r['id']))}" class="mr5"> 编辑 </a>
                        |  <a class="J_ajax_del" href="{:U('Transport/Index/task_delete',array('id'=>$r['id']))}"> 删除 </a>
                        |  <a href="{:U('Transport/Index/task_exec_index',array('id'=>$r['id']))}"> 立即执行 </a>
                    </td>
                </tr>
            </volist>
        </table>
    </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
    $(function(){
        $('#J_time_select').on('change', function(){
            $('#J_time_'+ $(this).val()).show().siblings('.J_time_item').hide();
        });

        var lock = false;
        $('a.J_cron_back').on('click', function(e){
            e.preventDefault();
            var $this = $(this);
            if(lock) {
                return false;
            }
            lock = true;

            $.post(this.href, function(data) {
                lock = false;
                if(data.state === 'success') {
                    $( '<span class="tips_success fr">' + data.message + '</span>' ).insertAfter($this).fadeIn( 'fast' );
                    reloadPage(window);
                }else if( data.state === 'fail' ) {
                    Wind.dialog.alert(data.message);
                }
            }, 'json');
        });
    });
</script>
</body>
</html>
