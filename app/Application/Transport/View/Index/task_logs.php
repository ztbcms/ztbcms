<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">

    <Admintemplate file="Common/Nav"/>

    <div class="table_list">
        <table width="100%">
            <thead>
            <tr>
                <td>计划标题</td>
                <td>备注</td>
                <td>文件名</td>
                <td>执行时间</td>
            </tr>
            </thead>

            <volist name="data" id="r">
                <?php
                $modified = $r['modified_time'] ? date("Y-m-d H:i",$r['modified_time']) : '-';
                $next = $r['next_time'] ? date("Y-m-d H:i",$r['next_time']) : '-';
                ?>
                <tr>
                    <td>{$r.title}</td>
                    <td>{$r.remark}</td>
                    <td>{$r.filename}</td>
                    <td>
                      {:date('Y-m-d H:i:s', $r['inputtime'])}
                    </td>
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
