$(function () {
    if ($(".sp_card").length <= 0) {
        var html = '<div class="sp_card" style="display:none; height:180px;">' 
        + '<div class="sp_card_content">' 
        + '<div class="sp_card_loading" style="display:none;"><img src="' + _config['domainStatic'] + '/images/loading.gif" width="32" height="32" alt="正在加载中..." /></div>' 
        + '<dl class="sp_card_view">' 
        + '<dt><a href="javascript:;" target="_blank"><img src="' + _config['domainStatic'] + '/images/none.gif" border="0" width="48" height="48" onerror="this.src= \''+ _config['domainStatic'] + 'images/noavatar.jpg\'" /></a></dt>' 
        + '<dd></dd>' 
        + '</dl>' 
        + '<div class="sp_card_intro"></div>' 
        + '<div class="sp_card_medal">' 
        + '<ul class="medal">' + '</ul>' 
        + '</div>' 
        + '<div class="sp_card_follow">\
            <a href="javascript:;" class="sp_follow_bnt" style="display:none;">+&nbsp;关注</a>\
            <a href="javascript:;" class="sp_follow_bnt sp_unfollow_bnt" style="display:none;">已关注&nbsp;|&nbsp;取消</a>\
            <a href="javascript:;" class="sp_follow_bnt sp_unfollow_bnt" style="display:none;">相互关注&nbsp;|&nbsp;取消</a>\
           </div>' 
        + '<b class="sp_caret sp_caret_out"></b>' 
        + '<b class="sp_caret sp_caret_in"></b>' 
        + '</div>' 
        + '</div>';
        $(html).appendTo("body");
    }
    var $card = $(".sp_card");
    var v = $(".sp_card .sp_card_view"),
        uci = $(".sp_card .sp_card_intro");
    var w = $(".sp_card .sp_card_medal"),
        ucf = $(".sp_card .sp_card_follow"),
        ucl = $(".sp_card .sp_card_loading");
    var $sp_caret = $card.find(".sp_caret");
    var closeCardTimer = null;
    var loadTimer = null;

    //渲染
    function bindCardInfo(res) {
        if (res.status) {
            //设置头像，用户名等
            v.find("dt > a").attr("href", _config['domainSite'] + res.uid + '/').children("img").attr("src", res.avatar);
            //基本统计信息
            var siteHtml = '<a target="_blank" href="' + _config['domainSite'] + res.uid + '/" style="display:inline;float:left;font-weight: bold;">' + res.nickname + '</a><br />关注&nbsp;' + '<a target="_blank" href="' + _config['domainSite'] + res.uid + '/following/1/">' + res.following_num + '</a>&nbsp;&nbsp;|&nbsp;&nbsp;粉丝&nbsp;<a target="_blank" href="' + _config['domainSite'] + res.uid + '/fans/1/">' + res.fans_num + '</a>&nbsp;&nbsp;|&nbsp;&nbsp;';
            siteHtml += '分享&nbsp;<a target="_blank" href="' + _config['domainSite'] + res.uid + '/dance/1/">' + res.dance_num + '</a>';
            v.find("dd").html(siteHtml);
            //加载介绍
            uci.html(res.about != null && res.about.length > 0 ? res.about : "暂无介绍...");
            //勋章处理
            var medalHtml = "";
            for (var n in res.medal) //遍历数组中的每一项
            {
                medalHtml += "<li><a href='javascript:;' title='" + res.medal[n]['title'] + "' onclick=\"openWebsite.openTo(\'" + n + "\');\">";
                if (res.medal[n]['display'] != 0) {
                    medalHtml += "<em class='" + n + "'></em>";
                } else {
                    medalHtml += "<em class='" + n + "_none'></em>";
                }
                if (res.medal[n]['role'] != 0) {
                    if (n == "sign") {
                        if (res.medal[n]['role'] > 9) {
                            //medalHtml += "<b class='num n"+res.medal[n]['role']+"n'></b>";
                            medalHtml += "<b class='num n9n'></b>";
                        } else {
                            medalHtml += "<b class='num n" + res.medal[n]['role'] + "'></b>";
                        }
                    } else {
                        medalHtml += "<b class='num n" + res.medal[n]['role'] + "'></b>";
                    }
                }
                medalHtml += "</a></li>";
            }
            //w.find("ul").html(medalHtml);

            //是否已经关注
            if(res.follow[res.uid] == 1 || res.follow[res.uid] == 2){
                ucf.find("a").hide().eq(1).show();
            } else {
                //显示未关注
                ucf.find("a").hide().eq(0).show();
            }
            ucf.find("span").html("");
            //隐藏加载样式
            ucl.hide();
            //显示名片
            v.show();
            //显示介绍
            uci.show();
            //显示勋章
            w.show();
            //显示是否关注
            ucf.show().find("a").attr("uid", res.uid).attr("nickname", res.nickname);
        } else {
            //隐藏名片
            $card.hide();
        }
    }

    $(".user_card").live({
        "mouseover": function () {
            if (closeCardTimer != null) {
                clearTimeout(closeCardTimer);
                closeCardTimer = null
            }
            if (loadTimer != null) {
                clearTimeout(loadTimer);
                loadTimer = null
            }
            var obj = this;
            loadTimer = setTimeout(function () {
                    ucl.show();
                    v.hide();
                    uci.hide();
                    w.hide();
                    ucf.hide();
                    var objH = $(obj).height();
                    var ucH = 180;
                    var offset = $(obj).offset();
                    var st = 0;
                    var sl = 0;
                    if (document.documentElement) {
                        st = parseInt(document.documentElement.scrollTop);
                        sl = parseInt(document.documentElement.scrollLeft)
                    }
                    if (st <= 0 && document.body) {
                        st = parseInt(document.body.scrollTop)
                    }
                    if (sl <= 0 && document.body) {
                        sl = parseInt(document.body.scrollLeft)
                    }
                    var g = offset.top - st;
                    var h = $(window).height() - offset.top + st - $(obj).height();
                    var i = offset.left;
                    var j = $(window).width() + sl - offset.left;
                    //alert($(window).width() +'    '+ sl +'   '+ $(obj).width());
                    //alert(offset.left+'    '+offset.right);
                    var k = 0;
                    if (j < 350) {
                        k = 350 - j
                    }
                    var objHalfW = parseInt($(obj).width() / 2);
                    if (isNaN(objHalfW)) objHalfW = 28;
                    if (g < ucH && h >= ucH) {
                        $sp_caret.eq(0).css({
                            "border-width": "0 8px 7px 7px",
                            "bottom": (ucH - 2) + "px",
                            "left": (objHalfW - 8 + k) + "px",
                            "border-color": "transparent transparent #CFCFCF transparent",
                            "border-style": "dashed dashed solid dashed"
                        });
                        $sp_caret.eq(1).css({
                            "border-width": "0 6px 7px 6px",
                            "bottom": (ucH - 2) + "px",
                            "left": (objHalfW - 7 + k) + "px",
                            "border-color": "transparent transparent #fff transparent",
                            "border-style": "dashed dashed solid dashed"
                        });
                        $card.css({
                            left: offset.left - k,
                            top: offset.top + objH + 8
                        }).show()
                    } else {
                        $sp_caret.eq(0).css({
                            "border-width": "8px 7px 0 7px",
                            "bottom": "-8px",
                            "left": (objHalfW - 8 + k) + "px",
                            "border-color": "#CFCFCF transparent transparent transparent",
                            "border-style": "solid dashed dashed dashed"
                        });
                        $sp_caret.eq(1).css({
                            "border-width": "7px 6px 0 6px",
                            "bottom": "-7px",
                            "left": (objHalfW - 7 + k) + "px",
                            "border-color": "#fff transparent transparent transparent",
                            "border-style": "solid dashed dashed dashed"
                        });
                        $card.css({
                            left: offset.left - k,
                            top: offset.top - ucH - 8
                        }).show()
                    }
                    var uid = $(obj).attr("uid");
                    if (typeof uid == "undefined") {
                        return false;
                    } else {
                        var cardInfo = $("body").data("userCard" + uid);
                        if (typeof cardInfo == "undefined" || cardInfo == 0) {
                            $.getJSON(_config['domainSite'] + "index.php?g=Member&m=Public&a=fetchusercard&callback=?", {
                                    uid: uid
                                },
                                function (res) {

                                    $("body").data("userCard" + res.uid, res);
                                    $(obj).attr("uid", res.uid);
                                    bindCardInfo(res)
                                })
                        } else {
                            bindCardInfo(cardInfo)
                        }
                    }
                },
                800);
        },
        "mouseout": function () {
            closeCardTimer = setTimeout(function () {
                    $card.hide();
                    if (loadTimer != null) {
                        clearTimeout(loadTimer);
                        loadTimer = null
                    }
                },
                400);
        }
    });

    $card.hover(function () {
            if (closeCardTimer != null) {
                clearTimeout(closeCardTimer);
                closeCardTimer = null
            }
        },
        function () {
            closeCardTimer = setTimeout(function () {
                    $card.hide();
                    if (loadTimer != null) {
                        clearTimeout(loadTimer);
                        loadTimer = null
                    }
                },
                400)
        });
    //绑定关注事件
    ucf.find("a").each(function (i, k) {
        var command = "";
        var callback = function () {};
        switch (i) {
        case 0:
            command = "fansAddDialog";
            callback = function (data) {
                var uid = $(k).attr("uid");
                var $fans = $('#fans');
                var nickname = $(k).attr("nickname");
                if (data["error"] == 20001) {
                    user.userNotLogin('您未登录无法执行此操作！');
                } else {
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
                                        $("body").data("userCard" + uid, 0);
                                        $.tipMessage('您成功关注了 <strong>'+nickname+'</strong> ！', 0, 3000);
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
            };
            break;
        case 1:
        case 2:
            {
                command = "doFansDel";
                callback = function (data) {
                    var uid = $(k).attr("uid");
                    var nickname = $(k).attr("nickname");
                    if (data['error'] == 20001) {
                        user.userNotLogin('您未登录无法执行此操作！');
                    } else if (data['error'] == 10004) {
                        $.tipMessage('您不是 <strong>' + nickname + '</strong> 的粉丝!', 1, 3000);
                    } else if (data['error'] == 10013) {
                        $.tipMessage('您只能关注别人，不能关注自己哦！', 1, 3000);
                    } else {
                        $("body").data("userCard" + uid, 0);
                        $.tipMessage('您成功取消了对 <strong>' + nickname + '</strong> 的关注!', 0, 3000);

                    }
                };
                break;
            }
        default:
            break;
        }
        //执行关注
        if (command == "fansAddDialog") {
            $(k).click(function () {
                var uid = $(this).attr("uid");

                if (uid && uid.length > 0) {
                    $.getJSON(_config['domainSite'] + "index.php?g=Member&m=Relation&a=fansAdd", {
                            uid: uid
                        },
                        callback)
                }
                return false;
            })
        } else if (command == "doFansDel") {//取消关注
            $(k).click(function () {
                var nickname = $(this).attr("nickname");
                var uid = $(this).attr("uid");
                $.dialog({
                    id: 'friendDel12',
                    title: '取消关注',
                    width: '340px',
                    lock: true,
                    content: '<br/>你确定取消对 <strong>' + nickname + '</strong>的关注吗？<br/><br/><span style="color: #999999;">提示：取消关注后您将再也不能收到他的新鲜事。</span><br/><br/>',
                    okValue: '确认',
                    ok: function () {
                        if (uid && uid.length > 0) {
                            $.post(_config['domainSite'] + "index.php?g=Member&m=Relation&a=followingdel", {
                                    'friend_uid': uid
                                },
                                callback)
                        }
                    },
                    cancelValue: '取消',
                    cancel: function () {

                    }
                });
            })
        }
    })
});