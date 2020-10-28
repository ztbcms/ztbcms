<!doctype html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>安装向导</title>
    <link rel="stylesheet" href="/statics/extres/install/css/install.css?v=9.0"/>
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

    <div class="section">
        <div class="main cc">
      <pre class="pact" readonly="readonly">ZTBCMS 软件使用协议

版权所有&copy;{:date("Y")}，ZTBCMS保留所有权力。

感谢您选择 ZTBCMS 内容管理系统, 希望我们的产品能够帮您把网站发展的更快、更好、更强！

本授权协议适用于 ZTBCMS 任何版本，本公司拥有对本授权协议的最终解释权和修改权。

ZTBCMS 免责声明
  1、利用 ZTBCMS 构建的网站的任何信息内容以及导致的任何版权纠纷和法律争议及后果，ZTBCMS 官方不承担任何责任。
  2、ZTBCMS 损坏包括程序的使用(或无法再使用)中所有一般化、特殊化、偶然性的或必然性的损坏(包括但不限于数据的丢失，自己或第三方所维护数据的不正确修改，和其他程序协作过程中程序的崩溃等)，ZTBCMS 官方不承担任何责任。

电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦安装使用ZTBCMS，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，ZTBCMS 官方有权随时终止授权，责令停止损害，并保留追究相关责任的权力。</pre>
        </div>
        <div class="bottom tac"><a href="{:api_url('/install/index/step2')}" class="btn">接 受</a></div>
    </div>
</div>
<div class="footer"> &copy; {:date("Y")} <a href="http://www.ztbcms.com" target="_blank">http://www.ztbcms.com</a>（ZTBCMS内容管理系统）</div>

</body>
</html>
