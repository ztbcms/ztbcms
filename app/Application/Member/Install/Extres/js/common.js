//var baseDomain = window.location.host.match(/[0-9a-zA-Z]+\.(com|net)+/g);
//document.domain = baseDomain;
//浏览器版本
var browser = {};
var ua = navigator.userAgent.toLowerCase();
var browserStr;
(browserStr = ua.match(/msie ([\d]+)/)) ? browser.ie = browserStr[1] :
    (browserStr = ua.match(/firefox\/([\d]+)/)) ? browser.firefox = browserStr[1] :
    (browserStr = ua.match(/chrome\/([\d]+)/)) ? browser.chrome = browserStr[1] :
    (browserStr = ua.match(/opera.([\d]+)/)) ? browser.opera = browserStr[1] :
    (browserStr = ua.match(/version\/([\d]+).*safari/)) ? browser.safari = browserStr[1] : 0;
var isPad = navigator.userAgent.match(/iPad|iPhone|iPod|Android/i) != null;
//isPad = true;
//Prevent memory leaks in IE6
if (browser.ie == 6) {
    window.attachEvent("onunload", function () {
        for (var id in jQuery.cache) {
            if (jQuery.cache[id].handle) {
                try {
                    jQuery.event.remove(jQuery.cache[id].handle.elem);
                } catch (e) {}
            }
        }
    });
}
//cookie
(function ($) {
    jQuery.cookie = function (key, value, options) {
        // key and value given, set cookie...
        if (arguments.length > 1 && (value === null || typeof value !== "object")) {
            options = jQuery.extend({}, options);

            if (value === null) {
                options.expires = -1;
            }

            if (typeof options.expires === 'number') {
                var days = options.expires,
                    t = options.expires = new Date();
                t.setDate(t.getDate() + days);
            }

            return (document.cookie = [
                encodeURIComponent(key), '=',
                options.raw ? String(value) : encodeURIComponent(String(value)),
                options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
                options.path ? '; path=' + options.path : '',
                options.domain ? '; domain=' + options.domain : '',
                options.secure ? '; secure' : ''
            ].join(''));
        }

        // key and possibly options given, get cookie...
        options = value || {};
        var result, decode = options.raw ? function (s) {
                return s;
            } : decodeURIComponent;
        return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
    };
})(jQuery);

