
<div class="page-container visibility-hidden">
    <div class="main">
        <form class="mainmenu" onsubmit='return false;' onkeydown="submitByEnter">
            <div class="logo-box flex-between">
                <div class="title">{$_Config['sitename']}</div>
            </div>
            <div class="label">账号</div>
            <div class="input">
                <input type="text" placeholder="请输入账号" id="username">
            </div>
            <div class="label">登录密码</div>
            <div class="input password-input">
                <input type="password" placeholder="请输入密码" id="password">
                <button class="password-toggle" type="button" aria-label="显示密码" aria-pressed="false" title="显示密码">
                    <svg class="password-icon password-icon-hidden is-hidden" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 3l18 18M10.7 10.7a2 2 0 0 0 2.6 2.6M9.9 4.2A10.7 10.7 0 0 1 12 4c5.5 0 9 6 9 6a18.1 18.1 0 0 1-2.1 2.8M6.6 6.6C4.4 8.1 3 10 3 10s3.5 6 9 6a9.8 9.8 0 0 0 3.4-.6"/>
                    </svg>
                    <svg class="password-icon password-icon-visible" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6z"/>
                        <circle cx="12" cy="12" r="2.5"/>
                    </svg>
                </button>
            </div>
            <div class="label">验证码</div>
            <div class="input flex-between" style="margin-bottom: 0px">
                <input type="text" placeholder="请输入验证码" style="flex: 1;" id="code">
                <img id="code_img" class="code"
                     src="{:api_url('/admin/Checkcode/index?code_len=4&font_size=20&width=130&height=50')}"
                     onclick="refreshs()"></a>
            </div>
            <div class="btn-box">
                <button class="default" onclick="doLogin()" disabled>登录</button>
            </div>
        </form>
    </div>
    <div class="footer">
        <p>当前版本 v{:config('admin.application_version')}</p>
        <p> 建议分辨率1366*768以上，推荐使用 </p>
        <p>
            Chrome浏览器 <img src="/statics/admin/pages/public/login/chrome.png" alt="">
            Firefox浏览器 <img src="/statics/admin/pages/public/login/firefox.png" alt="">
            360极速浏览器 <img src="/statics/admin/pages/public/login/360.png" alt="">
            IE11及以上浏览器 <img src="/statics/admin/pages/public/login/ie.png" alt=""></p>
    </div>
</div>


