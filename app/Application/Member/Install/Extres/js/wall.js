//留言
var wallLib = {
	wallTimer: 0,
	commentTimer: 0,	
	//添加新留言init
	wallAddInit: function(uid){
		var $wallContent = $("#wallContent");
		var $currPage = $('#currPage');
		var $sW_message = $('#sW_message');
			var validCharLength = $wallContent.emotEditor("validCharLength");
			if(validCharLength<1){
				$sW_message.html('请输入您的留言内容！');
				
				clearTimeout(wallLib.wallTimer);
				wallLib.wallTimer = setTimeout(function(){$sW_message.html('');}, 2000);
				$wallContent.emotEditor("focus");
				return;
			}
			$("#wallSubmit").attr('disabled', 'disabled');
			setTimeout(function() {
				$("#wallSubmit").removeAttr('disabled');
				}, 5000);
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Wall&a=walladd",
				data:{
					'content': $wallContent.emotEditor("content"),
					'uid': uid
				},
				dataType: "json",
				success: function(data){
					if(data['error']==10007){
						$sW_message.html('请输入您的留言内容！');
						$wallContent.emotEditor("focus");
						clearTimeout(wallLib.wallTimer);
						wallLib.wallTimer = setTimeout(function(){$sW_message.html('');}, 2000);
					}
					else if(data['error']==10006){
						$sW_message.html('留言内容不能超过300个字！');
						$wallContent.emotEditor("focus");
						clearTimeout(wallLib.wallTimer);
						wallLib.wallTimer = setTimeout(function(){$sW_message.html('');}, 2000);
					}
					else if(data['error']==20001){
						user.userNotLogin('您未登录无法执行此操作！');
					}
					else if(data['error']==10002){
						$sW_message.html('您操作的太频繁，请稍后再试！');
						$wallContent.emotEditor("focus");
						clearTimeout(wallLib.wallTimer);
						wallLib.wallTimer = setTimeout(function(){$sW_message.html('');}, 8000);
					}else if(data['error'] == 10000){
						$.tipMessage("留言成功", 0, 2000, 0, function(){location.href = location.href;});
					}else if(data['error'] == 100061){
						$sW_message.html('留言数量超过限制！');
						$wallContent.emotEditor("focus");
						clearTimeout(wallLib.wallTimer);
						wallLib.wallTimer = setTimeout(function(){$sW_message.html('');}, 2000);
					}else if(data['error'] == 20015){
						$sW_message.html('您的账号异常，已被锁定！');
						$wallContent.emotEditor("focus");
						clearTimeout(wallLib.wallTimer);
						wallLib.wallTimer = setTimeout(function(){$sW_message.html('');}, 2000);
					}else if(data['error'] == 10005){
						$sW_message.html('一级以上用户才可发表留言！');
						clearTimeout(wallLib.wallTimer);
						wallLib.wallTimer = setTimeout(function(){$sW_message.html('');}, 2000);
					}
					else{
						$sW_message.html(data['info']);
					}
				}
			});
	},
	//留言和回复删除事件
	doDelWall: function (uid, wid, cid, showType){
		var follow, content, $handleObj
			,$currPage = $("#currPage")
			,dialogObj = $.dialog.get('delWall');
		if(cid){
			follow = $('#del-c'+cid)[0];
			content = '确认删除这条回复么？';
			$handleObj = $('#wallComment'+wid);
		}
		else{
			cid = 0;
			follow = $('#del-w'+wid)[0];
			content = '确认删除这条留言么？';
			$handleObj = $('#wall_content');
		}

		if (typeof dialogObj === 'object') {
			dialogObj.close();
		}
		
		$.dialog({
			id:'delWall', title:false, border:false, follow:follow, content:content,
			okValue: '确认',
			ok: function(){
				$.ajax({
					type: "POST",
					global: false,// 禁用全局Ajax事件.
					url: _config['domainSite'] + "index.php?g=Member&m=Wall&a=walldel",
					data:{
					    wid: wid
					},
					dataType: "json",
					success: function(data){
						if(data['error']==20001){
							user.userNotLogin('您未登录无法执行此操作！');
							return false;
						}
						else if(data['error']==20002){
							$.tipMessage('您没有权限删除！', 1, 3000);
							return false;	
						}else if(data['error']==10004) {
						  $.tipMessage("留言已被删除", 1, 3000, 0, function(){location.href = location.href;});
						}else if(data['error']==10000) {
							$.tipMessage("留言删除成功", 0, 2000, 0, function(){location.href = location.href;});
						} else {
						    $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
						}
					},
					error: function() {
						alert("数据执行错误！");
						return false;
					}
				});
			},
			cancelValue: '取消',
			cancel:function(){
			}
		});
	},	
	//留言回复按钮事件
	confirmWall:function(wid, uid){
		//return;
		var $content = $("#wallCommentInput"+wid);
		var replayUser = $("#replayUser_"+wid).html();
		var validCharLength = $content.emotEditor("validCharLength");
		if(validCharLength<1){
			$("#wCI_message"+wid).html('请输入您的留言内容！');
			$content.emotEditor("focus");
			clearTimeout(wallLib.commentTimer);
			wallLib.commentTimer = setTimeout(function(){$("#wCI_message"+wid).html('');}, 2000);
			return;
		}
		if(replayUser != ''){
			replayUser = replayUser.substring(2);
		}
		$("#wallcontSubmit").attr('disabled', 'disabled');
			setTimeout(function() {
				$("#wallcontSubmit").removeAttr('disabled');
				}, 5000);
		$.ajax({
			type: "POST",
			global: false,// 禁用全局Ajax事件.
			url: _config['domainSite'] + "index.php?g=Member&m=Wall&a=walladd",
			data:{
				parentid: wid, 
				content: $content.emotEditor("content"), 
				replayUser: replayUser, 
				uid: uid
			},
			dataType: "json",
			success: function(data){
				if(data['error']==20001){
					user.userNotLogin('您未登录无法执行此操作！');
					return false;
				}
				else if(data['error']==10004){
					$.tipMessage("留言已被删除", 1, 3000, 0, function(){location.href = location.href;});
				}
				else if(data['error']==10012) {
					$("#wCI_message"+wid).html("您操作的太频繁，请稍后再试!");
					clearTimeout(wallLib.commentTimer);
					wallLib.commentTimer = setTimeout(function(){$("#wCI_message"+wid).html('');}, 5000);
					return false;
				}
				else if(data['error']==10006){
					$("#wCI_message"+wid).html("留言回复内容超过最大限制！");
					clearTimeout(wallLib.commentTimer);
					wallLib.commentTimer = setTimeout(function(){$("#wCI_message"+wid).html('');}, 3000);
					return false;
				}
				else if(data['error']==10007){
					$("#wCI_message"+wid).html("请输入您的回复内容！");
					$content.emotEditor("focus");
					clearTimeout(wallLib.commentTimer);
					wallLib.commentTimer = setTimeout(function(){$("#wCI_message"+wid).html('');}, 3000);
					return false;
				}else if(data['error'] == 20015){
					$("#wCI_message"+wid).html("您的账号异常，已被锁定！");
					clearTimeout(wallLib.commentTimer);
					wallLib.commentTimer = setTimeout(function(){$("#wCI_message"+wid).html('');}, 3000);
					return false;
				
				}else if(data['error'] == 10005){
					$("#wCI_message"+wid).html('一级以上用户才可发表留言回复！');
					clearTimeout(wallLib.commentTimer);
					wallLib.commentTimer = setTimeout(function(){$("#wCI_message"+wid).html('');}, 3000);
					return false;
				}else if(data['error'] == 10000){
					$.tipMessage("留言回复成功", 0, 2000, 0, function(){location.href = location.href;});
				}else{
					$("#wCI_message"+wid).html(data['info']);
				}
			},
			error: function() {
				alert("数据执行错误！");
				return false;
			}
		});
	},
	//显示留言和评论回复框事件
	replyWall:function(wid, cid, toUid, toNickname, authorUid){
		var $wallCommentInput = $("#wallCommentInput"+wid);
		if(cid) {
			if(authorUid!=toUid){
				$("#replayUser_"+wid).html("回复@"+toNickname+"["+toUid+"]").show();
				$("#replayUserDel_"+wid).show();
			}
			else{
				$("#replayUser_"+wid).empty().hide();
				$("#replayUserDel_"+wid).hide();
			}
		}
		else{
			$("#replayUser_"+wid).empty().hide();
			$("#replayUserDel_"+wid).hide();
		}
		$('.wallCommentInputBox').hide();
		$("#wallCommentInputBox"+wid).show();
		$wallCommentInput.emotEditor({emot:true, focus:true, newLine:true});
		return false;
	},
	//取消回复某人事件
	delReplayUser:function(wid){
		$("#replayUser_"+wid).empty().hide();
		$("#replayUserDel_"+wid).hide();
		return false;
	},
	//取消回复事件
	cancelWall:function(wid){
		$("#wallCommentInputBox"+wid).hide();
	},
	
	moreWall:function(uid, currPage){
		$.ajax({
			type: "POST",
			global: false,// 禁用全局Ajax事件.
			url: "/wall?a=fetchSpaceWall",
			data:{uid: uid, currPage: currPage},
			dataType: "text",
			success: function(data){
				$("#wall_content").html(data);
			}
		});
	
	}
}		