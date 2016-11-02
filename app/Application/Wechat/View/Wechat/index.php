<Admintemplate file="Common/Head" />
<body class="J_scroll_fixed" style="padding:10px;">
    <div class="h_a">搜索</div>
    <form method="post" action="{:U('index')}">
        <input type="hidden" value="{$catid}" name="catid">
        <input type="hidden" value="0" name="steps">
        <input type="hidden" value="1" name="search">
        <div class="search_type cc mb10">
            昵称：<input type="text" name="nickname" class="input" value="{$nickname}"> openid：
            <input type="text" name="openid" class="input" value="{$openid}">
            <button class="btn">搜索</button>
        </div>
        </div>
    </form>
    <form class="J_ajaxForm" action="" method="post">
        <div class="table_list">
            <table width="100%">
                <thead>
                    <tr>
                        <td align="center">用户id</td>
                        <td align="center">头像</td>
                        <td align="center">昵称</td>
                        <td align="center">性别</td>
                        <td align="center">国籍</td>
                        <td align="center">省份</td>
                        <td align="center">城市</td>
                    </tr>
                </thead>
                <volist name="wx_users" id="vo">
                    <tr>
                        <td align="center">{$vo.userid}</td>
                        <td align="center">
                            <img style="width:80px;" src="{$vo.headimgurl}" alt="">
                        </td>
                        <td align="center">{$vo.nickname}</td>
                        <td align="center">{$vo.sex}</td>
                        <td align="center">
                            {$vo.country}
                        </td>
                        <td align="center">
                            {$vo.province}
                        </td>
                        <td align="center">
                            {$vo.city}
                        </td>
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