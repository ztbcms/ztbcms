var sinabinding = { //QQ账号绑定
	init: function(){
		//绑定
		var $rqloginName = $('#rqloginName');
		var $mqloginName = $('#mqloginName');
		var $rqpassword = $('#rqpassword');
		var $mqpassword = $('#mqpassword');
		var $binding = $('#binding');
		
		//注册
		var $username = $("#username");
		var $musername = $("#musername");
		var $rpassword = $('#rpassword');
		var $mpassword = $('#mpassword');
		var $rpassword2 = $('#rpassword2');
		var $mpassword2 = $('#mpassword2');
		var $rnickname = $('#rnickname');
		var $mnickname = $('#mnickname');
		var $remail = $('#remail');
		var $memail = $('#memail');
		var $rvCode = $('#rvCode');
		var $submit = $('#submit');

		$rqloginName.focus(function (){
			$mqloginName.html('<div class="err_message"><span class="icon">请输入您的登录账号！</span></div>');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mqloginName.html('<div class="err_message error"><span class="icon">请输入您的登录账号！</span></div>');
				return false;
			}
			else if($(this).val().length<3){
				$mqloginName.html('<div class="err_message error"><span class="icon">登录账号长度应大于3位！</span></div>');
				return false;
			}
			else{
				$mqloginName.html('<div class="err_message right"><span class="rightIcon"></span></div>');
			}
		});
		
		$rqpassword.focus(function (){
			$(this).addClass('input_focus');
			$mqpassword.html('<div class="err_message"><span class="icon">请输入您的登录密码。</span></div>');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mqpassword.html('<div class="err_message error"><span class="icon">请输入您的登录密码！</span></div>');
				return false;
			}else if($(this).val().length<6){
				$mqpassword.html('<div class="err_message error"><span class="icon">密码长度应是6位以上！</span></div>');
				return false;
			}
			else{
				$mqpassword.html('<div class="err_message right"><span class="rightIcon"></span></div>');
			}
		});
		//进行绑定
		$binding.click(function(){
			$('#qqBinding :input').trigger('keyup');
			var numError = $('#qqBinding .icon').length;
			if(!numError){
				$.ajax({
					type: "POST",
					global: false,// 禁用全局Ajax事件.
					url: _config['domainSite']+"index.php?g=Member&m=Public&a=connectbinding",
					data:{
						loginName:$rqloginName.val(), 
						password:$rqpassword.val()
					},
					dataType: "json",
					success: function(data){
						if(data['status']){
							$.tipMessage(data['info'], 0, 2000,0,function(){
								if(data['url']){
									libs.redirect(data['url']);
								}
							});
						}else{
							$.tipMessage(data['info'], 1, 3000);
						}
					}
				});
			}
			return false;
		});
		
		//----------
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
		$remail.focus(function (){
			$memail.html('<div class="err_message"><span class="icon">请输入您常用的Email电子邮箱。</span></div>');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			var search_str = /^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/;
			if($(this).val() == ''){
				$memail.html('<div class="err_message error"><span class="icon">请输入您的Email电子邮箱。</span></div>');
				return false;
			}
			else if(!search_str.test($(this).val())){
				$memail.html('<div class="err_message error"><span class="icon">输入的电子邮箱格式不正确。</span></div>');
				return false;
			}
			else{
				$memail.html('<div class="err_message right"><span class="rightIcon"></span></div>');
			}
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Public&a=checkEmail",
                data: {
                    email: $remail.val()
                },
				dataType: "json",
				success: function(data){
					if(data.status){
						$memail.html('<div class="err_message right"><span class="rightIcon"></span></div>');
					}else{
						$memail.html('<div class="err_message error"><span class="icon">'+data.info+'</span></div>');
                        return false;
					}
				},
				error: function() {
					alert("数据执行错误！");
					return false;
				}
			});
		});
		
		$rpassword.focus(function (){
			$mpassword.html('<div class="err_message"><span class="icon">请输入6位以上字符做为密码。</span></div>');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mpassword.html('<div class="err_message error"><span class="icon">请输入您的登录密码。</span></div>');
				return false;
			}else if($(this).val().length<6){
				$mpassword.html('<div class="err_message error"><span class="icon">密码长度应在6位以上。</span></div>');
				return false;
			}
			else{
				$mpassword.html('<div class="err_message right"><span class="rightIcon"></span></div>');
			}
		});
		
		$rpassword2.focus(function (){
			$mpassword2.html('<div class="err_message"><span class="icon">再次输入密码，以确认密码无误。</span></div>');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mpassword2.html('<div class="err_message error"><span class="icon">请输入确认密码！</span></div>');
				return false;
			}
			else if($(this).val() != $rpassword.val()){
				$mpassword2.html('<div class="err_message error"><span class="icon">两次输入密码不相同！</span></div>');
				return false;
			}
			else{
				$mpassword2.html('<div class="err_message right"><span class="rightIcon"></span></div>');
			}
		});
		
		$rnickname.focus(function (){
			$mnickname.html('<div class="err_message"><span class="icon">请填写您的名字。</span></div>');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mnickname.html('<div class="err_message error"><span class="icon">请填写您的名字！</span></div>');
				return false;
			}
			else if(!/^([\S])*$/.test($(this).val())){
				$mnickname.html('<div class="err_message error"><span class="icon">名字中不能有空格！</span></div>');
				return false;
			}
			else if(!/^([^0-9])*$/.test($(this).val())){
				$mnickname.html('<div class="err_message error"><span class="icon">名字中不能有数字！</span></div>');
				return false;
			}
			else if(!/^([^<>'"\/\\])*$/.test($(this).val())){
				$mnickname.html('<div class="err_message error"><span class="icon">名字中不能有 < > \' \" / \\ 等非法字符！</span></div>');
				return false;
			}
			else if($(this).val().length<2){
				$mnickname.html('<div class="err_message error"><span class="icon">名字最少要两位以上！</span></div>');
				return false;
			}
			else if($(this).val().length>6){
				$mnickname.html('<div class="err_message error"><span class="icon">名字不能大于六位！</span></div>');
				return false;
			}
			else{
				$mnickname.html('<div class="err_message right"><span class="rightIcon"></span></div>');
			}
			
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Public&a=checkNickname",
                data: {
                    nickname: $rnickname.val()
                },
				dataType: "json",
				success: function(data){
					if(data.status){
						$mnickname.html('<div class="err_message right"><span class="rightIcon"></span></div>');
					}else{
						$mnickname.html("<div class='err_message error'><span class='icon'></span>" + $data['info'] + "</span></div>").show();
                        return false;
					}
				}
			});
		});
		
		$submit.click(function(){
			$('#regData :input').trigger('keyup');
			var numError = $('#regData .icon').length;
			if(!numError){
				$("#register").html("<span class='input_register'>资料完善中...</span>");
				$.ajax({
					type: "POST",
					global: false,// 禁用全局Ajax事件.
					url: _config['domainSite'] + "index.php?g=Member&m=Public&a=connectregister",
					data:{
						username:$username.val(),
						password:$rpassword.val(), 
						password2: $rpassword2.val(), 
						nickname:$rnickname.val(), 
						email:$remail.val()
					},
					dataType: "json",
					success: function(data){
						if (data.status) {
							$.tipMessage('操作成功！', 0, 2000,0,function(){
                                libs.redirect(_config['domainSite']+'index.php?g=Member&a=regavatar');
                            });
                        }else{
							libs.changeAuthCode();
                            $.tipMessage(data['info'], 0, 3000,0,function(){
                                if(data['url']){
                                    libs.redirect(data['url']);
                                }
                            });
                            return false;
						}
					}
				});
			}
			return false;
		});
	}
};