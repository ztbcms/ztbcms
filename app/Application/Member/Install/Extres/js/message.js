var messageLib = {
	//删除短消息type:0,消息列表;1,新消息;2,内容页
	msgDelInit:function(){
		var $currPage = $('#currPage');//当前页码
		$(".del").click(function(){
			var type = $(this).attr("type");
			var msgid = $(this).attr("msgid");//短信id
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Msg&a=msgdel",
				data:{
					'type': type, 
					'msgid': msgid
				},
				dataType: "json",
				success: function(data){
					if(data['error']==20001){
						user.userNotLogin('您未登录无法执行此操作！');
					} else if(data['error']==20002) {
						$.tipMessage("对不起，你无权删除", 1, 3000, 0, function(){location.href = location.href;});
					} else if(data['error'] == 10000){
						$.tipMessage('短信删除成功！', 1, 2000,0,function(){
							location.href = location.href;
						});
					} else {
						$.tipMessage(data['info'], 1, 3000);
					}
				}
			});
		});
	},
	
	//删除全部短消息
	msgAllDelInit:function(){
		var fromUid = $(".delAll").attr("fromUid");//发送者id
		var type = "";
		var toUid = "";
		$(".delAll").click(function(){
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Msg&a=msgdelall",
				dataType: "json",
				success: function(data){
					if(data['error']==20001){
						user.userNotLogin('您未登录无法执行此操作！');
						return false;
					} else if(data['error']==20002){
						$.tipMessage('对不起，您没有操作权限', 1, 3000);
						return false;
					} else if(data['error']==10000){
						$.tipMessage('短信息清空成功！', 1, 2000,0,function(){
							location.href = location.href;
						});
					} else {
						$.tipMessage(data['info'], 1, 3000);
						return false;
					}
				}
			});
		});
	},
	
	//忽略全部新消息
	msgIgnoreInit: function(){
		var uid = $(".ignore").attr("uid");//用户uid
		$(".ignore").click(function(){
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: "/message?a=doMsgIgnore",
				data:{'uid': uid},
				dataType: "text",
				success: function(data){
					if(data==20001){
						libs.userNotLogin('您未登录无法执行此操作！');
					}
					else if(data==20002){
						$.tipMessage('对不起，您没有操作权限', 1, 3000);
						return false;
					}
					else{
						$('#msg').html("<div class='nothing'>没有未读私信!</div>");
					}
				}
			});
		});
	},
	//回复站内信
	msgAddInit: function(){
		var $fnote = $('#fnote');//内容
		var uid = $(".reMsg").attr('touid');//接受者id
		var answerid = $(".reMsg").attr('answerid');//会话id
		$fnote.elastic({maxHeight:130});
		$fnote.emotEditor({emot:true, newLine:true});
		$(".reMsg").click(function(){
			var validCharLength = $fnote.emotEditor("validCharLength");
			if(validCharLength<1 || $fnote.emotEditor("content")==" "){
				$.tipMessage('您什么都没写啊！', 1, 2000);
				$fnote.emotEditor("focus");
				return false;
			}
			if($fnote.html().length>1500){
				$.tipMessage('您写的太多了，我装不下了！', 1, 2500);
				$fnote.emotEditor("focus");
				return false;
			}
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Msg&a=msgadd",
				data:{
				     'uid': uid, 
					 'answerid':answerid,
					 'note': $fnote.emotEditor("content")
				},
				dataType: "json",
				success: function(data){
					if (data['error'] == 20001) {
					     user.userNotLogin('您未登录无法执行此操作！');
					} else if (data['error'] == 10013) {
                         $.tipMessage('您不能给自己发私信！', 1, 3000);
                         return false;
                     } else if (data['error'] == 10007) {
					     $.tipMessage('请先写点什么吧！', 1, 3000);
						 return false;
                     } else if (data['error'] == 10004) {
                         $.tipMessage('对不起，用户不存在！', 1, 3000);
						 return false;
                     } else if (data['error'] == 20002) {
                         $.tipMessage('对不起，你的用户等级不够，无法发送私信！', 1, 2500);
						 return false;
                     } else if (data['error'] == 10000)  {
                         $.tipMessage('私信已经回复给对方', 0, 2000,0, function(){
						     location.href=location.href;
						 });
                     } else {
                         $.tipMessage(data['info'], 1, 3000);
                         return false;
                     }
				}
			});
		});
	},
	
	//删除单条消息
	reMsgOneDelInit: function(){
		//type:0,消息列表;1,新消息;2,内容页
		$(".del").click(function(){
			var msgid = $(this).attr("msgid");//短信id
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Msg&a=msgdel",
				data:{
					'msgid': msgid
				},
				dataType: "json",
				success: function(data){
					if(data['error']==20001){
						user.userNotLogin('您未登录无法执行此操作！');
					} else if(data['error']==20002) {
						$.tipMessage("对不起，你无权删除", 1, 3000, 0, function(){location.href = location.href;});
					} else if(data['error'] == 10000){
						$.tipMessage('短信删除成功！', 1, 2000,0,function(){
							location.href = _config['domainSite'] + 'index.php?g=Member&m=Msg&a=msg';
						});
					} else {
						$.tipMessage(data['info'], 1, 3000);
					}
				}
			});
		});
	},
	
	//删除本对话
	reMsgDelInit: function(){
		//type:0,消息列表;1,新消息;2,内容页
		$(".reMessage").click(function(){
			var msgid = $(this).attr("msgid");//短信id
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Msg&a=msgdel",
				data:{
					'msgid': msgid
				},
				dataType: "json",
				success: function(data){
					if(data['error']==20001){
						user.userNotLogin('您未登录无法执行此操作！');
					} else if(data['error']==20002) {
						$.tipMessage("对不起，你无权删除", 1, 3000, 0, function(){location.href = location.href;});
					} else if(data['error'] == 10000){
						$.tipMessage('短信删除成功！', 1, 2000,0,function(){
							location.href = _config['domainSite'] + 'index.php?g=Member&m=Msg&a=msg';
						});
					} else {
						$.tipMessage(data['info'], 1, 3000);
					}
				}
			});
		});
	},
	
	//删除通知
	notificationDelInit:function(){
		$(".ndel").click(function(){
			var nid = $(this).attr('id');//通知id
			var parent = $(this).parent("li");
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Message&a=noticeDel",
				data:{'nid': nid},
				dataType: "json",
				success: function(data){
					if(data['error']==20001){
						libs.userNotLogin('您未登录无法执行此操作！');
					}
					else if(data['error']==10004){
						$.tipMessage('通知不存在', 1, 1500, 0);
					}
					else if(data['error']==20002){
						$.tipMessage("对不起，你无权删除", 1, 3000, 0, function(){location.href = location.href;});
					}
					else if(data['error']==10000){
						parent.remove();
						$.tipMessage("删除成功！", 1, 2000, 0, function(){location.href = location.href;});
					}
					else {
						$.tipMessage(data['info'], 2, 3000);
					}
				},
				error: function(){
					$.tipMessage('数据执行意外错误！', 2, 3000);
				}
			});
		});
	},
	
	//删除全部通知
	notificationAllDel:function(uid, type, month, id){
			$.dialog({
				id:'delAll', title:false, border:false, follow: $("#"+id)[0], content:'确认删除全部通知么？',
				okValue: '确认',
				ok: function(){
					$.ajax({
						type: "POST",
						global: false,// 禁用全局Ajax事件.
						url: _config['domainSite'] + "index.php?g=Member&m=Message&a=notificationAllDel",
						data:{'uid':uid, 'type':type, month: month},
						dataType: "json",
						success: function(data){
							if(data['error']==20001){
								libs.userNotLogin('您未登录无法执行此操作！');
							}
							else if(data['error']==20002){
								$.tipMessage("对不起，你无权删除", 1, 3000, 0, function(){location.href = location.href;});
							}
							else if(data['error']==10000){
								location.href = location.href;
							}
							else{
								$.tipMessage(data['info'], 1, 3000, 0, function(){location.href = location.href;});
							}
						}
					});
				},
				cancelValue: '取消',
				cancel:function(){
							
				}
			});
	}
}
