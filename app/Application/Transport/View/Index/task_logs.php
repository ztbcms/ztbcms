<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">

    <Admintemplate file="Common/Nav"/>

    <div class="table_list">
        <table width="100%">
            <thead>
            <tr>
                <td>ID</td>
                <td>计划标题</td>
                <td>备注</td>
                <td>文件名</td>
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
                    <td>{$r.id}</td>
                    <td>{$r.title}</td>
                    <td>{$r.remark}</td>
                    <td>
                        <?php
                        $_task = M('TransportTask')->where(['id' => $r['task_id']])->find();
                        ?>
                        <if condition="$_task['type'] == 1">
                                <a href="{$r['filename']}">点击下载导入Excel文件</a>
                            <else/>
                                {$r['filename']}
                        </if>
                    </td>
                    <td>
                      {:date('Y-m-d H:i:s', $r['inputtime'])}
                    </td>
                    <td><a href="{:U('Transport/Index/task_exec', ['task_log_id' => $r['id']])}" target="_blank">立即执行</a></td>
                </tr>
            </volist>
        </table>
        <div class="p10"><div class="pages"> {$Page} </div> </div>
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
