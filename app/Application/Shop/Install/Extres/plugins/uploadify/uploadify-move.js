	//上传图片框 的移动 JS
	//移动
	//移动代码开始
	var _move = false;
	var ObjT = "#MainTit";
	var ObjW = "#Wrap";

	//鼠标离控件左上角的相对位置
	var _x,_y,_top,_left;

	//初始化窗口位置
	_top  = parseInt($(window.parent.window).height()/2)-208 + $(window.parent.document).scrollTop();
	_left = parseInt($(window.parent.window).width()/2)-245;
	$(ObjW).css({"top":_top,"left":_left});
	$(ObjW).css({"display":"block"});

	//浏览器窗口发生变化时窗口位置
	$(window).resize(function(){
		_top  = parseInt($(window.parent.window).height()/2)-208 + $(window.parent.document).scrollTop();
		_left = parseInt($(window.parent.window).width()/2)-245;
		$(ObjW).css({"top":_top,"left":_left});
	});

	//鼠标按下时允许进行移动操作
	$(ObjT).mousedown(function(e){
		_move = true;
		_x = e.pageX - parseInt($(ObjW).css("left"));
		_y = e.pageY - parseInt($(ObjW).css("top"));
	});

	$(document).mousemove(function(e){
		if(_move){

			//移动时根据鼠标位置计算控件左上角的绝对位置
			var x = e.pageX - _x;
			var y = e.pageY - _y;
	
			//控件新位置
			$(ObjW).css({top:y,left:x});
		}
	}).mouseup(function(){
		_move = false;
	});