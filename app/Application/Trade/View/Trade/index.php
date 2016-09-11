<?php if (!defined('SHUIPF_VERSION')) exit(); ?>
<Admintemplate file="Common/Head" />

<body class="J_scroll_fixed">
    <div class="h_a">搜索</div>
    <form method="post" action="{:U('index')}">
        <input type="hidden" value="{$catid}" name="catid">
        <input type="hidden" value="0" name="steps">
        <input type="hidden" value="1" name="search">
        <div class="search_type cc mb10">
            <div class="mb10">
                <span class="mr20">时间：
        <input type="text" name="start_time" class="input length_2 J_date" value="{$Think.get.start_time}" style="width:180px;">-<input type="text" class="input length_2 J_date" name="end_time" value="{$Think.get.end_time}" style="width:180px;">
        <select class="select_2" name="status"style="width:70px;">
          <option value='' <if condition=" $status === '' "> selected</if>>全部</option>
          <option value="1" <if condition=" $status === '1' "> selected</if>>有效</option>
          <option value="0" <if condition=" $status === '0' "> selected</if>>无效</option>
        </select>
        <select class="select_2" name="type" style="width:170px;">
          <option value='' <if condition=" $status === '' "> selected</if>>全部</option>
        <volist name="trade_type" id="vo">
          <option value='{$vo.type}' <if condition=" $type == $vo[type] "> selected</if>>{$vo.type}</option>
          </volist>
        </select>
        <button class="btn">搜索</button>
        </span>
            </div>
        </div>
    </form>
    <form class="J_ajaxForm" action="" method="post" style="padding:0px 10px;">
        <div class="table_list">
            <table width="100%">
                <thead>
                    <tr>
                        <td align="center">收入</td>
                        <td align="center">支出</td>
                        <td align="center">余额</td>
                        <td align="center">所属用户</td>
                        <td align="center">状态</td>
                        <td align="center">类型</td>
                        <td align="center">描述</td>
                        <td align="center">交易凭证</td>
                        <td align="center"><span>创建时间</span></td>
                        <!--<td align="center"><span>更新时间</span></td>-->
                    </tr>
                </thead>
                <volist name="trades" id="vo">
                    <tr>
                        <td align="center">{$vo.income}</td>
                        <td align="center">{$vo.pay}</td>
                        <td align="center">{$vo.balance}</td>
                        <td align="center">{$vo.userid}</td>
                        <td align="center">
                            {$vo.status}
                        </td>
                        <td align="center">
                            {$vo.type}
                        </td>
                        <td align="center">
                            {$vo.detail}
                        </td>
                        <td align="center">
                            {$vo.trade_no}
                        </td>
                        <td align="center">{$vo.create_time|date="Y-m-d H:i:s",###}</td>
                        <!--<td align="center">{$vo.update_time|date="Y-m-d H:i:s",###}</td>-->
                    </tr>
                </volist>
            </table>
            <div class="p10">
                <div class="pages"> {$Page} </div>
            </div>

        </div>
    </form>
    </div>
    <script src="{$config_siteurl}statics/js/common.js?v"></script>
    <script>
        setCookie('refersh_time', 0);

        function refersh_window() {
            var refersh_time = getCookie('refersh_time');
            if (refersh_time == 1) {
                window.location.reload();
            }
        }
        setInterval(function() {
            refersh_window()
        }, 3000);
        $(function() {
            Wind.use('ajaxForm', 'artDialog', 'iframeTools', function() {
                //批量移动
                $('#J_Content_remove').click(function(e) {
                    var str = 0;
                    var id = tag = '';
                    $("input[name='ids[]']").each(function() {
                        if ($(this).attr('checked')) {
                            str = 1;
                            id += tag + $(this).val();
                            tag = '|';
                        }
                    });
                    if (str == 0) {
                        art.dialog.through({
                            id: 'error',
                            icon: 'error',
                            content: '您没有勾选信息，无法进行操作！',
                            cancelVal: '关闭',
                            cancel: true
                        });
                        return false;
                    }
                    var $this = $(this);
                    art.dialog.open("{$config_siteurl}index.php?g=Content&m=Content&a=remove&catid={$catid}&ids=" + id, {
                        title: "批量移动"
                    });
                });
            });
        });

        function view_comment(obj) {
            Wind.use('artDialog', 'iframeTools', function() {
                art.dialog.open($(obj).attr("data-url"), {
                    close: function() {
                        $(obj).focus();
                    },
                    title: $(obj).attr("data-title"),
                    width: "800px",
                    height: '520px',
                    id: "view_comment",
                    lock: true,
                    background: "#CCCCCC",
                    opacity: 0
                });
            });
        }

        function pushs() {
            var str = 0;
            var id = tag = '';
            $("input[name='ids[]']").each(function() {
                if ($(this).attr('checked')) {
                    str = 1;
                    id += tag + $(this).val();
                    tag = '|';
                }
            });
            if (str == 0) {
                art.dialog({
                    id: 'error',
                    icon: 'error',
                    content: '您没有勾选信息，无法进行操作！',
                    cancelVal: '关闭',
                    cancel: true
                });
                return false;
            }
            Wind.use('artDialog', 'iframeTools', function() {
                art.dialog.open("{$config_siteurl}index.php?g=Content&m=Content&a=push&action=position_list&modelid={$modelid}&catid={$catid}&id=" + id, {
                    title: "信息推送"
                });
            });
        }
    </script>
</body>

</html>