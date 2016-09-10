var lostpass = { //找回密码
	init: function(){
		var $lostPass = $('#lostPass');
		var $rloginName = $('#rloginName');
		var $mloginName = $('#mloginName');
		var $rvCode = $('#rvCode');
		var $mvCode = $('#mvCode');
		var $submit = $('#submit');
		
		$rloginName.focus(function (){
			$mloginName.html('<div class="err_message"><span class="icon">请输入您想要找回密码的登录账号。</span></div>');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			var search_str1 = /^([a-zA-Z\_][0-9a-zA-Z]*)+$/;
			var search_str = /^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/;
			if($(this).val() == ''){
				$mloginName.html('<div class="err_message error"><span class="icon">请输入您的登录账号！</span></div>');
				return false;
			}
			else if($(this).val().length<3){
				$mloginName.html('<div class="err_message error"><span class="icon">登录账号长度应大于3位！</span></div>');
				return false;
			}
			else{
				$mloginName.html('<div class="err_message right"><span class="rightIcon"></span></div>');
			}
		});
		
		$rvCode.focus(function (){
			$mvCode.html('<div class="err_message"><span class="icon">请正确输入上面的验证码，如看不清请更换一个。</span></div>');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mvCode.html('<div class="err_message error"><span class="icon">请输入验证码！</span></div>');
				return false;
			}
			else if($(this).val().length!=4){
				$mvCode.html('<div class="err_message error"><span class="icon">请正确输入上面的四位验证码！</span></div>');
				return false;
			}
			else{
				$mvCode.html('');
			}
		});
		
		$("#authCode").click(function(){
			libs.changeAuthCode();
			return false;
		});
		$('#changeAuthCode').click(function(){
			libs.changeAuthCode();
			return false;
		});

		$submit.click(function(){
			$('#lostPass :input').trigger('keyup');
			var numError = $('#lostPass .icon').length;
			if(!numError){
				$.ajax({
					type: "POST",
					global: false,// 禁用全局Ajax事件.
					url: _config['domainSite'] + "index.php?g=Member&m=Public&a=doLostPassword",
					data:{
						loginName:$rloginName.val(), 
						vCode:$rvCode.val()
					},
					dataType: "json",
					success: function(data){
						if(data['error']==20031){ //验证码不正确
							$mvCode.html('<div class="err_message error"><span class="icon">请正确输入上面的四位验证码！</span></div>');
							libs.changeAuthCode();
							$rvCode.val('').focus();
							return false;
						}
						else if(data['error']==1012){ //登录账号不存在
							$mloginName.html('<div class="err_message error"><span class="icon">账号&nbsp;'+$rloginName.val()+'&nbsp;不存在，请输入正确的登录账号！</span></div>');
							return false;
						}
						else{
							mailStr = '';
							if(data['email']!=''){
								mailStr += '<a href="javascript:;" onclick="lostpass.doSendEmail(\''+data['email']+'\',\''+data['email1key']+'\')" title="将邮件发送到 '+data['email']+'">'+data['email']+'</a>&nbsp;&nbsp;&nbsp;';
							}
							listStr = '<li><div class="rM_title">登录账号：</div><div class="rM_input"><span class="nickname">'+$rloginName.val()+'</span></div></li>';
							listStr += '<li><div class="rM_title">注册邮箱：</div><div class="rM_input"><span class="email">'+mailStr+'</span></div></li>';
							listStr += '<li><div class="rM_noleft2"><span class="note">提示：请先确认上面的邮箱是否为您的邮箱，然后选择您用来接收密码找回邮件的邮箱。</span></div></li>';
							
							$lostPass.html(listStr);
						}
					}
				});
			}
		});
		return false;
	},
	doSendEmail: function(email, key){
	
		var $lostPass = $('#lostPass');
		$.ajax({
			type: "POST",
			global: false,// 禁用全局Ajax事件.
			url: _config['domainSite'] + "index.php?g=Member&m=Public&a=doLostPassEmail",
			data:{key: key},
			dataType: "json",
			success: function(data){
				if(data['error']==1100){
					alert('本次请求已经失效，请从新提交密码找回申请。');
					libs.redirect(_config['domainSite']+'index.php?g=Member&a=lostpassword');
				}
				else if(data['error'] == 10000){
					listStr = '<li><div class="rM_noleft2"><span class="note">邮件已经发送到 '+email+' 请去您的邮箱查收邮件，</span><a href="'+_config['domainSite']+'">返回</a>。</div></li>';
					listStr += '<li><div class="rM_noleft2"><span class="note">如果在收件箱中没有找到邮件，请到垃圾邮件箱中查找。</span></div></li>';
					$lostPass.html(listStr);
				}
				else {
					alert(data['info']);
					libs.redirect(_config['domainSite']+'index.php?g=Member&a=lostpassword');
				}
			}
		});
	},
	resetInit: function(){
		var $submit = $('#submit');
		var $rpassword = $('#rpassword');
		var $mpassword = $('#mpassword');
		var $rpassword2 = $('#rpassword2');
		var $mpassword2 = $('#mpassword2');
		var $key = $('#key');
		var $submit = $('#submit');
		
		$rpassword.focus(function (){
			$mpassword.html('<div class="err_message"><span class="icon">6到20个字符，请使用英文字母（区分大小写）、符号或数字。</span></div>');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mpassword.html('<div class="err_message error"><span class="icon">请输入您的新密码！</span></div>');
				return false;
			}else if($(this).val().length<6){
				$mpassword.html('<div class="err_message error"><span class="icon">密码长度应大于6位！</span></div>');
				return false;
			}
			else{
				$mpassword.html('<div class="err_message right"><span class="rightIcon"></span></div>');
			}
		});
		
		$rpassword2.focus(function (){
			$mpassword2.html('再次输入您所设置的新密码，以确认密码无误。');
		}).blur(function(){
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mpassword2.html('<div class="err_message error"><span class="icon">请输入确认新密码！</span></div>');
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
		
		$submit.click(function(){
			$('#resetPass :input').trigger('keyup');
			var numError = $('#resetPass .icon').length;
			//alert($rpassword.val());
			//return;
			if(!numError){
				$.ajax({
					type: "POST",
					global: false,// 禁用全局Ajax事件.
					url: _config['domainSite']+'index.php?g=Member&m=Public&a=resetpassword',
					data:{
						key: $key.val(), 
						password: $rpassword.val(), 
						password2: $rpassword2.val()
					},
					dataType: "jsonp",
					success: function(data){
						if(data['error']==1100){
							$.tipMessage('本次请求已经失效，请从新提交密码找回申请。', 0, 3000,0,function(){
								libs.redirect(_config['domainSite']+'index.php?g=Member&a=lostpassword');
							});
						}
						else if(data['error']==1014){
							$.tipMessage('两次输入密码不相同，请从新输入！', 0, 3000);
						}
						else if(data['error']==10000){
							$.tipMessage('密码已经修改成功！', 0, 3000,0,function(){
								libs.redirect(_config['domainSite']+'index.php?g=Member');
							});
						}
						else {
							$.tipMessage(data['info'], 0, 3000);
						}
					}
				});
			}
		});
	}
};