//tip消息提示
(function ($) {
    jQuery.tipMessage = function (msg, type, time, zIndex, callback) {
        if (typeof tipMessageTimeoutId !== 'number') {
            tipMessageTimeoutId = 0
        }
        if (typeof time !== 'number') {
            time = 2000
        }
        if (typeof zIndex !== 'number' || zIndex == 0) {
            zIndex = 65500
        }
        var $doc = $(document);
        var $win = $(window);
        var $tipMessage = $('#tipMessage');
        var _typeTag = '';
        var _newTop = 0;
        var _newLeft = 0;
        var _width = 0;
        var _NumCount = 1;
        var _mask = "";

        if ($tipMessage.length <= 0) {
            $("body").append('<div id="tipMessage" class="tip_message" ></div>');
            $tipMessage = $('#tipMessage');
        } else {
            if (browser.ie == 6 || browser.ie == 7) {
                $tipMessage.css({
                    width: '99%'
                });
            } else {
                $tipMessage.css({
                    width: 'auto'
                });
            }
        }
        $tipMessage.css({
            opacity: 0,
            zIndex: zIndex
        });
        clearTimeout(tipMessageTimeoutId); //清除旧的延时事件

        if (type == 1) {
            _typeTag = 'hits';
        } else if (type == 2) {
            _typeTag = 'fail';
        } else {
            _typeTag = 'succ';
        }
        if (browser.ie == 6) {
            _mask = '<iframe frameborder="0" scrolling="no" class="ie6_mask"></iframe>';
        }
        $tipMessage.html(_mask + '<div class="tip_message_content"><span class="tip_ico_' + _typeTag + '"></span><span class="tip_content" id="tip_content">' + msg + '</span><span class="tip_end"></span></div>').show();


        //计算top,left 值

        function _calculate() {
            _width = $('#tip_content').width() + 86; //计算tip宽度
            if ($doc.scrollTop() + $win.height() > $doc.height()) {
                _newTop = $doc.height() - $win.height() / 2 - 40;
            } else {
                _newTop = $doc.scrollTop() + $win.height() / 2 - 40;
            }

            if ($win.width() >= $doc.width()) {
                _newLeft = $doc.width() / 2 - _width / 2;
            } else {
                if ($win.width() <= _width) {
                    if ($doc.scrollLeft() + $win.width() + (_width - $win.width()) / 2 > $doc.width()) {
                        _newLeft = $doc.width() - _width;
                    } else {
                        _newLeft = $doc.scrollLeft() + $win.width() / 2 - _width / 2;
                    }
                } else {
                    //alert(1);
                    _newLeft = $doc.scrollLeft() + $win.width() / 2 - _width / 2;

                }
            }
            if (_newLeft < 0) {
                _newLeft = 0;
            }
        }
        _calculate(); //计算top,left 值
        $tipMessage.css({
            top: _newTop,
            left: _newLeft,
            width: _width,
            opacity: 10
        });

        //重置

        function _reSet() {
            _calculate(); //从新计算top,left 值
            $tipMessage.css({
                top: _newTop,
                left: _newLeft,
                width: _width
            });
        }
        //调整大小

        function _resize() {
            if (_NumCount % 2 == 0) { //解决IE6下scrollLeft值问题
                _reSet();
                _NumCount = 1;
            } else {
                ++_NumCount;
            }
        }
        if (!isPad) { //pad设备不支持浮动
            $win.bind({
                "scroll": _reSet,
                "resize": _resize
            });
        }
        tipMessageTimeoutId = setTimeout(function () {
            $tipMessage.remove();
            if (typeof callback == 'function') {
                callback.call();
            }
        }, time);
    };
})(jQuery);
//DIV输入框高度自动调整插件，专处理IE6版
(function(jQuery) {
	jQuery.fn.extend({
		elastic: function(options) {
			var defaults = {
				maxHeight: 1024
			};
			var options = $.extend(defaults, options);
			var mimics = ['paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 'fontSize', 'lineHeight', 'fontFamily', 'width', 'fontWeight'];
			return this.each(function() {
				var $obj = $(this),
					maxheight = parseInt(options.maxHeight, 10) || Number.MAX_VALUE,
					minheight = parseInt($obj.css('height'), 10) || lineHeight * 3,
					$twin = jQuery('<div />').css({
						'position': 'absolute',
						'display': 'none',
						'word-wrap': 'break-word',
						'word-break': 'break-all'
					});
				$twin.appendTo($obj.parent());
				var i = mimics.length;
				while (i--) {
					$twin.css(mimics[i].toString(), $obj.css(mimics[i].toString()));
				}
				
				function update() {
					var tContent = $obj.html()
					var twinContent = $twin.html();
					if (tContent != twinContent) {
						$twin.html(tContent);
						if (Math.abs($twin.height() - $obj.height()) > 3) {
							var goalheight = $twin.height();
							if (goalheight >= maxheight) {
								$obj.css({'height': maxheight + 'px', 'overflow-y': 'auto'});
							}
							else if (goalheight <= minheight) {
								$obj.css({'height': minheight + 'px', 'overflow-y': 'hidden'});
							}
							else {
								$obj.css({'height': goalheight + 'px', 'overflow-y': 'hidden'});
							}
						}
					}
				}
				if (browser.ie==6 && !$.support.style) {
					$obj.css({'overflow-y': 'hidden'});
					$obj.keyup(function() {
						update();
					});
				}
			})
		}
	})
})(jQuery);
//执行呼叫函数
$(document).ready(function(){
    domIsReady = true;
    if (domReadyList) {
        var fn, i = 0;
        while ((fn = domReadyList[i])) {
            fn.call(domReadyObject[i]);
            i++;
        };
        domReadyList = null;
    };
});

var core = {
    getPage: function (maxpage, path) {
        var pageNum = parseInt($("#pageNum").val());
        if (pageNum == "") {
            alert("请输入要转向的页数！");
        } else if (parseInt(maxpage) < pageNum) {
            alert("本类最大页数为" + maxpage + "！");
        } else if (pageNum < 1 || !pageNum) {
            alert("请输入正确的页数！");
        } else {
            if (path == undefined) {
                window.location.href = "../" + pageNum + "/"
            } else {
                window.location.href = path.replace("!!PageNum!!", pageNum);
            }
        }
    }
}

var user = {
    //用户未登录
    userNotLogin: function (msg) {
        $.tipMessage(msg, 1, 2000, 0, function () {
            libs.login();
        });
    },
    //检测用户是否登录
    loginInit: function (tid) {
        $('#vCode').focus(function () {
            $(this).addClass('input_size');
            $(this).val("");

        });
        $("#authCode").click(function () {
            libs.changeAuthCode();
            return false;
        });

        $('#changeAuthCode').click(function () {
            libs.changeAuthCode();
            return false;
        });
        var $url = $("#refer").val();
        var $loginName = $("#loginName");
        var $password = $("#password");
        var $vCode = $('#vCode');
        var $setCookieTime = $('#setCookieTime');
        var $errMessage = $('#errMessage');
        $("#submit2").click(function () {
            if ($loginName.val() == '' || $loginName.val() == '登录账号') {
                $errMessage.html('请输入正确的账号！').show();
                $loginName.val('').focus();
                return false;
            } else if ($loginName.val().length < 1) {
                $errMessage.html('账号长度应大于1位！').show();
                $loginName.focus();
                return false;
            } else if ($password.val() == '') {
                $errMessage.html('登录密码不能为空！').show();
                $password.val('').focus();
                return false;
            } else if ($password.val().length < 6) {
                $errMessage.html('密码长度应大于6位！').show();
                $password.focus();
                return false;
            } else if ($vCode.val() == '') {
                $errMessage.html('请输入验证码。').show();
                $vCode.focus();
                return false;
            }
            $errMessage.html('登录中，请稍后...').show();
            if ($setCookieTime.is(":checked")) {
                cookieTime = 1;
            } else {
                cookieTime = 0;
            }
            $.getJSON(_config['domainSite'] + "index.php?g=Member&m=Public&a=doLogin", "loginName=" + escape($loginName.val()) + "&password=" + escape($password.val()) + "&cookieTime=" + escape(cookieTime) + "&vCode=" + escape($vCode.val()) + '&escape=1',
                function (data) {
                    if (data['error'] == 10005) {
                        $errMessage.html('登录账号不能为空！').show();
                        $loginName.val('').focus();
                        $('#vCode').val("");
                        libs.changeAuthCode();
                        return false;
                    } else if (data['error'] == 20023) {
                        $errMessage.html('账号或密码错误！').show();
                        $('#vCode').val("");
                        libs.changeAuthCode();
                        return false;
                    } else if (data['error'] == 20031) {
                        $errMessage.html('验证码错误！').show();
                        $('#vCode').val("");
                        libs.changeAuthCode();
                        return false;
                    } else if (data['error'] == 20015) {
                        alert("该账号还没有激活，无法登录");
                        return false;
                    } else if (data['error'] == 20014) {
                        alert("该账号已被锁定");
                        $('#vCode').val("");
                        libs.changeAuthCode();
                        return false;
                    } else if (data['error'] == 10000) {
                        if (tid != undefined) {
							if(data['script']){
								$('head').append(data['script']);
							}
                            if ($url != undefined && $url != '') {
                                location.href = $url;
                            } else {
                                location.href = _config['domainSite'] +'index.php?g=Member&a=home';
                            }
                        } else {
                            $("#welcome").hide();
                            $("#userLogin").show();
                            $("#siteLink").attr({
                                'href': _config['domainSite'] + data["uid"] + '/',
                                'vip': data["vip"]
                            });
                            $("#userInfo").attr('src', data["avatar"]);
                            var dialogObj = $.dialog.get('login');
                            if (typeof dialogObj == 'object') {
                                dialogObj.close();
                            }
                        }
                    }
                });
            return false;
        });
    },
    //赞
    praiseUser: function (uid, nickname, rvip, mvip, num, id) {
        $praise = $('.praise_num');
        $praiseCount = $("#praiseCount");
        //var num = $praise.attr('num'), rvip = $praise.attr('rvip');
        if (id == 1) {
            var mvip = $("#siteLink").attr('vip');
            if (typeof mvip == 'undefined') {
                mvip = 0;
            }
        }
        //var uid = $(this).attr('uid');
        //var nickname = $(this).attr('nickname');
        $.getJSON(_config['domainSite'] + "index.php?g=Member&m=User&a=praiseup&callback=?", "uid=" + uid,
            function (data) {
                if (data['error'] == 20001) {
                    user.userNotLogin('您未登录无法执行此操作！');
                } else if (data['error'] == 10002) {
                    $.tipMessage('您最近刚刚赞过 <strong>' + nickname + '</strong>！', 1, 3000);
                } else if (data['error'] == 10013) {
                    $.tipMessage('您只能赞别人，不能赞自己哦!', 1, 3000);
                } else if (data['error'] == 10004) {
                    $.tipMessage('该用户不存在', 2, 3000);
                } else if (data['error'] == 10000) {
                    if (id == 1) {
                        $("#love").remove();
                        //$(window).height();
                        var $doc = $(document);

                        if (rvip == 0 && mvip == 0) {
                            $("body").append("<div id='love' class='charm'></div>");
                        } else {
                            $("body").append("<div id='love' class='charm2'></div>");
                        }
                        var $love = $("#love");
                        $love.css({
                            top: $doc.scrollTop() + 200
                        });
                        $love.animate({
                            top: $doc.scrollTop() + 100,
                            opacity: "show"
                        }, 500);
                        setTimeout(function () {
                            $love.animate({
                                top: $doc.scrollTop(),
                                opacity: 'hide'
                            }, 600, function () {
                                $praiseCount.animate({
                                    paddingTop: "25px",
                                    height: 'toggle'
                                }, 400, function () {
                                    $praiseCount.css({
                                        paddingTop: "0px"
                                    }).html(data['praise']).slideDown();
                                    $praise.unbind('mouseover mouseout');
                                });
                            });
                        }, 1000);
                    } else {
                        $praiseCount.html(num);
                        if (rvip == 0 && mvip == 0) {
                            $("body").append("<div id='love' class='charm'></div>");
                        } else {
                            $("body").append("<div id='love' class='charm2'></div>");
                        }
                        var $love = $("#love");
                        $love.animate({
                            top: "300px",
                            opacity: "show"
                        }, 500);
                        setTimeout(function () {
                            $love.animate({
                                top: '100px',
                                opacity: 'hide'
                            }, 600, function () {
                                $praiseCount.animate({
                                    paddingTop: "25px",
                                    height: 'toggle'
                                }, 400, function () {
                                    $praiseCount.css({
                                        paddingTop: "0px"
                                    }).html(data['praise']).slideDown();
                                    $praise.unbind('mouseover mouseout');
                                });
                            });
                        }, 1000);
                    }
                } else {
				    $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
				}
            });
        return false;
    },
    //用户登录框css样式
    loginCss: function () {
        $("#loginName").focus(function () {
            $(".username").addClass('input_focus');
        }).blur(function () {
            $(".username").removeClass('input_focus');
        });
        $("#password").focus(function () {
            $(".password").addClass('input_focus');
        }).blur(function () {
            $(".password").removeClass('input_focus');
        });

    }
};

//监听消息
var listenMsg = {
    id: 0, //监听消息ID
    sleepTime: 10 * 1000, //每隔多长时间监听一次
    titleCount: 0,
    oldTitle: '',
    sound: 0,
    isPlay: 0,
    start: function () {
        listenMsg.doListen();
    },
    stop: function () {
        clearTimeout(listenMsg.id);
        listenMsg.id = 0;
    },
    title: function () {
        listenMsg.titleCount++;
        if (listenMsg.titleCount > 20) {
            document.title = listenMsg.oldTitle;
            return false;
        } else if (listenMsg.titleCount % 2 == 0) {
            document.title = '【新通知】 - ' + listenMsg.oldTitle
        } else {
            document.title = '【　　　】 - ' + listenMsg.oldTitle
        }
        setTimeout("listenMsg.title()", 1000);
    },
    doListen: function () {
        $.getJSON(_config['domainMainSite'] + "index.php?g=Member&m=Public&a=checkNewNotification&callback=?&rand=" + Math.random(), {
                keyHash: $.cookie('loginKey'),
                isPlay: listenMsg.isPlay
            },
            function (data) {
                if (data['error'] == 20001) { //用户未登录
                    listenMsg.stop();
                    $("#msgTips").hide();
                } else {
                    if (data['mnew'] > 0) {
                        var player = '';
                        if (listenMsg.sound == 0) {
                            if (data['sound'] == 1) {
                                listenMsg.oldTitle = document.title;
                                listenMsg.title();
                                player = '<embed style="position:absolute;top:-100000px" width="0" height="0" type="application/x-shockwave-flash" swliveconnect="true" allowscriptaccess="sameDomain" menu="false" flashvars="sFile=' + _config['domainStatic'] + 'images/notification.mp3" src="' + _config['domainStatic'] + 'images/soundPlayer.swf" />';
                            }
                        }
                        //消息条数
                        $("#msgTips").html('<b>' + data['mnew'] + '</b>' + player).show();
                        listenMsg.sound = 1;
                    } else {
                        $("#msgTips").hide();
                    }
                    if (data['fnew'] > 0) {
                        $("#feedTips").html('<b>' + data['fnew'] + '</b>').show();
                    } else {
                        $("#feedTips").hide();
                    }
                    //listenFeed.id = setTimeout(listenFeed.doListen, listenFeed.sleepTime);
                    listenMsg.id = setTimeout(listenMsg.doListen, listenMsg.sleepTime);
                }
            });
        return false;
    }
};

var nav = {
    init: function(){
        select.init("searchType");
    },
    userMenu: function () {
        $(function () {
            var closeSetTimer = null;
            var loadSetTimer = null;
            var $set = $(".set_menu");
            var $setList = $(".m_set_list");
            $set.mouseover(function () {
                var $this = $(this);
                if (closeSetTimer != null) {
                    clearTimeout(closeSetTimer);
                    closeSetTimer = null
                }
                if (loadSetTimer != null) {
                    clearTimeout(loadSetTimer);
                    loadSetTimer = null
                }
                loadSetTimer = setTimeout(function () {
                        $setList.hide();
                        $this.next("div").show();
                    },
                    20);
            }).mouseout(function () {
                var $this = $(this);
                closeSetTimer = setTimeout(function () {
                        $this.next("div").hide();
                        if (loadSetTimer != null) {
                            clearTimeout(loadSetTimer);
                            loadSetTimer = null
                        }
                    },
                    20);
            });
            $setList.hover(function () {
                    var $this = $(this);
                    if (closeSetTimer != null) {
                        clearTimeout(closeSetTimer);
                        closeSetTimer = null
                    }
                },
                function () {
                    closeSetTimer = setTimeout(function () {
                            $setList.hide();
                            if (loadSetTimer != null) {
                                clearTimeout(loadSetTimer);
                                loadSetTimer = null
                            }
                        },
                        200)
                });
            /*$(".list", $setList).hover(function() {
				$(this).addClass("hover");
			},
			function() {
				$(this).removeClass("hover");
			});*/
        });
    },
    helpNoticeInit: function () {
        helpNoticeTimer = setTimeout(function () {
            clearInterval(helpNoticeTimer);
            var _wrap = $('#helpNotice');
            var _field = _wrap.find('li:first'); //此变量不可放置于函数起始处,li:first取值是变化的 
            var _h = _field.height(); //取得每次滚动高度(多行滚动情况下,此变量不可置于开始处,否则会有间隔时长延时
            _field.appendTo(_wrap); //隐藏后,将该行的margin值置零,并插入到最后,实现无缝滚动	
            nav.helpNoticeInit();
        }, 5000);
    }
};

var searchDance = {
    init: function () { //搜索
        var $search = $("#searchType");
        var sid = $search.attr("sid");
        var key = $("#txtKey").val();
        if (sid == 1) {
            if (key != "") {
                window.open(_config['domainMainSite'] + "index.php?g=Search&q=" + encodeURIComponent(key), '_blank');
            } else {
                window.open(_config['domainMainSite'] + "index.php?g=Search", '_blank');
            }
        } else {
            if (key != "") {
                window.open(_config['domainSite'] + "index.php?g=Member&m=User&a=search&keywords=" + encodeURIComponent(key), '_blank');
            } else {
                window.open(_config['domainSite'] + "index.php?g=Member", '_blank');
            }
        }
    }
};

var select = {
    init: function (key) {
        $(function () {
            var loadTimer = null;
            var closeCardTimer = null;
            var $Box = $("#" + key);
            var $BoxNext = $Box.next("div");
            $Box.parent().hover(function () {
                    $BoxNext.show();
                    if (closeCardTimer != null) {
                        clearTimeout(closeCardTimer);
                        closeCardTimer = null
                    }
                },
                function () {
                    closeCardTimer = setTimeout(function () {
                            $BoxNext.hide();
                            if (loadTimer != null) {
                                clearTimeout(loadTimer);
                                loadTimer = null
                            }
                        },
                        200)
                });
            $("a", $BoxNext).click(function () {
                var note = $(this).html();
                $Box.html(note + '<b class="arrow"></b>');
                $BoxNext.hide();
                if (note == "舞曲") {
                    $("#searchType").removeAttr("sid");
                    $("#searchType").attr("sid", 1);
                }
                if (note == "会员") {
                    $("#searchType").removeAttr("sid");
                    $("#searchType").attr("sid", 2);
                }

            });
        });
    }
}

var fans = {
    //加为粉丝
    fansAdd: function (uid, nickname, fid) {
        var $fans = $('#fans');
        $.getJSON(_config['domainSite'] + "index.php?g=Member&m=Relation&a=fansAdd", {'uid':uid},
            function (data) {
                if (data["error"] == 20001) {
                    user.userNotLogin('您未登录无法执行此操作！');
                } 
                else if (data["error"] == 10000) {
                    var makeHtml = '';
                    makeHtml += "<div id=\"addFollowing\" class=\"following_dialog_add\"><div class=\"box\"><div class=\"check\"><span><input type=\"checkbox\" name=\"checkbox\" value=\"1\"  id=\"is_quietly\"";
                    //是否悄悄关注
                    if (data["is_quietly"] == 1) {
                        makeHtml += "checked=\"checked\"";
                    }
                    makeHtml += "/></span><label for=\"is_quietly\">悄悄关注&nbsp;&nbsp;(对方和其他人不会知道您关注了他。)</label></div></div><div class=\"selection\">为“<a href=/" + data["friendsUser"]["userid"] + "/ target=\"_blank\">" + nickname + "</a>”选择分组：</div><div class=\"box\"><div class=\"group\"><ul class=\"radio\" id=\"aa\">";
                    if (data["friendsGroup"]) {
                        for (var n in data['friendsGroup']) //遍历数组中的每一项
                        {
                            makeHtml += "<li id=\"followingGroupLine1_" + data["friendsGroup"][n]["gid"] + "\" onclick=\"$(\'#addfgName1\').attr(\'value\', \'" + data["friendsGroup"][n]["name"] + "\');$(\'#edit\').attr(\'fgid\', \'" + data["friendsGroup"][n]["gid"] + "\');\"><span><input type=\"radio\" name=\"fgid\" value=\"" + data["friendsGroup"][n]["gid"] + "\"";
                            if (data["gid"] == data["friendsGroup"][n]["gid"]) {
                                makeHtml += "checked=\"checked\"";
                            }
                            makeHtml += "id=\"radio_" + data["friendsGroup"][n]["gid"] + "\"/> </span><label for=\"radio_" + data["friendsGroup"][n]["gid"] + "\">" + data["friendsGroup"][n]["name"] + "</label><span class=\"option\"><a class=\"icon edit\" title=\"编辑\" onclick=\"$(\'#editGroup\').show();$(\'#addfgName1\').attr(\'value\', \'" + data["friendsGroup"][n]["name"] + "\'); $(\'#radio_" + data["friendsGroup"][n]["gid"] + "\').attr(\'checked\', \'checked\');$(\'#addGroup\').hide(); $(\'#edit\').attr(\'fgid\', \'" + data["friendsGroup"][n]["gid"] + "\')\"; href=\"javascript:;\"></a><a class=\"icon del\" title=\"删除\" onclick=\"fans.followingGroupDel(" + data["friendsGroup"][n]["gid"] + ", 1,  \'" + data["friendsGroup"][n]["name"] + "\');\" href=\"javascript:;\"></a></span></li>";
                        }
                    }
                    makeHtml += "<li onclick=\"$(\'#editGroup\').hide();$(\'#addGroup\').hide();$(\'#foundGroup\').html(\'+创建分组\');\"><span><input type=\"radio\" name=\"fgid\" value=\"\"";
                    if (data["gid"] == 0) {
                        makeHtml += "checked=\"checked\"";
                    }
                    makeHtml += "id=\"radio_93\"/></span><label for=\"radio_93\">未分组</label></li></ul>";
                    makeHtml += "<div class=\"create_group\"><a herf=\"javascript:;\" id=\"foundGroup\">+创建分组</a></div><div id=\"addGroup\" style=\"display:none;\"><input type=\"text\" maxlength=\"7\" style=\"width:121px;\" class=\"input_normal\" id=\"addfgName2\"><span class=\"button-main\"><span><button type=\"button\" uid=\"" + data["friendsUser"]["userid"] + "\" id=\"adds\">添加</button></span></span><span class=\"cancel button2-main\"><span><a href=\"#\" id=\"cancel1\">取消</a></span></span></div><div id=\"editGroup\" style=\"display:none;\"><div class=\"create_group\">编辑分组</div><input type=\"text\" maxlength=\"7\" style=\"width:121px;\" class=\"input_normal\" id=\"addfgName1\"><span class=\"button-main\"><span><button type=\"button\" uid=\"" + data["friendsUser"]["userid"] + "\" id=\"edit\">编辑</button></span></span><span class=\"cancel button2-main\"><span><a href=\"#\" id=\"cancel2\">取消</a></span></span></div></div></div></div><script type=\"text/javascript\">fans.submit();</script>";
                    $.dialog({
                        id: 'friendAdd',
                        title: '添加关注好友',
                        width: '340px',
                        lock: true,
                        content: makeHtml,
                        okValue: '确认',
                        ok: function () {
                            var dialogObj = $.dialog.get('delGroup');
                            if (typeof dialogObj === 'object') {
                                dialogObj.close();
                            }
                            var fgid = $("input[type=radio]:checked").val();
                            var is_quietly = $("#is_quietly:checked").val();
                            $.post(_config['domainSite'] + "index.php?g=Member&m=Relation&a=fansAdd", {'uid':uid,'fgid':fgid,'is_quietly':is_quietly},
                                function (data) {
                                    if (data['error'] == 20001) {
                                        user.userNotLogin('您未登录无法执行此操作！');
                                    } else if (data['error'] == 10013) {
                                        $.tipMessage('您只能关注别人，不能关注自己哦！', 1, 3000);
                                    } else if (data['error'] == 10003) {
                                        $.tipMessage('您已经关注过了 <strong>' + nickname + '</strong> ！', 1, 3000);
                                        $fans.html('<a class="attention already" href="javascript:;" onClick="fans.fansDel(' + uid + ',\'' + nickname + '\'); return false;"> </a>');
                                    } else if (data['error'] == 10004) {
                                        $.tipMessage('该用户不存在！', 1, 3000);
                                    } else if (data['error'] == 10007) {
                                        $.tipMessage('该分组不存在！', 1, 3000);
                                    } else if (data['error'] == 10006) {
                                        $.tipMessage('关注数量已经是最大值', 1, 3000);
                                    } else if (data['error'] == 10000) {
                                        if (fid == 1) {
                                            location.href = location.href;
                                        } else {
                                            $.tipMessage('您成功关注了 <strong>' + nickname + '</strong> ！', 0, 3000);
                                            $fans.html('<a class="attention already" href="javascript:;" onClick="fans.fansDel(' + uid + ',\'' + nickname + '\'); return false;"></a>');
                                        }
                                    } else if (data['error'] == 2) {
                                        if (fid == 1) {
                                            location.href = location.href;
                                        } else {
                                            $.tipMessage('您成功关注了 <strong>' + nickname + '</strong> ！', 0, 3000);
                                            $fans.html('<a onclick="fans.fansDel(' + uid + ', \'' + nickname + '\');  return false;" class="attention mutual" href="javascript:;">');
                                        }
                                    } else {
                                        $.tipMessage(data['info'], 2, 3000);
                                    }
                                },
                                'json'
                            );
                        },
                        cancelValue: '取消',
                        cancel: function () {
                            var dialogObj = $.dialog.get('delGroup');
                            if (typeof dialogObj === 'object') {
                                dialogObj.close();
                            }
                        }
                    });
                }
                else{
                    $.tipMessage(data['info'], 2, 3000);
                } 
            });
    },
    //取消粉丝
    fansDel: function (uid, nickname, fid) {
        $.dialog({
            id: 'friendDel12',
            title: '取消关注',
            width: '340px',
            lock: true,
            content: '<br/>你确定取消对 <strong>' + nickname + '</strong>的关注吗？<br/><br/><span style="color: #999999;">提示：取消关注后您将再也不能收到他的新鲜事。</span><br/><br/>',
            okValue: '确认',
            ok: function () {
                var $fans = $('#fans');
                $.post(_config['domainSite'] + "index.php?g=Member&m=Relation&a=followingdel", {'friend_uid':uid},
                    function (data) {
                        if (data['error'] == 20001) {
                            user.userNotLogin('您未登录无法执行此操作！');
                        } else if (data['error'] == 10004) {
                            $.tipMessage('您不是 <strong>' + nickname + '</strong> 的歌迷!', 1, 3000);
                        } else if (data['error'] == 10013) {
                            $.tipMessage('您只能关注别人，不能关注自己哦！', 1, 3000);
                        } else if(data['error']==10000) {
                            if (fid == 1) {
                                location.href = location.href;
                            } else {
                                $.tipMessage('您成功取消了对 <strong>' + nickname + '</strong> 的关注!', 0, 3000);
                                $fans.html('<a class="attention" href="javascript:;" onClick="fans.fansAdd(' + uid + ',\'' + nickname + '\'); return false;"></a>');
                            }
                        }
                        else{
                            $.tipMessage(data['info'], 2, 3000);
                        }
                    },'json');
            },
            cancelValue: '取消',
            cancel: function () {

            }
        });
    },
    submit: function () {
        $("#foundGroup").click(function () {
            $("#editGroup").hide();
            $("#addGroup").show();
        });
        $("#cancel1").click(function () {
            $("#addGroup").hide();
        });
        $("#cancel2").click(function () {
            $("#editGroup").hide();
            $("#foundGroup").html("+创建分组");
        });
        $("#edit").click(function () {
            if (!/^([^<>'"\/\\])*$/.test($('#addfgName1').val())) {
                $.tipMessage("名字中不能有 < > \' \" / \\ 等非法字符！", 1, 2000);
                return false;
            }
            var fgid = $(this).attr('fgid');
            var uid = $(this).attr("uid");
            //var nickname = $(this).attr("nickname");

            $.post(_config['domainSite'] + "index.php?g=Member&m=Relation&a=groupeditname", {"gid": fgid,"name":$('#addfgName1').val(),"uid":uid},
                function (data) {
                    if (data['error'] == 20001) {
                        user.userNotLogin('您未登录无法执行此操作！')
                    } else if (data['error'] == 20002) {
                        $.tipMessage("您没有权限修改！", 1, 2000);
                    } else if (data['error'] == 10007) {
                        $.tipMessage("分组名称不能为空！", 1, 2000);
                    } else if (data['error'] == 10006) {
                        $.tipMessage("分组名超过不能超过七个字！", 1, 2000);
                    } else if (data['error'] == 10000) {
                        var makeHtml = '';
                        makeHtml += "<span><input type='radio' name='fgid' value='" + data["gid"] + "'";
                        makeHtml += " id='radio_" + data["gid"] + "'/></span><label for='radio_" + data["gid"] + "'>" + data["name"] + "</label><span class='option'><a class='icon edit' title='编辑' onclick=\"$('#editGroup').show();$('#addfgName1').attr('value', '" + data["name"] + "'); $('#radio_" + data["gid"] + "').attr('checked', 'checked');$('#addGroup').hide(); $('#edit').attr('fgid', '" + data["gid"] + "')\"; href='javascript:;'></a><a class='icon del' title='删除' onclick=\"fans.followingGroupDel(" + data["gid"] + ", 1, \'" + data["name"] + "\');\"href='javascript:;'></a></span>";
                        $('#followingGroupLine1_'+data["gid"]).html(makeHtml);
                        $('#addfgName1').val('');
                    }
                    else{
                        $.tipMessage(data['info'], 2, 3000);
                    }
                },
                'json'
            );
        });
        //dialog添加分组
        $("#adds").click(function () {
            if (!/^([^<>'"\/\\])*$/.test($('#addfgName2').val())) {
                $.tipMessage("名字中不能有 < > \' \" / \\ 等非法字符！", 1, 2000);
                return false;
            }
            var uid = $(this).attr("uid");
            //var nickname = $(this).attr("nickname");
            var fg_name = $("#addfgName2").val();

            $.post(_config['domainSite'] + "index.php?g=Member&m=Relation&a=groupadd", {"name":fg_name,"uid":uid},
                function (data) {
                    if (data['error'] == 20001) {
                        user.userNotLogin('您未登录无法执行此操作！')
                    } else if (data['error'] == 20002) {
                        $.tipMessage("您没有权限添加！", 1, 2000);
                    } else if (data['error'] == 10007) {
                        $.tipMessage("分组名称不能为空！", 1, 2000);
                    } else if (data['error'] == 10006) {
                        $.tipMessage("分组名不能超过七个字！", 1, 2000);
                    } else if (data['error'] == 100061) {
                        $.tipMessage("分组数量不能超过8个！", 1, 2000);
                    } else if (data['error'] == 10004) {
                        $.tipMessage("你不是对方的歌迷", 1, 2000);
                    } else if(data['error']==10000) {
                        var makeHtml = '';
                        makeHtml += "<li id='followingGroupLine1_" + data["gid"] + "'><span><input type='radio' name='fgid' value='" + data["gid"] + "'";
                        makeHtml += " id='radio_" + data["gid"] + "'/></span><label for='radio_" + data["gid"] + "'>" + data["name"] + "</label><span class='option'><a class='icon edit' title='编辑' onclick=\"$('#editGroup').show();$('#addfgName1').attr('value', '" + data["name"] + "'); $('#radio_" + data["gid"] + "').attr('checked', 'checked');$('#addGroup').hide(); $('#edit').attr('fgid', '" + data["gid"] + "')\"; href='javascript:;'></a><a class='icon del' title='删除' onclick=\"fans.followingGroupDel(" + data["gid"] + ", 1, \'" + data["name"] + "\');\"href='javascript:;'></a></span></li>";
                        $("#aa").append(makeHtml);
                        $("#addfgName2").val('');
                    }
                    else{
                        $.tipMessage(data['info'], 2, 3000);
                    }
                },
                'json'
            );
        })
    },
    //删除我关注人的分组
    followingGroupDel: function (fgid, type, fgName) {
        var dialogObj = $.dialog.get('delGroup');
        if (typeof dialogObj === 'object') {
            dialogObj.close();
        }
        if (type == 1) {
            follow = $('#followingGroupLine1_' + fgid)[0];
        } else {
            follow = $('#followingGroupLine_' + fgid)[0];
        }
        $.dialog({
            id: 'delGroup',
            title: false,
            border: false,
            follow: follow,
            content: '确认删除分组"' + fgName + '"么？',
            okValue: '确认',
            ok: function () {
                $.getJSON(_config['domainSite'] + "index.php?g=Member&m=Relation&a=groupdel&callback=?", "gid=" + escape(fgid),
                    function (data) {
                        if (data['error'] == 20001) {
                            user.userNotLogin('您未登录无法执行此操作！')
                        } else if (data['error'] == 20002) {
                            $.tipMessage("您没有权限添加！", 1, 2000);
                        } else if (data['error'] == 10000) {
                            if (type == 1) {
                                $("#followingGroupLine1_" + fgid).remove();
                            } else {
                                location.href = data['referer'];
                            }
                        } else {
                            $.tipMessage(data['info'], 1, 2000);
                        }
                    });
            },
            cancelValue: '取消',
            cancel: function () {}
        });
    }
};
//管理
var libs = {

    patchSign: function (sid) {
        $.getJSON(_config['domainSite'] + "user?a=doUserLast&callback=?", "sid=" + sid,
            function (data) {
                if (data['error'] == 20001) {
                    user.userNotLogin('您未登录无法执行此操作！');
                } else if (data['error'] == 10002) {
                    $.tipMessage('今天已经签过到了！', 1, 3000);
                    return false;
                } else if (data['error'] == 20005) {
                    $.tipMessage('积分不足！', 1, 3000);
                    return false;
                } else {
                    var dialogObj = $.dialog.get('userlast');
                    if (typeof dialogObj === 'object') {
                        dialogObj.close();
                    }
                    var num = $("#user_sign").attr('num');
                    if (num != 0) {
                        if (data["sign_sum"] == 0) {
                            var num1 = ++num;
                        } else {
                            var num1 = data["sign_sum"];
                        }
                    } else {
                        var num1 = 1;
                    }


                    $("#time").html(data["sign_num"]);
                    $("#user_sign").attr({
                        'title': "已经连续签到" + data["sign_num"] + "天, 累计签到" + num1 + "天"
                    });

                    $.tipMessage('签到成功，已领取' + data['score'] + '积分。', 0, 3000);
                }
            });
        return false;
    },
    praise: function (rvip, mvip, num) {
        $praise = $('.praise_num');
        $praiseCount = $("#praiseCount");
        $praise.mouseover(function () {
            if (rvip == 0 && mvip == 0) {
                $praiseCount.html("+1");
            } else {
                $praiseCount.html("+2");
            }
        }).mouseout(function () {
            $praiseCount.html(num);
        });

    },
    allSelect: function (objName) { //全选
        $('#' + objName + ' :checkbox').each(function () {
            if (!$(this).attr('disabled')) {
                $(this).attr('checked', 'checked');
            }
        });
    },
    otherSelect: function (objName) { //反选
        $('#' + objName + ' :checkbox').each(function () {
            if ($(this).attr('checked')) {
                $(this).removeAttr('checked');
            } else {

                if (!$(this).attr('disabled')) {
                    $(this).attr('checked', 'checked');
                }
            }
        });
    },
    //空间登录框		
    login: function () {
        $.ajax({
            type: "POST",
            global: false, // 禁用全局Ajax事件.
            url: _config['domainSite'] + 'index.php?g=Member&m=Public&a=logindialog',
            dataType: "text",
            success: function (data) {
                $.dialog({
                    id: 'login',
                    title: '会员登录',
                    content: data,
                    lock: true
                });
            }
        });
        return false;
    },
    spaceInit: function () {
        upTop.init(); // 返回顶部
    },
    spaceHomeInit: function () {
        $("#wallContent").elastic({
            maxHeight: 130
        }).emotEditor({
            allowed: 300,
            charCount: true,
            emot: true,
            newLine: true
        });
        upTop.init(); // 返回顶部

        $('#selectAllaa').click(function () { //全选
            $('#list :checkbox').each(function () {
                if (!$(this).attr('disabled')) {
                    $(this).attr('checked', 'checked');
                }
            });
        });

        $('#selectOtheraa').click(function () { //反选
            $('#list :checkbox').each(function () {
                if ($(this).attr('checked')) {
                    $(this).removeAttr('checked');
                } else {
                    if (!$(this).attr('disabled')) {
                        $(this).attr('checked', 'checked');
                    }
                }
            });
        });
    },
    //验证码
    changeAuthCode: function () {
        var num = new Date().getTime();
        var rand = Math.round(Math.random() * 10000);
        var num = num + rand;
        $("#authCode").attr('src', $("#authCode").attr('src') + "&refresh=1&t=" + num);
    },
    reloadcode: function (_id) {
        if (_id != '') {
            $('#' + _id).attr('src', '/system?a=getVCode&rand=' + Math.random());
        }
    },
    redirect: function (url) {
        location.href = url;
    },
    imageError: function (obj) {
        obj.onerror = null;
        obj.src = _config['domainStatic'] + "images/none.gif";
    },
    //个人中心feed初始值
    feed: function () {
		libs.feedNew(2);
		$('#refresh').attr({'cid':2, type:0});
		return true;
    },
    //个人中心feed标签
    feedNew: function (cid) {
        $(".current").removeClass('current');
        var $feed = $("#feed");
        $feed.html('<div class="load"></div>');
        if (typeof cid == "undefined" || cid == 0) {
            $('#a1').show();
            $("#a2").hide();
            $("#a3").hide();
            $("#feed_all").removeClass('on');
            $("#friend_feed").addClass('on');
            $("#feed_me").removeClass('on');
            var $targetLink = $('#feed_' + 0)
        } else if (cid == 2) {
            $('#a1').hide();
            $("#a2").show();
            $("#a3").hide();
            $("#friend_feed").removeClass('on');
            $("#feed_me").removeClass('on');
            $("#feed_all").addClass('on');
            var $targetLink = $('#feedA_' + 0)
        } else if (cid == 3) {
            $('#a1').hide();
            $("#a2").hide();
            $("#a3").show();
            $("#feed_all").removeClass('on');
            $("#friend_feed").removeClass('on');
            $("#feed_me").addClass('on');
            var $targetLink = $('#feedM_' + 0)
        }
        $targetLink.addClass('current');
        $.ajax({
            type: "POST",
            global: false, // 禁用全局Ajax事件.
            url: _config['domainSite'] +'index.php?g=Member&m=Feed&a=fetchfeed',
            data: {
                cid: cid
            },
            dataType: "text",
            success: function (data) {
                if (data == 20001) {
                    alert('您没有登录或已经退出，请登录后再进行操作 ！');
                    location.href = '/';
                } else {
                    $feed.html(data);
                }
            }
        });
    },
    //加载feed信息
    currItem: 0,
    showFeedMenu: function (type, cid) {
        var $feed = $("#feed");
        $feed.html('<div class="load"></div>');
        if (cid == 0) {
            var $currLink = $('#feed_' + libs.currItem);
            var $targetLink = $('#feed_' + type);
        } else if (cid == 2) {
            var $currLink = $('#feedA_' + libs.currItem);
            var $targetLink = $('#feedA_' + type);
        } else if (cid == 3) {
            var $currLink = $('#feedM_' + libs.currItem);
            var $targetLink = $('#feedM_' + type);
        }
        /*if(libs.currItem==type){
			return false;
		}*/
        $(".current").removeClass('current');
        //$currLink.removeClass('current');
        $targetLink.addClass('current');
        libs.currItem = type;
        $.ajax({
            type: "POST",
            global: false, // 禁用全局Ajax事件.
            url: _config['domainSite'] +'index.php?g=Member&m=Feed&a=fetchfeed',
            data: {
                'type': type,
                'cid': cid
            },
            dataType: "text",
            success: function (data) {
                if (data == 20001) {
                    alert('您没有登录或已经退出，请登录后再进行操作 ！');
                    location.href = '/';
                } else {
                    $feed.html(data);
                }
            }
        });

    },
    showFeedMenu1: function () {
        $("#refresh").click(function () {
            var cid = $(this).attr("cid");
            var type = $(this).attr("type");
            var $feed = $("#feed");
            $feed.html('<div class="load"></div>');
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] +'index.php?g=Member&m=Feed&a=fetchfeed',
                data: {
                    'type': type,
                    'cid': cid
                },
                dataType: "text",
                success: function (data) {
                    if (data == 20001) {
                        alert('您没有登录或已经退出，请登录后再进行操作 ！');
                        location.href = '/';
                    } else {
                        $feed.html(data);
                    }
                }
            });
        });
    },
    //空间载入留言
    /*wall: function(uid) {
	
		$.ajax({
			type: "POST",
			global: false,// 禁用全局Ajax事件.
			url: "/wall?a=fetchSpaceWall",
			data:{uid: uid},
			dataType: "text",
			success: function(data){
			
			$("#wall_content").html(data);
			}
		});
	},*/
    //签到
    userSign: function () {
        $("#user_sign_but").attr('disabled', "disabled");
        $.getJSON(_config['domainSite'] + "user?a=userSign&callback=?",
            function (data) {
                if (data['error'] == 20001) {
                    user.userNotLogin('您未登录无法执行此操作！');
                    $("#user_sign_but").removeAttr('disabled');
                } else if (data['error'] == 10002) {
                    $.tipMessage('今天已经签过到了！', 1, 3000);
                    $("#user_sign_but").removeAttr('disabled');
                    return false;
                } else if (data['error'] == 20007) {
                    $.ajax({
                        type: "POST",
                        global: false, // 禁用全局Ajax事件.
                        url: "/user?a=userLast",
                        dataType: "text",
                        success: function (data) {
                            $.dialog({
                                id: 'userlast',
                                title: '会员签到',
                                content: ' ' + data + '',
                                lock: true
                            });
                            $("#user_sign_but").removeAttr('disabled');
                            //$("#rand").show();
                            //$("#rand").html(data);
                        }
                    });

                } else {
                    var num = $("#user_sign").attr('num');
                    if (num != 0) {
                        var num1 = ++num;
                    } else {
                        var num1 = 1;
                    }

                    $("#time").html(data["sign_num"]);
                    $("#user_sign").attr({
                        'title': "已经连续签到" + data["sign_num"] + "天, 累计签到" + num1 + "天"
                    });

                    $.tipMessage('签到成功，已领取' + data['score'] + '积分。', 0, 3000);
                    $("#user_sign_but").removeAttr('disabled');
                }

            });
        return false;
    },

    rand: function () {
        $.ajax({
            type: "POST",
            global: false, // 禁用全局Ajax事件.
            url: "/user?a=userSlot",
            dataType: "text",
            success: function (data) {
                $("#rand").show();
                $("#rand").html(data);
            }
        });
    },

    //账号激活
    activate: function () {
        $('#vCode').focus(function () {
            $(this).addClass('input_size');
            $(this).val("");

        });
        $("#authCode").click(function () {
            libs.changeAuthCode();
            return false;
        });

        $('#changeAuthCode').click(function () {
            libs.changeAuthCode();
            return false;
        });

        $("#activateSubmit").click(function () {
            var $loginName = $("#loginName");
            var $password = $("#password");
            var $vCode = $('#vCode');
            var $errMessage = $('#errMessage');
            if ($loginName.val() == '' || $loginName.val() == '登录账号') {
                $errMessage.html('请输入正确的账号！').show();
                $loginName.val('').focus();
                return false;
            } else if ($loginName.val().length < 3) {
                $errMessage.html('账号长度应大于4位！').show();
                $loginName.focus();
                return false;
            } else if ($password.val() == '') {
                $errMessage.html('登录密码不能为空！').show();
                $password.val('').focus();
                return false;
            } else if ($password.val().length < 6) {
                $errMessage.html('密码长度应大于6位！').show();
                $password.focus();
                return false;
            }
            if ($vCode.val() == '' || $vCode.val() == '请输入激活码') {
                $errMessage.html('验证码不能为空').show();
                $vCode.focus();
                return false;
            } else if ($vCode.val().length != 4) {
                $errMessage.html('请正确输入验证码！').show();
                $vCode.focus();
                return false;
            }
            $errMessage.html('激活中，请稍后...').show();
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: "/user?a=doActivatePassport",
                data: {
                    loginName: $loginName.val(),
                    password: $password.val(),
                    vCode: $vCode.val()
                },
                dataType: "text",
                success: function (data) {
                    if (data == 10005) {
                        $errMessage.html('登录账号不能为空！').show();
                        $loginName.val('').focus();
                        return false;
                    } else if (data == 20031) {
                        $errMessage.html('请输入正确的验证码！').show();
                        libs.changeAuthCode();
                        $vCode.val('').focus();
                        return false;
                    } else if (data == 20023) {
                        $errMessage.html('账号或密码错误！').show();
                        return false;
                    } else if (data == 20014) {
                        $errMessage.html('该账号已被锁定').show();
                        return false;
                    } else if (data == 10000) {
                        location.href = '/system?a=home';
                    }

                }
            });
        });
    }

};


