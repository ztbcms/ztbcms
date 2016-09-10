var skinLib = {
	//预览及更换主题
	skinUpdate: function(){
		var skinPath="";
		$(".ornament-content").click(function(){//点击图片更换主题
			skinPath = $(this).attr("skinPath");
			$("#skin").attr("href", _config['domainStatic']+"theme/"+ skinPath +"/style.css");
		}).hover(function(){//显示点击预览文字
			var id = $(this).attr("skinId");
			$("#on"+id).show();
		},function(){//隐藏点击预览文字
			$(".click-preview").hide();
		});
		$("#change").click(function(){//保存主题
			var uid = $(this).attr("uid");//用户uid
			var BskinPath = $(this).attr("BskinPath");//用户原来的皮肤主题编号
			var showType = 1;//保存主题
			if(skinPath != ""){
				$.ajax({
					type: "POST",
					global: false,// 禁用全局Ajax事件.
					url: _config['domainSite']+'index.php?g=Member&m=Skin&a=setup',
					data:{'skinPath': skinPath, 'BskinPath': BskinPath, 'showType': showType},//皮肤id
					dataType: "json",
					success: function(data){
						if(data['error']==10000){
							$.tipMessage('主题更新成功！', 1, 2000, 0, function(){location.href = location.href;});
							return false;
						}
						else if(data['error']==20001){
							$.tipMessage('您还没有登录，登录就能换肤啦!', 1, 3000);
							return false;
						}						
						else if(data['error']==10004){
							$.tipMessage('对不起，皮肤已被删除', 1, 3000, 0, function(){location.href = location.href;});
							return false;
						}
						else {
							$.tipMessage(data['info'], 1, 3000, 0);
							return false;
						}
					},
					error: function() {
						alert('数据执行意外错误！');
					}
				});
				return false;	
			}else{
				$.tipMessage('获取参数错误！', 1, 2000, 0, function(){location.href = location.href;});
				return false;
			}
		});
		$("#default").click(function(){//恢复默认主题
			var uid = $(this).attr("uid");
			var BskinPath = $(this).attr("BskinPath");//用户原来的皮肤主题编号
			var skinPath = "";
			var showType = 0;//恢复默认主题
			$.ajax({
				type: "POST",
				global: false,// 禁用全局Ajax事件.
				url: _config['domainSite']+'index.php?g=Member&m=Skin&a=setup',
				data:{'skinPath': skinPath, 'BskinPath': BskinPath, 'showType': showType},
				dataType: "json",
				success: function(data){
					if(data['error']==10000){
						$.tipMessage('恢复默认主题成功！', 1, 2000, 0, function(){location.href = location.href;});
						return false;
					}
					else if(data['error']==20001){
						$.tipMessage('您还没有登录，登录就能换肤啦!', 1, 3000);
						return false;
					}
					else {
						$.tipMessage(data['info'], 1, 3000, 0);
						return false;
					}
				},
				error: function() {
					alert('数据执行意外错误！');
				}
			});
			return false;	
		});
	}
}
