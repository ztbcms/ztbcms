var imgLoaded = {
    uid: 0,
    page: 1,
	load:false,
    init: function (obj, uid, page) {
	    imgLoaded.page = page;
		imgLoaded.uid = uid;
        $(obj).waterfall({
			columnCount:4,
			columnWidth:224,
			isResizable:false, // 自适应浏览器宽度, 默认false
			isAnimated:true, // 元素动画, 默认false
			Duration:500,// 动画时间
			Easing:'swing',// 动画效果, 配合 jQuery Easing Plugin 使用
			endFn:function(){
				imgLoaded.load = true;
			}
		});
		$(window).scroll(function () {
			if(imgLoaded.load == false){
				return false;
			}
		   var winH = $(window).height();
		   var pageH = $(document).height(); //页面总高度   
		   var scrollT = $(window).scrollTop(); //滚动条top   
		   var scrollWeizi = (pageH-winH-scrollT)/winH;
		   if(scrollWeizi<0.03){
				imgLoaded.page ++;
				imgLoaded.load = false;
				$.ajax({
					type: "GET",
					dataType:'json',
					//async:false,
					url: _config['domainSite']+'index.php?g=Member&m=Home&a=album&userid='+imgLoaded.uid+'&page='+imgLoaded.page,
					success: function(data){
						if( data.totalpages < imgLoaded.page ){
							imgLoaded.load = false;
							$.tipMessage('已经没有新的照片！', 1, 2000);
							return ;
						}
						var html = '';
						$.each(data.data,function(i,v){
							html += '<li class="imageBlock masonry-brick" >\
											  <div class="box" onmouseover="albumLib.showInit('+v.id+');" onmouseout="albumLib.hideInit();">\
											  <div class="act">\
												  <div class="imgpraise" style="display: none; " id="imgpraise'+v.id+'"><a class="praiseImg" id="praise" onclick="albumLib.doImagePraise('+imgLoaded.uid+','+v.id+', '+v.love+', '+v.plsum+');" href="javascript:;">喜欢</a></div>\
											  </div>\
											  <a href="'+v.url+'" name="'+v.filename+'" target="_blank"><img src="'+v.thumb+'" width="'+v.thumb_width+'" height="'+v.thumb_height+'"></a>\
											  <div class="info">\
												  <span id="praiseCount'+v.id+'">'+v.love+'人喜欢</span>\
												  <span id="replyNum'+v.id+'" class="last">'+v.plsum+'人评论</span>\
											  </div>\
											  <div class="end_line"></div>\
											  </div>\
										  </li>';
						});
						$(obj).append(html).waterfall({
                            isAnimated:true,
							endFn:function(){
								imgLoaded.load = true;
							}
						});
					}
				});
		   }
		});   
    }
}

