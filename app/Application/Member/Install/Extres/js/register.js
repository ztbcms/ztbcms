var register = {
    init: function () {
        var $username = $("#username");
        var $rpassword = $("#rpassword");
        var $mpassword = $("#mpassword");
        var $rpassword2 = $("#rpassword2");
        var $mpassword2 = $("#mpassword2");
        var $rnickname = $("#rnickname");
        var $mnickname = $("#mnickname");
		
        var $raddress = $('#raddress');
        var $maddress = $('#maddress');
        var $oldaddress = $('#oldaddress');
		var $musername = $("#musername");
        var $remail = $('#remail');
        var $memail = $('#memail');
        var $rvCode = $('#rvCode');
        var $mvCode = $('#mvCode');
        var $submit = $('#submit');

        //用户名检查
		$username.focus(function () {
            $(this).addClass('input_focus');
            $musername.html('<div class="err_message"><span class="icon">请输入用户名。</span></div>');
        }).blur(function () {
            $(this).removeClass('input_focus');
            $(this).triggerHandler('keyup');
        }).keyup(function () {
            if ($(this).val() == '') {
                $musername.html('<div class="err_message error"><span class="icon">请输入用户名。</span></div>');
                return false;
            } else {
                $musername.html('<div class="err_message right"><span class="rightIcon"></span></div>');
            }
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Public&a=checkUsername",
                data: {
                    username: $username.val()
                },
                dataType: "json",
                success: function (data) {
					if(data.status){
						$musername.html('<div class="err_message right"><span class="rightIcon"></span></div>');
					}else{
						$musername.html('<div class="err_message error"><span class="icon">'+data.info+'</span></div>');
						return false;
					}
                },
                error: function () {
                    alert("数据执行错误。");
                    return false;
                }
            });
        });
		//邮箱地址
        $remail.focus(function () {
            $(this).addClass('input_focus');
            $memail.html('<div class="err_message"><span class="icon">请输入您常用的Email电子邮箱。</span></div>');
        }).blur(function () {
            $(this).removeClass('input_focus');
            $(this).triggerHandler('keyup');
        }).keyup(function () {
            var search_str = /^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/;
            if ($(this).val() == '') {
                $memail.html('<div class="err_message error"><span class="icon">请输入您的Email电子邮箱。</span></div>');
                return false;
            } else if (!search_str.test($(this).val())) {
                $memail.html('<div class="err_message error"><span class="icon">输入的电子邮箱格式不正确。</span></div>');
                return false;
            } else {
                $memail.html('<div class="err_message right"><span class="rightIcon"></span></div>');
            }
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Public&a=checkEmail",
                data: {
                    email: $remail.val()
                },
                dataType: "json",
                success: function (data) {
                    if(data.status){
						$memail.html('<div class="err_message right"><span class="rightIcon"></span></div>');
					}else{
						$memail.html('<div class="err_message error"><span class="icon">'+data.info+'</span></div>');
						return false;
					}
                },
                error: function () {
                    alert("数据执行错误。");
                    return false;
                }
            });
        });
		//设置密码
        $rpassword.focus(function () {
            $(this).addClass('input_focus');
            $mpassword.html('<div class="err_message"><span class="icon">请输入6位以上字符做为密码。</span></div>');
        }).blur(function () {
            $(this).removeClass('input_focus');
            $(this).triggerHandler('keyup');
        }).keyup(function () {
            if ($(this).val() == '') {
                $mpassword.html('<div class="err_message error"><span class="icon">请输入您的登录密码。</span></div>');
                return false;
            } else if ($(this).val().length < 6) {
                $mpassword.html('<div class="err_message error"><span class="icon">密码长度应在6位以上。</span></div>');
                return false;
            } else {
                $mpassword.html('<div class="err_message right"><span class="rightIcon"></span></div>');
            }
        });
		//确认密码
        $rpassword2.focus(function () {
            $(this).addClass('input_focus');
            $mpassword2.html('<div class="err_message"><span class="icon">再次输入密码，以确认密码无误。</span></div>');
        }).blur(function () {
            $(this).removeClass('input_focus');
            $(this).triggerHandler('keyup');
        }).keyup(function () {
            if ($(this).val() == '') {
                $mpassword2.html('<div class="err_message error"><span class="icon">请输入确认密码。</span></div>');
                return false;
            } else if ($(this).val() != $rpassword.val()) {
                $mpassword2.html('<div class="err_message error"><span class="icon">两次输入密码不相同。</span></div>')
                return false;
            } else {
                $mpassword2.html('<div class="err_message right"><span class="rightIcon"></span></div>');
            }
        });
		//昵称
        $rnickname.focus(function () {
            $(this).addClass('input_focus');
            $mnickname.html('<div class="err_message"><span class="icon">请填写您的昵称。</span></div>');
        }).blur(function () {
            $(this).removeClass('input_focus');
            $(this).triggerHandler('keyup');
        }).keyup(function () {
            if ($(this).val() == '') {
                $mnickname.html('<div class="err_message error"><span class="icon">请填写您的昵称。</span></div>');
                return false;
            } else if (!/^([\S])*$/.test($(this).val())) {
                $mnickname.html('<div class="err_message error"><span class="icon">昵称中不能有空格。</span></div>');
                return false;
            } else if (!/^([^0-9])*$/.test($(this).val())) {
                $mnickname.html('<div class="err_message error"><span class="icon">昵称中不能有数字。</span></div>');
                return false;
            } else if ($(this).val().length < 2) {
                $mnickname.html('<div class="err_message error"><span class="icon">昵称最少要两位以上。</span></div>').show();
                return false;
            }

            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Public&a=checkNickname",
                data: {
                    nickname: $rnickname.val()
                },
                dataType: "json",
                success: function (data) {
					if(data.status){
						$mnickname.html('<div class="err_message right"><span class="rightIcon"></span></div>');
					}else{
						$mnickname.html('<div class="err_message error"><span class="icon">'+data.info+'</span></div>');
						return false;
					}
                },
                error: function () {
                    alert("数据执行错误！");
                    return false;
                }
            });
        });

        $("#authCode").click(function () {
            libs.changeAuthCode();
            return false;
        });

        $('#changeAuthCode').click(function () {
            libs.changeAuthCode();
            return false;
        });

        $rvCode.focus(function () {
            $(this).addClass('input_focus');
            $mvCode.html('<div class="err_message"><span class="icon">请输入验证码，如看不清请更换一个。</span></div>');

        }).blur(function () {
            $(this).removeClass('input_focus');
            $(this).triggerHandler('keyup');
        }).keyup(function () {
            if ($(this).val() == '') {
                $mvCode.html('<div class="err_message error"><span class="icon">请输入验证码。</span></div>');
                return false;
            } else if ($(this).val().length != 4) {
                $mvCode.html('<div class="err_message error"><span class="icon">请正确输入四位验证码。</span></div>');
                return false;
            } else {
                $mvCode.html('');
            }
        });
		$submit.click(function () {
            $('#regData :input').trigger('keyup');
            var numError = $('#regData .icon').length;
            var note = $("#openQQ").text();
            if (!numError) {
                $.ajax({
                    type: "POST",
                    global: false, // 禁用全局Ajax事件.
                    url: _config['domainSite'] + "index.php?g=Member&m=Public&a=doRegister",
                    data: {
						username: $username.val(),
                        password: $rpassword.val(),
                        password2: $rpassword2.val(),
                        nickname: $rnickname.val(),
                        email: $remail.val(),
                        vCode: $rvCode.val()
                    },
                    dataType: "json",
                    success: function (data) {
						if(data.status){
							libs.changeAuthCode();
                            $("#submit").attr('disabled', 'disabled');
                            $.tipMessage('帐号注册成功！', 0, 2000,0,function(){
                                libs.redirect(_config['domainSite']+'index.php?g=Member&a=regavatar');
                            });
						}else{
							$.tipMessage(data.info, 2, 3000,0,function(){
								//刷新验证码
                                libs.changeAuthCode();
                            });
							return false;
						}
                    },
                    error: function () {
                        alert("数据执行错误！");
                        return false;
                    }

                });
            }
        });
        return false;
    }
}