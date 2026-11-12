<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>安装向导</title>
    <link rel="stylesheet" href="/statics/modules/install/css/install.css"/>
    <script type="text/javascript" src="/statics/modules/install/js/jquery.js"></script>
</head>
<body>
<div class="wrap">
    {include file="index/header" /}
    <section class="section">
        <div class="step">
            <ul>
                <li class="on"><em>1</em>检测环境</li>
                <li class="on"><em>2</em>创建数据</li>
                <li class="current"><em>3</em>完成安装</li>
            </ul>
        </div>
        <div class="install" id="log">
            <ul id="loginner">
            </ul>
        </div>
        <div class="bottom tac"><a href="javascript:;" class="btn_old">正在安装...</a></div>
    </section>
    <script type="text/javascript">
        var n = 0;
        var data = {$data|raw};

        function reloads(n) {
            var url = "{:api_url('/install/index/doInstall')}?n=" + n;
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'json',
                success: function (res) {
                    var resultData = res.data || {};

                    if (Array.isArray(resultData.logs)) {
                        $.each(resultData.logs, function (_, item) {
                            var $icon = $('<span>')
                                .addClass(item.status ? 'correct_span' : 'correct_span error_span')
                                .html(item.status ? '&radic;' : '&times;');
                            var $li = $('<li>')
                                .append($icon)
                                .append(document.createTextNode(item.msg || ''));
                            $('#loginner').append($li);
                        });
                    }

                    if (!res.status) {
                        $('.btn_old').text('安装失败，请处理后重试');
                        alert(res.msg || '安装失败');
                        return;
                    }

                    if (resultData.n == '{$finished_step}') {
                        $('.btn_old').text('安装完成，正在跳转...');
                        setTimeout('gonext()', 1500);
                        return;
                    }
                    if (resultData.n && resultData.n >= 0) {
                        reloads(resultData.n);
                    } else {
                        alert(res.msg || '安装失败');
                    }
                }
            });
        }

        function gonext() {
            window.location.href = "{:api_url('/install/index/step5')}";
        }

        $(document).ready(function () {
            reloads(n);
        })
    </script>
</div>
</body>
</html>