var albumLib = {
    //空间照片列表
    spaceImageInit: function (imgData, uid) {
        imgData = imgData;
        imgUid = uid;
        $container = $('#spaceAlbumList');
        sTimer = "";
        imgDataLenth = imgData.length;
        iCount = new Array(); //每次处理的图片计数数组
        iTotalCount = 0; //正在处理的图片总计数
        imgLoadTotalCount = 0;
        loadMoreCount = 0; //loadMore载入次数统计

        if ($container.length > 0) {
            $container.html('').masonry({
                itemSelector: '.imageBlock'
            });
            albumLib.loadMore(); //预载入一次图片
            $(window).bind("scroll", albumLib.scrollHandler); //绑定拖动滚动条事件
        }
    },

    loadMore: function () {
        var str = '';
        var startId = iTotalCount;
        var endId = 0;
        iCount[loadMoreCount] = 0;

        if (startId + 20 < imgDataLenth) {
            endId = startId + 20;
        } else {
            endId = imgDataLenth;
        }
        if (startId != imgDataLenth) {
            for (var i = startId; i < endId; i++) {
                str += '<li class="imageBlock imageBlock_' + loadMoreCount + '"><div class="box" onmouseover="albumLib.showInit(' + imgData[i]['pid'] + ');" onmouseout="albumLib.hideInit();"><div class="act"><div class="imgpraise" style="display:none;" id="imgpraise' + imgData[i]['pid'] + '"><a class="praiseImg" id="praise" onClick="albumLib.doImagePraise(' + imgUid + ',' + imgData[i]['pid'] + ', ' + imgData[i]['praiseNum'] + ', ' + imgData[i]['replyNum'] + ');" href="javascript:;">喜欢</a></div></div><a href="/' + imgUid + '/album/' + imgData[i]['pid'] + '.html" name="' + imgData[i]['pid'] + '" target="_blank"><img onload="albumLib.imgLoaded(\'imageBlock_' + loadMoreCount + '\', ' + loadMoreCount + ')" onerror="albumLib.imgLoaded(\'imageBlock_' + loadMoreCount + '\', ' + loadMoreCount + ')" src="' + _config['domainUpload'] + imgData[i]['src'] + '.thumb_w200.jpg" width="200" /></a><div class="info"><span id="praiseCount' + imgData[i]['pid'] + '">' + imgData[i]['praiseNum'] + '人喜欢</span><span class="last">' + imgData[i]['replyNum'] + '人评论</span></div><div class="end_line"></div></div></li>';
                ++iTotalCount;
            }
            $container.append(str);
            albumLib.scrollHandler(); //再次检测图片是否已经显示满屏
        } else {
            $(window).unbind("scroll", albumLib.scrollHandler); //清除绑定事件
        }
        ++loadMoreCount;
    },

    scrollHandler: function () {
        clearTimeout(sTimer);
        sTimer = setTimeout(function () {
            var h = $(window).height(),
                t = $(document).scrollTop();
            if (t + h + 500 >= $container.offset().top + $container.height()) {
                albumLib.loadMore();
            }
        }, 50);
    },
    imgLoaded: function (obj, id) {
        ++iCount[id];
        ++imgLoadTotalCount;
        if (iCount[id] % 20 == 0 || imgLoadTotalCount == imgDataLenth) {
            $("." + obj).fadeIn();
            $container.masonry("reload");
            if (imgLoadTotalCount == imgDataLenth) {
                $(window).unbind("scroll", albumLib.scrollHandler); //清除绑定事件
            }
        }
    },

    //批量删除我的图片
    imagesBatchDelInit: function () {
        $(".page #selectAll").click(function () { //全选
            var dialogObj = $.dialog.get('delAlbum');
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
            $('#list :checkbox').each(function () {
                $(this).attr('checked', 'checked');
            });
        });

        $(".page #selectOther").click(function () { //反选
            var dialogObj = $.dialog.get('delAlbum');
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
            $('#list :checkbox').each(function () {
                if ($(this).attr('checked')) {
                    $(this).removeAttr('checked');
                } else {
                    $(this).attr('checked', 'checked');
                }
            });
        });
        $('#list input').click(function () {
            var dialogObj = $.dialog.get('delAlbum');
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
        });

        $('.page #delButton').click(function () {
            var pidArr = new Array(); //获取选中图片id
            var i = 0;
            $('#list input:checked').each(function () {
                pidArr[i] = $(this).attr('pid');
                i++;
            });
            if (pidArr.length <= 0) {
                alert('请选择您要删除的图片！ ');
                return false;
            }
            $.dialog({
                id: 'delAlbum',
                title: false,
                border: false,
                follow: $("#delButton")[0],
                content: '确认删除这些照片么？',
                okValue: '确认',
                ok: function () {
                    $.ajax({
                        type: "POST",
                        global: false, // 禁用全局Ajax事件.
                        url: _config['domainSite'] + "index.php?g=Member&m=Album&a=imagedel",
                        data: {
                            'pid': pidArr
                        },
                        dataType: "json",
                        success: function (data) {
                            if (data['error'] == 20001) {
                                libs.userNotLogin('您未登录无法执行此操作！');
                            } else if (data['error'] == 10000) {
                                location.href = location.href;
                            } else if (data['error'] == 20002) {
                                $.tipMessage('您没有权限！', 1, 2000);
                            } else if (data['error'] == 30000) {
                                $.tipMessage('图片不存在或已删除！', 1, 2000, 0, function () {
                                    location.href = location.href;
                                });
                            }else{
                                $.tipMessage(data['info'], 1, 2000);
                            }
                        },
                        error: function () {
                            alert('数据执行意外错误！');
                        }
                    });
                },
                cancelValue: '取消',
                cancel: function () {

                }
            });
            return false;
        });
    },

    //删除我的照片
    imageDelInit: function () {
        $(".delete").click(function () {
            var pidArr = new Array();
            var showType = $('#showType').val(); //删除类型（默认个人中心删除，1为空间删除）
            var uid = $(".delete").attr("uid");
            var dialogObj = $.dialog.get('delAlbumComment');
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
            pidArr[0] = $(this).attr('pid');
            $.dialog({
                id: 'delAlbumComment',
                title: false,
                border: false,
                follow: $(this)[0],
                content: '确认删除这张照片么？',
                okValue: '确认',
                ok: function () {
                    $.ajax({
                        type: "POST",
                        global: false, // 禁用全局Ajax事件.
                        url: _config['domainSite'] + "index.php?g=Member&m=Album&a=imagedel",
                        data: {
                            'pid': pidArr
                        },
                        dataType: "json",
                        success: function (data) {
                            if (data['error'] == 20001) {
                                libs.userNotLogin('您未登录无法执行此操作！');
                            } else if (data['error'] == 20002) {
                                $.tipMessage('您没有权限！', 1, 2000);
                            } else if (data['error'] == 10005) {
                                $.tipMessage('本次操作失败了，请稍后再试！', 1, 2000);
                            } else if (data['error'] == 10012) {
                                $.tipMessage('本次操作失败了，请稍后重试', 1, 2000);
                            } else if (data['error'] == 10000) {
                                if (showType == 1) {
                                    location.href = '/' + uid + '/album/1/';
                                } else {
                                    location.href = location.href;
                                }
                            }else{
                                $.tipMessage(data['info'], 1, 2000);
                            }
                        },
                        error: function () {
                            alert('数据执行意外错误！');
                        }
                    });
                },
                cancelValue: '取消',
                cancel: function () {

                }
            });
        });
    },

    //上传照片
    imageAddInit: function () {
        //检测上传图片控件
        var hasFlash = true;
        if (browser.ie) {
            try {
                var objFlash = new ActiveXObject("ShockwaveFlash.ShockwaveFlash");
            } catch (e) {
                hasFlash = false;
            }
        } else {
            if (!navigator.plugins["Shockwave Flash"]) {
                hasFlash = false;
            }
        }
        if (!hasFlash) {
            $('#upButton').html('您未安装FLASH控件，无法上传图片！请安装FLASH控件后再试。');
        }
    },

    //照片排序
    imageSortInit: function () {
        $(document).ready(
            function () {
                $('#imageSort').sortable({
                    tolerance: 'pointer'
                });
                $("#imageSort").disableSelection();
            }
        );
        $("#saveButton").click(function () {
            var idArr = new Array();
            var i = 0;
            $(".imageSort .avatar").each(function () {
                idArr[i] = $(this).attr('id');
                i++;
                uid = $(this).attr('uid');
            });
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Album&a=listorder",
                data: {
                    'idArr': idArr
                },
                dataType: "json",
                success: function (data) {
                    if (data['error'] == 20001) {
                        libs.userNotLogin('您需要先登录才能进行删除操作！');
                    } else if (data['error'] == 10000) {
                        $.tipMessage('照片排列结果已更新！', 0, 2000);
                    } else {
                        $.tipMessage(data['info'], 1, 2000);
                    }
                },
                error: function () {
                    alert('数据执行意外错误！');
                }
            });
        });
    },

    //设置空间首页图片
    imageSpaceSortInit: function () {
        var imgLength = 0;
        $(document).ready(function () {
            var $imageSort1 = ('#imageSort1'); //图片堆图片
            var $imageSort2 = ('#imageSort2'); //空间显示图片
            var imgLength = $(".imgfile", $imageSort1).length; //图片数量
            $('#imageSort1, #imageSort2').sortable({
                opacity: 0.5,
                tolerance: 'pointer',
                connectWith: '.sortable'
            }).disableSelection();

            $('#imageSort1').bind('sortover', function (event, ui) { //图片堆拖到空间显示图片
                imgLength = $(".imgfile", $imageSort1).length;
                if (imgLength >= 8) {
                    $("img:last", $imageSort1).prependTo("#imageSort2");
                }
            });
            $('#imageSort2').bind('sortover', function (event, ui) { //空间显示图片拖到图片堆
                imgLength = $(".imgfile", $imageSort1).length;
                if (imgLength <= 7) {
                    $("img:first", $imageSort2).appendTo($imageSort1);
                }
            });
        });

        $("#saveButton").click(function () {
            var idArr = new Array();
            var i = 0;
            $(".imageSort .imgfile").each(function () {
                idArr[i] = $(this).attr('id');
                i++;
            });
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Album&a=homeshow",
                data: {
                    'idArr': idArr
                },
                dataType: "json",
                success: function (data) {
                    if (data['error'] == 20001) {
                        libs.userNotLogin('您需要先登录才能进行删除操作！');
                    } else if (data['error'] == 10000) {
                        $.tipMessage('照片排列结果已更新！', 0, 2000);
                    } else {
                        $.tipMessage(data['info'], 1, 2000);
                    }
                },
                error: function () {
                    alert('数据执行意外错误！');
                }
            });
        });
    },

    //更新图片说明
    imageNameModifyInit: function () {
        var text = $(".imageShow #imageNameContent");
        $(".imageShow .explain").click(function () {
            var nameInfo = $(".nameInfo").attr("nameInfo");
            $(".imageShow #imageNameInputBox").show(); //文本框
            $(".imageShow #imageNameContent").val(nameInfo).focus();
        })
        $(".imageShow #cencel").click(function () {
            $(".imageShow #imageNameInputBox").hide();
        })
        $(".imageShow .sends").click(function () {
            if (text.val().length < 32) {
                var pid = $(".sends").attr("pid");
                var uid = $(".sends").attr("uid");
                $.ajax({
                    type: "POST",
                    global: false, // 禁用全局Ajax事件.
                    url: _config['domainSite'] + "index.php?g=Member&m=Album&a=imageremark",
                    data: {
                        'pid': pid,
                        'uid': uid,
                        'remark': text.val()
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data['error'] == 20001) {
                            user.userNotLogin('您需要先登录才能进行删除操作！');
                        } else if (data['error'] == 20002) {
                            $.tipMessage('您没有权限！', 1, 2000);
                        } else if (data['error'] == 30000) {
                            $.tipMessage('图片不存在或已删除！', 1, 2000, 0, function () {
                                location.href = location.href;
                            });
                        } else if (data['error'] == 10005) {
                            $.tipMessage('本次操作失败了，请稍后再试！', 1, 2000);
                        } else if (data['error'] == 10000)  {
                            if (data['remark'] != "") {
                                $(".imageShow #nameInfo").html("<span class='lquotes'></span>" + data['remark'] + "<span class='rquotes'></span>");
                                $(".nameInfo").attr("nameInfo", data['remark']);
                            } else {
                                $(".imageShow #nameInfo").html("<span class='lquotes'></span>还没有添加说明！<span class='rquotes'></span>");
                            }
                        } else {
							$.tipMessage(data['info'], 1, 2000);
						}
                    },
                    error: function () {
                        alert('数据执行意外错误！');
                    }
                });
            } else {
                $.tipMessage('您填写的内容太多了！', 1, 2000);
            }
        });
    },

    //上一张下一张图片
    imageDetailInit: function () {
        var $imgItem = $("#imgItem");
        var $body = $("body");
        $imgItem.mousemove(function (e) {
            var positionX = 0;
            var $this = $(this);
            if (!browser.firefox) {
                positionX = window.event.offsetX;
            } else {
                positionX = e.originalEvent.x || e.originalEvent.layerX || 0;
            }

            if (positionX <= $this.width() / 2) {
                $this.css("cursor", "url(" + _config['domainStatic'] + "images/pre.cur),auto").attr('title', '点击查看上一张');
                $this.parent().attr('href', $this.attr('left'));
            } else {
                $this.css("cursor", "url(" + _config['domainStatic'] + "images/next.cur),auto").attr('title', '点击查看下一张');
                $this.parent().attr('href', $this.attr('right'));
            }
        });

        //查看原图
        $("#imageClick").click(function () {
            if (browser.ie) {
                docWidth = document.documentElement.scrollWidth;
            } else {
                docWidth = $(document).width();
            }

            //加入一个DIV(暗层),加入BODY中	
            var $background = $("<div></div>");
            $background.animate({
                'opacity': '.6'
            }, 1000).css({
                "width": docWidth,
                'height': $(document).height(),
                'background': '#656565',
                'z-index': '100',
                'position': 'absolute',
                'top': '0px',
                'left': '0px'
            });
            $body.append($background);
            //加入图片
            var $largeimage = $("<img/>");
            $largeimage.attr("src", $imgItem.attr("src")).css({
                'position': 'absolute',
                'z-index': '101',
                'display': 'none',
                'border': '10px solid #fff'
            });
            $body.append($largeimage);
            checkLargeImageWidth();
            var $largeImageBlank = $("<div></div>");
            $largeImageBlank.css({
                "width": docWidth,
                'height': $(document).height(),
                'background': '#656565',
                'z-index': '102',
                'cursor': 'pointer',
                'position': 'absolute',
                'filter': 'alpha(opacity=1)',
                'opacity': '.01',
                'top': '0px',
                'left': '0px'
            });
            $body.append($largeImageBlank);

            //图片滑出效果
            $largeimage.fadeIn(2000);
            $largeImageBlank.click(function () {
                $largeimage.fadeOut(1000, function () {
                    $largeimage.remove();
                })
                $background.fadeOut(1000, function () {
                    $background.remove();
                })
                $largeImageBlank.remove();
            });

            //检测大图宽度

            function checkLargeImageWidth() {
                if ($largeimage.width() > 0) {
                    $largeimage.css({
                        'left': ($body.width() - $largeimage.width() - 20) / 2,
                        'top': ($(document).height() - $largeimage.height() - 20) / 2 + 'px',
                        'width': $largeimage.width() + 'px',
                        'height': $largeimage.height() + 'px'
                    });
                } else {
                    setTimeout(checkLargeImageWidth, 10);
                }
            }
        });

        //图片评论和回复
        var $note = $("#note"); //回复内容
        $note.emotEditor({
            emot: true,
            defaultText: '请在这里输入评论！',
            defaultCss: 'comments_text'
        });

        $("#submitBtn").click(function () {
            var $uid = $(this).attr("uid"); //当前空间用户id
            var $pid = $(this).attr("pid"); //回复图片的id
            var $replayUser = $("#replayUser"); //回复框
            var $comments = $('#comments_list'); //回复列表
            var validCharLength = $note.emotEditor("validCharLength");
            if (validCharLength < 1) {
                $.tipMessage('还没有填写评论！', 1, 2000);
                $note.emotEditor("focus");
                return false;
            }
            if (validCharLength <= 75) {
                $.ajax({
                    type: "POST",
                    global: false, // 禁用全局Ajax事件.
                    url: _config['domainSite'] + "index.php?g=Member&m=Album&a=commentadd",
                    data: {
                        uid: $uid,
                        aid: $pid,
                        replayUser: $replayUser.html(),
                        content: $note.emotEditor("content")
                    },
                    dataType: "json",
                    success: function (data) {
                        if (data['error'] == 10007) {
                            $.tipMessage('评论内容不能为空！', 1, 2000);
                            $note.emotEditor("focus");
                            return false;
                        } else if (data['error'] == 20001) {
                            libs.userNotLogin('您需要先登录才能进行留言操作！');
                            return false;
                        } else if (data == 10006) {
                            $.tipMessage('您填写的内容太多了！', 1, 2000);
                        } else if (data['error'] == 30000) {
                            $.tipMessage('图片或评论不存在，或已删除！', 1, 2000);
							return false;
                        } else if (data['error'] == 10002) {
                            $.tipMessage('您操作的太频繁，请稍后再试！', 1, 2000);
                            return false;
                        } else if (data['error'] == 10005) {
                            $.tipMessage('本次操作失败了，请稍后重试！', 1, 2000);
                            return false;
                        } else if (data['error'] == 10000) {
                            $note.emotEditor("reset");
                            $("#replayUserDel").hide();
                            $("#replayUser").html("").hide();
                            $.tipMessage('评论添加成功！', 1, 2000, 0, function () {
                                albumLib.commentPageInit(1,$uid,$pid);
                            });
							return true;
                        } else {
                            $.tipMessage(data['info'], 1, 2000);
                        }
                    }
                });
            } else {
                $.tipMessage('您填写的内容太多了！', 1, 2000);
                $note.emotEditor("focus");
                return false;
            }
        });
    },
    //赞图片
    picPraiseInit: function (uid, pid) {
        var $picPraise = $('#praise');
        var $praiseNum = $('#praiseCount');
        var $praiseImg = $('.praiseImg');
        $.ajax({
            type: "POST",
            global: false, // 禁用全局Ajax事件.
            url: _config['domainSite'] + "index.php?g=Member&m=Album&a=addlove",
            data: {
                'pid': pid
            },
            dataType: "json",
            success: function (data) {
                if (data['error'] == 20001) {
                    user.userNotLogin('您还没有登录，无法喜欢图片哦！');
                    return false;
                } else if (data['error'] == 30000) {
                    $.tipMessage('图片不存在或已删除！', 1, 2000);
                    return false;
                } else if (data['error'] == 10013) {
                    $.tipMessage('您只能喜欢别人的图片，不能喜欢自己的图片！', 1, 3000);
                    return false;
                } else if (data['error'] == 10000) {
                    //$("#picComment").html(data);
                    var albumInfo = $('#praiseNum').attr('num');
                    $picPraise.html("<div id='praise'><a class='praiseImg' num='" + albumInfo + "' onclick='$call(function(){albumLib.cancelPraiseInit(" + uid + ", " + pid + ")});' " +
                        "onmouseover='$(\"#praiseCount\").html(\"-1\");' onmouseout='$(\"#praiseCount\").html(" + albumInfo + ");' title='取消喜欢'> </a></div>");
                    $.tipMessage('喜欢图片成功！', 0, 2000,0,function(){
                        location.href = location.href;
                    });
                } else {
                    $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
                    return false;
                }
            },
            error: function () {
                alert('数据执行意外错误！');
            }
        });
    },
    //取消喜欢
    cancelPraiseInit: function (uid, pid) {
        var $cancelPraise = $('#praise');
        var $cancelNum = $('#cancelCount');
        var $cancelImg = $('.praiseImg');
        $.ajax({
            type: "POST",
            global: false, // 禁用全局Ajax事件.
            url: _config['domainSite'] + "index.php?g=Member&m=Album&a=dellove",
            data: {
                'pid': pid
            },
            dataType: "json",
            success: function (data) {
                if (data['error'] == 20001) {
                    user.userNotLogin('您还没有登录，无法取消喜欢图片！');
                } else if (data['error'] == 30000) {
                    $.tipMessage('图片不存在或已删除！', 1, 2000);
                    return false;
                } else if (data['error'] == 10003) {
                    $.tipMessage('没有赞过这张图片！', 1, 2000, 0, function () {
                        location.href = location.href;
                    });
                    return false;
                } else if (data['error'] == 10013) {
                    $.tipMessage('这是您自己的图片！', 1, 2000);
                    return false;
                } else if (data['error'] == 10000) {
                    var albumInfo = $('#praiseNum').attr('num') - 1;
                    $cancelPraise.html("<div id='praise'><a class='praiseImg' num='" + albumInfo + "' onclick='$call(function(){albumLib.picPraiseInit(" + uid + ", " + pid + ")});' " +
                        "onmouseover='$(\"#praiseCount\").html(\"+1\");' onmouseout='$(\"#praiseCount\").html(" + albumInfo + ");' title='喜欢就点一下'> </a></div>");
                    $.tipMessage('取消喜欢图片成功！', 0, 2000);
                } else {
                    $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
                    return false;
                }
            },
            error: function () {
                alert('数据执行意外错误！');
            }
        });
    },
    //显示图片列表喜欢按钮
    showInit: function (pid) {
        $("#imgpraise" + pid).show();
    },
    //隐藏图片列表喜欢按钮
    hideInit: function () {
        $(".imgpraise").hide();
    },
    //照片列表喜欢图片
    doImagePraise: function (uid, pid, praiseNum, replyNum, filePath) {
        var $praiseNum = $('#praiseCount' + pid);
        var $replyNum = $('#replyNum' + pid);
        $.ajax({
            type: "POST",
            global: false, // 禁用全局Ajax事件.
            cache: false,
            url: _config['domainSite'] + "index.php?g=Member&m=Album&a=addlove",
            data: {
                'uid': uid,
                'pid': pid,
                filePath: filePath
            },
            dataType: "json",
            success: function (data) {
                if (data['error'] == 20001) {
                    user.userNotLogin('您还没有登录，无法喜欢图片！');
                } else if (data['error'] == 30000) {
                    $.tipMessage('图片不存在或已删除！', 1, 2000, 0, function () {
                        location.href = location.href;
                    });
                } else if (data['error'] == 10003) {
                    $.tipMessage('您已经赞过这张照片了！', 1, 2000);
                } else if (data['error'] == 10013) {
                    $.tipMessage('您只能喜欢别人的图片，不能喜欢自己的图片！', 1, 3000);
                } else if (data['error'] == 10000)  {
                    $praiseNum.html(++praiseNum + "人喜欢");
                    //$replyNum.html(++replyNum + "人评论");
                    $.tipMessage("图片喜欢成功！", 0, 2000);
                } else {
					$.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
				}
            },
            error: function () {
                alert('数据执行意外错误！');
            }
        });
    },

    //照片列表取消喜欢图片
    doImageCancelPraise: function (uid, pid, praiseNum, filePath) {
        var $cancelNum = $('#cancelCount' + pid);
        $.ajax({
            type: "POST",
            global: false,
            cache: false,
            url: _config['domainSite'] + "index.php?g=Member&m=Album&a=dellove",
            data: {
                'uid': uid,
                'pid': pid,
                filePath: filePath
            },
            dataType: "json",
            success: function (data) {
                if (data['error'] == 20001) {
                    user.userNotLogin('您还没有登录，无法取消喜欢图片！');
                } else if (data['error'] == 30000) {
                    $.tipMessage('图片不存在或已删除！', 1, 2000);
                    return false;
                } else if (data['error'] == 10003) {
                    $.tipMessage('没有赞过这张图片！', 1, 2000, 0, function () {
                        location.href = location.href;
                    });
                    return false;
                } else if (data['error'] == 10013) {
                    $.tipMessage('这是您自己的图片！', 1, 2000);
                    return false;
                } else if (data['error'] == 10000) {
                    $praiseNum.html(--praiseNum + "人喜欢");
                    $.tipmessagy("取消喜欢成功！", 0, 2000);
                } else {
                    $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
                    return false;
                }
            },
            error: function () {
                alert('数据执行意外错误！');
            }
        });
    },

    //图片评论分页
    commentPageInit: function (pum, uid, pid) {
        $.ajax({
            type: "POST",
            global: false, // 禁用全局Ajax事件.
            url: _config['domainSite'] + "index.php?g=Member&m=Home&a=album&userid="+uid+"&id="+pid+"&page="+pum,
            dataType: "text",
            success: function (data) {
                $("#comments_list").html(data);
            }
        });
    },

    //回复指定用户
    replayUserInit: function () {
        $(".reply").click(function () {
            var authorId = $(this).attr("authorId"); //指定用户id
            var nickname = $(this).attr("nickname"); //制定用户昵称
            $("#replayUser").show();
            $("#replayUserDel").show();
            $("#replayUser").html("回复@" + nickname + "[" + authorId + "]");
            $note = $('#note');
            $note.emotEditor("focus");
        });
    },

    //取消回复
    replayUserCancelInit: function () {
        $(".comments_input #replayUserDel").click(function () {
            $("#replayUserDel").hide();
            $("#replayUser").html("").hide();
            $('#note').emotEditor("focus");
        });
    },

    //删除评论 或回复
    imageCommentDelInit: function () { //删除评论 
        $(".del").click(function () {
            var $comments = $('#commentList'); //评论列表
            var uid = $(".delete").attr("uid"); //当前空间用户id
            var dialogObj = $.dialog.get('delAlbumComment');
            var cid = $(this).attr('cid');
			var pid = $(this).attr('pid');//照片ID
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
            $.dialog({
                id: 'delAlbumComment',
                title: false,
                border: false,
                follow: $(this)[0],
                content: '确认删除这条评论么？',
                okValue: '确认',
                ok: function () {
                    $.ajax({
                        type: "POST",
                        global: false, // 禁用全局Ajax事件.
                        url: _config['domainSite'] + "index.php?g=Member&m=Album&a=commentdel",
                        data: {
                            'cid': cid,
                            'uid': uid
                        },
                        dataType: "json",
                        success: function (data) {
                            if (data['error'] == 20001) {
                                user.userNotLogin('您需要先登录才能进行操作！');
                            } else if (data['error'] == 30000) {
                                $.tipMessage('图片不存在或已删除！', 1, 2000, 0, function () {
                                    location.href = location.href;
                                });
                            } else if (data['error'] == 30001) {
                                $.tipMessage('评论不存在或已删除！', 1, 2000, 0, function () {
                                    location.href = location.href;
                                });
                            } else if (data['error'] == 20002) {
                                $.dialog({
                                    id: 'delMiniblog',
                                    title: false,
                                    icon: 'alert',
                                    width: '260px',
                                    lock: true,
                                    content: '您没有权限删除！',
                                    okValue: '确认',
                                    ok: function () {}
                                });
                            } else if (data['error'] == 10005) {
                                $.tipMessage('本次操作失败了，请稍后重试！', 1, 2000);
                            } else if (data['error'] == 10000) {
                                $.tipMessage('评论删除成功！', 0, 2000, 0, function () {
                                    albumLib.commentPageInit(1,uid,pid);
                                });
                            } else {
                                $.tipMessage(data['info'], 1, 2000);
                            }
                        },
                        error: function () {
                            alert('数据执行意外错误！');
                        }
                    });
                },
                cancelValue: '取消',
                cancel: function () {

                }
            });
        });
    },

    //删除我喜欢的照片
    imagePraiseDelInit: function () {
        var lidArr;
        $(".delete").click(function () {
            lidArr = $(this).attr('id');
            $.dialog({
                id: 'delAlbum',
                title: false,
                border: false,
                follow: $(this)[0],
                content: '确认删除这张照片么？',
                okValue: '确认',
                ok: function () {
                    $.ajax({
                        type: "POST",
                        global: false, // 禁用全局Ajax事件.
                        url: _config['domainSite'] + "index.php?g=Member&m=Album&a=praisedel",
                        data: {
                            'lidArr': lidArr
                        },
                        dataType: "json",
                        success: function (data) {
                            if (data['error'] == 20001) {
                                user.userNotLogin('您需要先登录才能进行删除操作！');
                            } else if (data['error'] == 10005) {
                                $.tipMessage('参数错误！', 1, 2000);
                            } else if (data['error'] == 10000) {
                                $.tipMessage('删除喜欢的照片成功！', 0, 2000, 0, function () {
                                    location.href = location.href;
                                });
                            } else {
                                $.tipMessage(date['info'], 1, 2000);
                            }
                        },
                        error: function () {
                            alert('数据执行意外错误！');
                        }
                    });
                },
                cancelValue: '取消',
                cancel: function () {

                }
            });
        });
    },

    //批量删除喜欢照片
    imagesPraiseBatchDelInit: function () {
        $(".page #selectAll").click(function () { //全选
            var dialogObj = $.dialog.get('delAlbum');
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
            $('#list :checkbox').each(function () {
                $(this).attr('checked', 'checked');
            });
        });

        $(".page #selectOther").click(function () { //反选
            var dialogObj = $.dialog.get('delAlbum');
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
            $('#list :checkbox').each(function () {
                if ($(this).attr('checked')) {
                    $(this).removeAttr('checked');
                } else {
                    $(this).attr('checked', 'checked');
                }
            });
        });
        $('#list input').click(function () {
            var dialogObj = $.dialog.get('delAlbum');
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
        });

        $('.page #delButton').click(function () {
            var lidArr = new Array(); //获取选中收藏图片id
            var i = 0;
            $('#list input:checked').each(function () {
                lidArr[i] = $(this).attr('lid');
                i++;
            });
            if (lidArr.length <= 0) {
                $.tipMessage('请选择您要删除的图片！', 1, 2000);
                return false;
            }
            $.dialog({
                id: 'delAlbum',
                title: false,
                border: false,
                follow: $("#delButton")[0],
                content: '确认删除这些照片么？',
                okValue: '确认',
                ok: function () {
                    $.ajax({
                        type: "POST",
                        global: false, // 禁用全局Ajax事件.
                        url: _config['domainSite'] + "index.php?g=Member&m=Album&a=praisedel",
                        data: {
                            'lidArr': lidArr
                        },
                        dataType: "json",
                        success: function (data) {
                            if (data['error'] == 20001) {
                                user.userNotLogin('您需要先登录才能进行删除操作！');
                            } else if (data['error'] == 10005) {
                                $.tipMessage('参数错误！', 1, 2000);
                            } else if (data['error'] == 10011) {
                                $.tipMessage('数据错误！', 1, 2000);
                            } else if (data['error'] == 20002) {
                                $.tipMessage('您没有权限！', 1, 2000, 0, function () {
                                    location.href = location.href;
                                });
                            } else if (data['error'] == 10012) {
                                $.tipMessage('操作失败！', 1, 2000, 0, function () {
                                    location.href = location.href;
                                });
                            } else if (data['error'] == 10000) {
                                $.tipMessage('删除喜欢的照片成功！', 0, 2000, 0, function () {
                                    location.href = location.href;
                                });
                            } else {
                                $.tipMessage(date['info'], 1, 2000);
                            }
                        },
                        error: function () {
                            alert('数据执行意外错误！');
                        }
                    });
                },
                cancelValue: '取消',
                cancel: function () {}
            });
            return false;
        });
    }
}