//返回顶部绝对定位
var upTop = {
    defaults: {
        right: 20,
        bottom: 30
    },
    isIe6: ($.browser.msie && parseInt($.browser.version) == 6) ? true : false,
    isPad: navigator.userAgent.match(/iPad|iPhone|iPod|Android/i) != null,
    mask: '',
    $this: '',
    $doc: '',
    $win: '',
    init: function () {
        if (!(this.isIe6 && screen.width < 1000) && !this.isPad) {
            if (this.isIe6) {
                this.mask = '<iframe frameborder="0" scrolling="no" class="ie6_mask"></iframe>';
            }
            $("body").append('<div id="top_control" class="top_control" title="返回顶部">' + this.mask + '<span></span></div>');

            this.$this = $('#top_control');
            this.$doc = $(document);
            this.$win = $(window);

            if (this.isIe6) {
                this.$this.css('position', 'absolute')
                    .click(function () {
                        $("html, body").animate({
                            scrollTop: 0
                        }, 120);
                    });
                this.resize();
                this.show();
                this.$win.bind({
                    "scroll": this.scroll,
                    "resize": this.resize
                });
            } else {
                this.$this.css({
                    position: 'fixed',
                    right: this.defaults.right,
                    bottom: this.defaults.bottom
                })
                    .click(function () {
                        $("html, body").animate({
                            scrollTop: 0
                        }, 120);
                    });

                this.show();
                this.$win.bind({
                    "scroll": this.scroll
                });
            }
        }
    },
    scroll: function () {
        upTop.show();
        if (upTop.isIe6) {
            var topTemp = upTop.$doc.scrollTop() + upTop.$win.height() - upTop.$this.height() - upTop.defaults.bottom;
            var leftTemp = upTop.$doc.scrollLeft() + upTop.$win.width() - upTop.$this.width() - upTop.defaults.right;

            upTop.$this.css({
                top: topTemp,
                left: leftTemp
            });
        }
    },
    show: function () {
        (upTop.$doc.scrollTop() > 100) ? upTop.$this.show() : upTop.$this.hide();
    },
    resize: function () {
        if (upTop.$doc.scrollTop() + upTop.$win.height() > upTop.$doc.height()) {
            var topTemp = upTop.$doc.height() - upTop.$this.height() - upTop.defaults.bottom;
        } else {
            var topTemp = upTop.$doc.scrollTop() + upTop.$win.height() - upTop.$this.height() - upTop.defaults.bottom;
        }
        if (upTop.$win.width() == upTop.$doc.width()) {
            var leftTemp = upTop.$doc.width() - upTop.$this.width() - upTop.defaults.right;
        } else {
            if (upTop.$doc.width() > screen.width) {
                var leftTemp = upTop.$win.width() - upTop.$this.width() - upTop.defaults.right;
            } else {
                var leftTemp = upTop.$doc.scrollLeft() + upTop.$win.width() - upTop.$this.width() - upTop.defaults.right;
            }
        }
        upTop.$this.css({
            top: topTemp,
            left: leftTemp
        });
    }
};

