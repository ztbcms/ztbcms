<Admintemplate file="Common/Head" />
<style>
    td {
        padding: 10px 5px;
    }
</style>

<body class="J_scroll_fixed">
    <div style="padding-left:20px;">
        <form action="{:U('setting')}" method="post">
            <h3> 微信设置</h3>
            <div>
                <table style="width: 100%;">
                    <tr>
                        <td colspan="2" style="background: #eee;">绑定会员模型</td>
                    </tr>
                    <tr>
                        <td width="100px"><label for="">所属会员模型</label></td>
                        <td>
                            <select name="wx_modelid" id="">
                            <volist name="memeber_models" id="vo">
                            <option <if condition="$vo['modelid'] eq $config['wx_modelid']">selected</if> value="{$vo.modelid}">{$vo.name}</option>
                            </volist>
                        </select>
                        </td>
                    </tr>
                </table>
            </div>
            <div>
                <table style="width: 100%;">
                    <tr>
                        <td colspan="2" style="background: #eee;">微信相关配置</td>
                    </tr>
                    <tr>
                        <td width="100px"><label for="">app_id</label></td>
                        <td><input value="{$config.wx_app_id}" name="wx_app_id" style="width:300px;" class="form-control" type="text"></td>
                    </tr>
                    <tr>
                        <td width="100px"><label for="">secret</label></td>
                        <td><input value="{$config.wx_secret}" name="wx_secret" style="width:300px;" class="form-control" type="text"></td>
                    </tr>
                </table>
            </div>
            <div>
                <table style="width: 100%;">
                    <tr>
                        <td colspan="2" style="background: #eee;">open平台相关配置</td>
                    </tr>
                    <tr>
                        <td width="100px"><label for="">app_id</label></td>
                        <td><input value="{$config.open_app_id}" name="open_app_id" style="width:300px;" class="form-control" type="text"></td>
                    </tr>
                    <tr>
                        <td width="100px"><label for="">secret_key</label></td>
                        <td><input value="{$config.open_secret_key}" name="open_secret_key" style="width:300px;" class="form-control" type="text"></td>
                    </tr>
                    <tr>
                        <td width="100px"><label for="">alias</label></td>
                        <td><input value="{$config.open_alias}" name="open_alias" style="width:300px;" class="form-control" type="text"></td>
                    </tr>
                </table>
            </div>
            <div>
                <button class="btn btn-primary">保存</button>
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
    </script>
</body>

</html>