<style>
    html,body{
        height: 100%;
        width: 100%;
    }
    body {
        margin: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: #f6f7ff;
    }

    .flex-between {
        display: flex;
        justify-content: space-between;
    }

    .visibility-visible {
        visibility: visible;
    }

    .visibility-hidden{
        visibility: hidden;
    }

    .main {
        box-sizing: border-box;
        padding: 50px 30px;
        width: 460px;
        height: 532px;
        background: #fff;
        box-shadow: 0px 12px 34px 0px rgba(0, 84, 202, 0.14);
        border-radius: 12px;
        margin: 5% auto;
    }

    .logo-box {
        margin-bottom: 50px;
    }

    .title {
        padding-bottom: 2px;
        font-weight: bold;
        font-size: 24px;
        color: #409eff;
        width: 100%;
        text-align: center;
    }

    .label {
        font-weight: bold;
        font-size: 14px;
        color: #333333;
        line-height: 1;
    }

    .input, .code-box {
        margin-bottom: 44px;
    }

    .input input {
        padding: 11px 0;
        width: 100%;
        border: 0;
        outline: 0;
        border-bottom: 1px solid #ededed;
    }

    .password-input {
        position: relative;
    }

    .password-input input {
        box-sizing: border-box;
        padding-right: 36px;
    }

    .password-toggle {
        position: absolute;
        right: 0;
        bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        color: #909399;
        background: transparent;
        border: 0;
        border-radius: 50%;
        outline: 0;
        cursor: pointer;
    }

    .password-toggle:hover,
    .password-toggle:focus {
        color: #409eff;
        background: #ecf5ff;
    }

    .password-icon {
        display: block;
        width: 20px;
        height: 20px;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .password-icon.is-hidden {
        display: none;
    }

    ::-webkit-input-placeholder { /* WebKit browsers */
        color: #d8d8d8;
    }

    :-moz-placeholder { /* Mozilla Firefox 4 to 18 */
        color: #d8d8d8;
    }

    ::-moz-placeholder { /* Mozilla Firefox 19+ */
        color: #d8d8d8;
    }

    :-ms-input-placeholder { /* Internet Explorer 10+ */
        color: #d8d8d8;
    }

    .code {
        margin-left: 16px;
    }

    .btn-box {
        margin-top: 50px;
    }

    .btn-box button {
        width: 402px;
        height: 50px;
        font-size: 16px;
        color: #fff;
        border-radius: 25px;
        border: 0;
        outline: 0;
    }

    .default {
        background: #d4d4d4;
    }

    .finish {
        background: #409eff;
    }

    .footer {
        z-index: -1;
        position: absolute;
        bottom: 16px;
        color: #909399;
        font-size: 12px;
        text-align: center;
        left: 0;
        width: 100%;
    }

    .footer p {
        margin: 6px;
    }

    .footer a {
        color: #909399;
        text-decoration: none;
    }

    .footer p img {
        vertical-align: bottom;
        height: 20px;
    }
</style>

<script>
    //若当前页面是再后台的内容页，则通知外层页面跳转
    if(parent !== window){
        parent.window.location = "{:api_url('/admin/Login/index')}"
    } else {
       // 非内嵌页面时呈现
       var page_container =  document.querySelector('.page-container')
        page_container.classList.remove('visibility-hidden')
    }

    var inputs = document.querySelectorAll('input');
    var len = inputs.length;
    var btn = document.querySelector('.btn-box button');
    var passwordInput = document.getElementById('password');
    var passwordToggle = document.querySelector('.password-toggle');
    var passwordHiddenIcon = document.querySelector('.password-icon-hidden');
    var passwordVisibleIcon = document.querySelector('.password-icon-visible');


    for (var i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener('input', inputChange);
    }

    passwordToggle.addEventListener('click', togglePasswordVisibility);

    if (!!window.ActiveXObject || "ActiveXObject" in window){
        alert('建议使用IE11及以上的浏览器');
    }

    function inputChange() {
        var index = 0;

        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].value.length > 0) {
                index++;
            }
        }

        if (index == len) {
            btn.classList.remove('default');
            btn.classList.add('finish');
            btn.disabled = false;
        } else {
            btn.classList.remove('finish');
            btn.classList.add('default');
            btn.disabled = true;
        }
    }

    //切换登录密码的显示状态
    function togglePasswordVisibility() {
        var isVisible = passwordInput.type === 'text';
        var nextLabel = isVisible ? '显示密码' : '隐藏密码';

        passwordInput.type = isVisible ? 'password' : 'text';
        passwordToggle.setAttribute('aria-label', nextLabel);
        passwordToggle.setAttribute('aria-pressed', String(!isVisible));
        passwordToggle.setAttribute('title', nextLabel);
        if (isVisible) {
            passwordHiddenIcon.classList.add('is-hidden');
            passwordVisibleIcon.classList.remove('is-hidden');
        } else {
            passwordHiddenIcon.classList.remove('is-hidden');
            passwordVisibleIcon.classList.add('is-hidden');
        }
    }

    //刷新二维码
    function refreshs() {
        document.getElementById('code_img').src = "{:api_url('/admin/Checkcode/index?code_len=4&font_size=20&width=130&height=50')}"+"&refresh=1&time=" + Math.random();
    }

    //按enter的时候触发点击效果
    function submitByEnter(event)
    {
        if(event && event.keyCode === 13) {
            $(".default").click();
        }
    }

    //登录请求
    function doLogin() {
        if (window.requesting) {
            return;
        }
        window.requesting = true
        var username = $('input#username').val();
        var password = $('input#password').val();
        var code = $('input#code').val();
        var data = {
            username: btoa(encodeURIComponent(username.trim())),
            password: btoa(encodeURIComponent(password.trim())),
            code: code.trim(),
        };

        $.ajax({
            url: "{:api_url('/admin/Login/doLogin')}",
            method: 'post',
            dataType: 'json',
            data: {
                form: data
            },
            success: function (res) {
                layer.msg(res.msg)
                if (res.status) {
                    setTimeout(function () {
                        window.location.replace(res.data.forward)
                    }, 800)
                }
                refreshs();
            },
            complete: function (){
                window.requesting = false
            }
        })
    }
</script>