var appSendMoney = {
    money: function (val) {
        if (val <= 30) {
            text = "恭喜您获得本时段空间奖励" + val + "积分";
        } else if (val <= 60) {
            text = "嗯，还不错哦！您获得了本时段空间奖励" + val + "积分";
        } else if (val <= 90) {
            text = "哇塞，您的人品不错哦！本次获得了" + val + "积分";
        } else if (val <= 100) {
            text = "您的人品大爆发啦，本次获得了" + val + "积分";
        }
        $(document).ready(function () {
            setTimeout(function () {
                $.dialog({
                    id: 'money',
                    title: '奖励',
                    content: text,
                    okValue: '领取奖励',
                    ok: function () {

                    }
                });
            }, 1000);
        });
    }
};

var message = {
    //发送站内信
    msgSendInit: function (uid,sessionUid) { //接受者id和昵称
        if (uid != sessionUid) {
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Msg&a=msgsenddialog",
                data: {
                    'uid': uid
                },
                dataType: "json",
                success: function (data) {
                    if (data['error'] == 20001) {
                        user.userNotLogin('您未登录无法执行此操作！');
                        return false;
                    } else if (data['error'] == 10004) {
                        $.tipMessage('用户不存在！', 1, 3000);
                        return false;
                    } else if (data['error'] == 10000) {
                        $.dialog({
                            id: 'sendMsg',
                            title: '发送私信',
                            //width: '340px',
                            lock: true,
                            content: data['data'],
                            okValue: '确认',
                            ok: function () {
                                var $fnote = $("#fnote");
                                var validCharLength = $fnote.emotEditor("validCharLength");
                                if (validCharLength < 1 || $fnote.emotEditor("content") == "") {
                                    $.tipMessage('请输入消息内容', 1, 3000);
                                    $fnote.emotEditor("focus");
                                    return false;
                                }
                                if ($fnote.html().length < 501) {
                                    var uid = $("#uid").attr("uid");
                                    $.ajax({
                                        type: "POST",
                                        global: false, // 禁用全局Ajax事件.
                                        url: _config['domainSite'] + "index.php?g=Member&m=Msg&a=msgadd",
                                        data: {
                                            'uid': uid,
                                            'note': $fnote.emotEditor("content")
                                        },
                                        dataType: "json",
                                        success: function (data) {
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
                                                $.tipMessage('私信已发出！', 0, 3000);
                                            } else {
                                                $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
                                                return false;
                                            }
                                        }
                                    });
                                } else {
                                    $.tipMessage('您写的太多了，我装不下了！', 1, 3000);
                                    $fnote.focus();
                                    return false;
                                }

                            },
                            cancelValue: '取消',
                            cancel: function () {

                            }
                        });
                    }
                }
            });
        } else {
            $.tipMessage('对不起，您不能给自己发私信！', 1, 2000);
            return false;
        }
    }
};