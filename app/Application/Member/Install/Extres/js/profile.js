var profile = { //个人设置
	init: function(){
		var $cprofile = $('#cprofile');
		var $cavatar = $('#cavatar');
		var $cpassword = $('#cpassword');
		var $modifyProfile = $('#modifyProfile');
		var $modifyAvatar = $('#modifyAvatar');
		var $modifyPassword = $('#modifyPassword');
		var $modifyNickname = $('#modifyNickname');
		
		var $remail = $('#remail');
		var $memail = $('#memail');

		var $rnickname = $('#rnickname');
		var $mnickname = $('#mnickname');
		
		var $roldpassword = $('#roldpassword');
		var $moldpassword = $('#moldpassword');
		var $rpassword = $('#rpassword');
		var $mpassword = $('#mpassword');
		var $rpassword2 = $('#rpassword2');
		var $mpassword2 = $('#mpassword2');
		
		var $seveProfile = $('#seveProfile');
		var $sevePassword = $('#sevePassword');
		
		$cprofile.click(function(){
			$(this).attr('class','current');
			$cavatar.attr('class','');
			$cpassword.attr('class','');
			
			$modifyProfile.show();
			$modifyAvatar.hide();
			$modifyPassword.hide();
			$modifyNickname.hide();
		});
		$cavatar.click(function(){
			$cprofile.attr('class','');
			$(this).attr('class','current');
			$cpassword.attr('class','');
			
			$modifyProfile.hide();
			$modifyAvatar.show();
			$modifyPassword.hide();
			$modifyNickname.hide();
		});
		$cpassword.click(function(){
			$cprofile.attr('class','');
			$cavatar.attr('class','');
			$(this).attr('class','current');
			
			$modifyProfile.hide();
			$modifyAvatar.hide();
			$modifyPassword.show();
			$modifyNickname.hide();
		});
		
		//修改基本资料开始
		//邮箱
		$remail.focus(function(){
			$memail.removeClass('input_tag2');
			$memail.addClass('input_tag1');
			$(this).addClass('input_focus');
			$memail.html('安全邮箱能够帮您取回您忘记的密码。');
		}).blur(function(){
			$(this).removeClass('input_focus');
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			var search_str = /^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/;
			if($(this).val() == ''){
				$memail.removeClass('input_tag1');
				$memail.addClass('input_tag2');
				$memail.html('<span class="errIcon"></span>请输入您的email！');
				return false;
			}
			else if(!search_str.test($(this).val())){
				$memail.removeClass('input_tag1');
				$memail.addClass('input_tag2');
				$memail.html('<span class="errIcon"></span>email格式不正确！');
				return false;
			}
			else{
				$memail.removeClass('input_tag1');
				$memail.removeClass('input_tag2');
				$memail.html('<span class="rightIcon"></span>');
			}
		});
		//昵称
		$rnickname.focus(function (){
			$(this).addClass('input_focus');
			$mnickname.removeClass('input_tag2');
			$mnickname.addClass('input_tag1');
			$mnickname.html('请填写您的名字（不能使用空格、数字、和 < > \' \" / \ 等非法字符）。');
		}).blur(function(){
			$(this).removeClass('input_focus');
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mnickname.removeClass('input_tag1');
				$mnickname.addClass('input_tag2');
				$mnickname.html('<span class="errIcon"></span>请填写您的名字！');
				return false;
			}
			else if(!/^([\S])*$/.test($(this).val())){
				$mnickname.removeClass('input_tag1');
				$mnickname.addClass('input_tag2');
				$mnickname.html('<span class="errIcon"></span>名字中不能有空格！');
				return false;
			}
			else if(!/^([^0-9])*$/.test($(this).val())){
				$mnickname.removeClass('input_tag1');
				$mnickname.addClass('input_tag2');
				$mnickname.html('<span class="errIcon"></span>名字中不能有数字！');
				return false;
			}
			else if(!/^([^<>'"\/\\])*$/.test($(this).val())){
				$mnickname.removeClass('input_tag1');
				$mnickname.addClass('input_tag2');
				$mnickname.html('<span class="errIcon"></span>名字中不能有 < > \' \" / \\ 等非法字符！');
				return false;
			}
			else if($(this).val().length<2){
				$mnickname.removeClass('input_tag1');
				$mnickname.addClass('input_tag2');
				$mnickname.html('<span class="errIcon"></span>名字最少要两位以上！');
				return false;
			}
			else {
				$mnickname.removeClass('input_tag1');
				$mnickname.removeClass('input_tag2');
				$mnickname.html('<span class="rightIcon"></span>');
				return false;
			}
			
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Public&a=checkNickname",
				data:{nickname: $rnickname.val()},
				dataType: "json",
				success: function(data){
					if(data.status){
						$mnickname.html('');
					}else{
						$mnickname.html("<span class='errIcon'></span>" + $data['info'] + "").show();
                        return false;
					}
				},
				error: function() {
					$.tipMessage("数据执行错误！", 2, 3000);
					return false;
				}
			});
		});
		$seveProfile.click(function(){
			$("#modifyProfile :input").trigger('keyup');
			var numError = $('#modifyProfile .errIcon').length;
			if(!numError){
				var options = {
					type:'POST',
					dataType:'JSON',
					success:function(data){
						if(data['status']){
							$.tipMessage('资料已经更新！', 0, 3000);
						}else{
							$.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
						}
					}
				};
				$('#doprofile').ajaxSubmit(options); 
			}
		});

		//修改密码开始
		$roldpassword.focus(function (){
			$(this).addClass('input_focus');
			$moldpassword.removeClass('input_tag2');
			$moldpassword.addClass('input_tag1');
			$moldpassword.html('请输入您当前使用的密码。');
		}).blur(function(){
			$(this).removeClass('input_focus');
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$moldpassword.removeClass('input_tag1');
				$moldpassword.addClass('input_tag2');
				$moldpassword.html('<span class="errIcon"></span>请输入您当前的密码！');
				return false;
			}else if($(this).val().length<6){
				$moldpassword.removeClass('input_tag1');
				$moldpassword.addClass('input_tag2');
				$moldpassword.html('<span class="errIcon"></span>密码长度应大于6位！');
				return false;
			}
			else{
				$moldpassword.removeClass('input_tag1');
				$moldpassword.removeClass('input_tag2');
				$moldpassword.html('<span class="rightIcon"></span>');
			}
		});
		
		$rpassword.focus(function (){
			$(this).addClass('input_focus');
			$mpassword.removeClass('input_tag2');
			$mpassword.addClass('input_tag1');
			$mpassword.html('6到20个字符，请使用英文字母（区分大小写）、符号或数字。');
		}).blur(function(){
			$(this).removeClass('input_focus');
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mpassword.removeClass('input_tag1');
				$mpassword.addClass('input_tag2');
				$mpassword.html('<span class="errIcon"></span>请输入您的新密码！');
				return false;
			}else if($(this).val().length<6){
				$mpassword.removeClass('input_tag1');
				$mpassword.addClass('input_tag2');
				$mpassword.html('<span class="errIcon"></span>密码长度应大于6位！');
				return false;
			}
			else{
				$mpassword.removeClass('input_tag1');
				$mpassword.removeClass('input_tag2');
				$mpassword.html('<span class="rightIcon"></span>');
			}
		});
		
		$rpassword2.focus(function (){
			$(this).addClass('input_focus');
			$mpassword2.removeClass('input_tag2');
			$mpassword2.addClass('input_tag1');
			$mpassword2.html('再次输入您所设置的新密码，以确认密码无误。');
		}).blur(function(){
			$(this).removeClass('input_focus');
			$(this).triggerHandler('keyup');
		}).keyup(function(){
			if($(this).val() == ''){
				$mpassword2.removeClass('input_tag1');
				$mpassword2.addClass('input_tag2');
				$mpassword2.html('<span class="errIcon"></span>请输入确认新密码！');
				return false;
			}
			else if($(this).val() != $rpassword.val()){
				$mpassword2.removeClass('input_tag1');
				$mpassword2.addClass('input_tag2');
				$mpassword2.html('<span class="errIcon"></span>两次输入密码不相同！');
				return false;
			}
			else{
				$mpassword2.removeClass('input_tag1');
				$mpassword2.removeClass('input_tag2');
				$mpassword2.html('<span class="rightIcon"></span>');
			}
		});
		
		$sevePassword.click(function(){
			$("#modifyPassword :input").trigger('keyup');
			var numError = $('#modifyPassword .errIcon').length;
			if(!numError){
				$.ajax({
					type: "POST",
					global: false,// 禁用全局Ajax事件.
					url: _config['domainSite'] + "index.php?g=Member&m=User&a=dopassword",
					data:{oldPassword:$roldpassword.val(), password:$rpassword.val(), password2:$rpassword2.val()},
					dataType: "json",
					success: function(data){
						if(data.status){
							$.tipMessage('密码更新成功，请重新登录！', 0, 2000, 0, function () {
								libs.login();
							});
						}else{
							$.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
							return false;
						}
					}
				});
			}
		});
	},
	retreat: function() {
		$.ajax({
			type: "POST",
			global: false,// 禁用全局Ajax事件.
			url: "user?a=retreat",
			dataType: "text",
			success: function(data){
				if(data==20001){
					user.userNotLogin('您没有登录，无法更新个人设置！');
				}
				else if(data==10001){
						alert('无法修改昵称！ ');
					libs.redirect('user?a=profileModify&i=nickname');
				}
				else{
					alert('修改昵称申请已经提交！ ');
					libs.redirect('user?a=profileModify&i=nickname');	
				}
			}
		});
	}
}