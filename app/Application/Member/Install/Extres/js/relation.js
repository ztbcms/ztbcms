//关系
var relation = {
	init: function() {
		var $cancel = $("#cancel");
		var $followingGroupAdd = $("#followingGroupAdd");
		var $add = $("#add");
		
		//取消分组
		$cancel.click(function() {
			$followingGroupAdd.hide();
		});
		
		//添加分组
		$add.click(function(){
			var dialogObj = $.dialog.get('delGroup');
				if (typeof dialogObj === 'object') {
					dialogObj.close();
				}
			var $fgName = $('#addfgName');
			var uid = $(this).attr('uid');
			if(!/^([^<>'"\/\\])*$/.test($fgName.val())){
				$.tipMessage("名字中不能有 < > \' \" / \\ 等非法字符！", 1, 2000);
				return false;
			}
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite'] + "index.php?g=Member&m=Relation&a=groupadd",
				data:{uid: uid, name: $fgName.val()},
				dataType: "json",
				success: function(data){
					if(data['error']==20001){
						user.userNotLogin('您未登录无法执行此操作！')
					}
					else if(data['error']==20002){
						$.tipMessage("您没有权限添加！", 1, 2000);
					}
					else if(data['error']==10007){
						$.tipMessage("分组名称不能为空！", 1, 2000);
					}
					else if(data['error']==10006){
						$.tipMessage("分组名超过不能超过七个字！", 1, 2000);
					}
					else if(data['error']==100061) {
						$.tipMessage("分组数量不能超过8个！", 1, 2000);
					}
					else if(data['error']==10000) {
						$('#addfgName').val('');
						$('#followingGroupList .list').append('<a class="list_clo" href="'+_config['domainSite']+'index.php?g=member&m=relation&a=following&cid=4&gid='+data['gid']+'">'+data['name']+'</a>');
						$.tipMessage('分组添加成功！', 0, 3000);
					}
					else{
						$.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
					}
				}
			});
		});
	},
	//显示我关注人的分组
	followingGroupSelect: function(fgid, friend_uid, is_quietly) {
		var newfgid = $("input[name='fgid']:checked").val();
		var $currPage = $("#currPage").html();
		$.ajax({
			type: "POST",
			global: false,// 禁用全局Ajax事件.
			url: _config['domainSite'] + "index.php?g=Member&m=Relation&a=groupdesignate",
			data:{friend_uid: friend_uid, oldfgid: fgid, newfgid: newfgid, is_quietly: is_quietly},
			dataType: "json",
			success: function(data){
				if(data['error']==20001){
					user.userNotLogin('您未登录无法执行此操作！');
				}
				else if(data['error']==20002){
					$.tipMessage('无权限操作', 1, 3000);
				}
				else if(data['error']==10000){
					 window.location.reload();
				}
				else{
					$.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
					return false;
				}
			}
		});
	},
	//删除我关注的人
	delFollowing: function(fgid, friend_uid, friend_nickname, showFgid, uid, avatar, class_id, is_quietly, status) {
		var $friend = $('#friend');
		var $fg_name = $("#group_"+friend_uid).html();
		var $currPage = $("#currPage");
		var $fgCount = $('#fgCount_'+fgid);
		var $s_fgCount = $('#s_fgCount_'+fgid);
		var $q_fgCount = $('#q_fgCount_'+fgid);
		var $fgCountAll = $('#fgCountAll');
		var nid = parseInt($("#number").attr("nid"));
		var s_fgCount = 0;
		var q_fgCount = 0;
		var	fgCount = 0;
		
		if(avatar==''){
			if($fgCountAll.html()!=undefined){
				allCount = parseInt($fgCountAll.html().replace(/([\[\]])/g,""));
			}
		}
		$.dialog({
			id:'friendDel1', title:'解除粉丝关系',  lock:true,
			content: '<br/>你确定与 <strong>'+friend_nickname+'</strong> 解除粉丝关系吗？<br/><br/><span style="color: #999999;">提示：解除后你将不再收到他的新鲜事。</span><br/><br/>',
			okValue: '确认',
			ok: function(){
				$.ajax({
					type: "POST",
					global: false,// 禁用全局Ajax事件.
					url: _config['domainSite'] + "index.php?g=Member&m=Relation&a=followingdel",
					data:{friend_uid: friend_uid, fgid: showFgid, currPage: $currPage.html(),fg_name: $fg_name, uid: uid, class_id: class_id},
					dataType: "json",
					success: function(data){
						if(data['error']==20001){
							user.userNotLogin('您未登录无法执行此操作！')
						}
						else if(data['error']==20002){
							$.tipMessage("您没有权限删除！", 1, 2000);
						}
						else if(data['error']==10000){
							if(avatar==''){
								nid = nid-1;
								$("#number").html("该组共有"+nid+"人").attr('nid',""+nid+"");
								$('#followingList').html(data);
								$.tipMessage('您成功解除了与 <strong>'+friend_nickname+'</strong> 的粉丝关系！', 0, 3000);
							}
						}
						else{
							$.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
						}
					}
				});
			},
			cancelValue: '取消',
			cancel:function(){
				
			}
		});
	},
	//修改我关注人的分组名字
	followingGroupModify: function(fgid){
		if(!/^([^<>'"\/\\])*$/.test($('#modifyfgName').val())){
			$.tipMessage("名字中不能有 < > \' \" / \\ 等非法字符！", 1, 2000);
			return false;
		}
		$.ajax({
			type: "POST",
			global: false,// 禁用全局Ajax事件.
			url: _config['domainSite'] + "index.php?g=Member&m=Relation&a=groupeditname",
			data:{'gid': fgid, 'name': $('#modifyfgName').val()},
			dataType: "json",
			success: function(data){
				if(data['error']==20001){
					user.userNotLogin('您未登录无法执行此操作！')
				}
				else if(data['error']==20002){
					$.tipMessage("您没有权限修改！", 1, 2000);	
				}
				else if(data['error']==10007){ 
					$.tipMessage("分组名称不能为空！", 1, 2000);
				}
				else if(data['error']==10006){
					$.tipMessage("分组名超过不能超过七个字！", 1, 2000);
				}
				else if(data['error']==10000){
					//$.tipMessage('分组名称修改成功！', 0, 3000);
					location.replace(location.href);
				}
				else{
					$.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
				}
			}
		});
	},
	change: function(fgid, fgName){
		$.dialog({
					id: 'login',
					title: '编辑分组',
					content: '<div id="editGroup">\
								<input type="text" maxlength="7" style="width:121px;" class="input_normal" id="modifyfgName" value="'+fgName+'">\
								<span class="button-main">\
									<span>\
										<button type="button" onclick="relation.followingGroupModify('+fgid+');" id="edit">编辑</button>\
									</span>\
								</span>\
							</div>',
					lock: true
				});
	
	},
	
	
	//给我关注的人分组
	group:function(fgid, friend_uid, nickname){
		$.ajax({
			type: "GET",
			global: false,// 禁用全局Ajax事件.
			url: _config['domainSite'] + "index.php?g=Member&m=Relation&a=groupdesignate",
			data:{fgid: fgid, nickname: nickname, friend_uid: friend_uid},
			dataType: "text",
			success: function(data){
				if(data==20001){
					user.userNotLogin('您未登录无法执行此操作！');
				}
				else if(data==20002){
					$.tipMessage("您没有权限添加！", 1, 2000);
				}
				else{
					$.dialog({
						id: "delGroup2",
						title: '修改分组',
						content: data,
						okValue: '确认修改',
						ok: function(){
							var  is_quietly = $("#is_quietly:checked").val();
							relation.followingGroupSelect(fgid, friend_uid, is_quietly);
						},
						cancelValue: '取消',
						cancel:function(){
						var dialogObj = $.dialog.get('delGroup');
							if (typeof dialogObj === 'object') {
								dialogObj.close();
							}
							//location.href=location.href;
						}
					});
				}
			}
		});
	}
};