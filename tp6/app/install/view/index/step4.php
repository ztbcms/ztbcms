<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>安装向导</title>
    <link rel="stylesheet" href="/statics/extres/install/css/install.css?v=9.0"/>
    <script type="text/javascript" src="/statics/extres/install/js/jquery.js"></script>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1 class="logo">logo</h1>
        <div class="icon_install">安装向导</div>
        <div class="version">
            <?php echo \think\facade\Config::get('admin.cms_version'); ?>
        </div>
    </div>
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
        <div class="bottom tac"><a href="javascript:;" class="btn_old"><img src="/statics/extres/install/images/install/loading.gif" align="absmiddle"/>&nbsp;正在安装...</a></div>
    </section>
    <script type="text/javascript">
        var n = 0;
        var data = {$data|raw};

        function reloads(n) {
            var url = "{:api_url('/install/index/doInstall')}?n=" + n;
            console.log(url)
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'json',
                success: function (res) {
                    if(res.data.msg){
                        $('#loginner').append(res.data.msg);
                    }

                    if (res.data.n == '999999') {
                        $('#dosubmit').attr("disabled", false);
                        $('#dosubmit').removeAttr("disabled");
                        $('#dosubmit').removeClass("nonext");
                        setTimeout('gonext()', 1500);
                        return;
                    }
                    if (res.data.n && res.data.n >= 0) {
                        reloads(res.data.n);
                    } else {
                        alert(res.data.msg);
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
{include file="index/footer" /}
</body>
</html>