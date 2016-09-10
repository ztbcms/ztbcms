var miniblogLib = {
    miniblogTimer: 0,
    //发表微博
    miniblogAddInit: function () {
        var $note = $("#note");
        var noteContent = "发一条说说, 让大家知道你在做什么...";
        $note.emotEditor({
            emot: true,
            charCount: true,
            defaultText: noteContent,
            defaultCss: 'default_color'
        });
        $("#send").click(function () {
            var $miniblogMessage = $('#miniblogMessage'); //显示提示信息
            var $miniblogList = $('#miniblogList'); //微博列表
            var validCharLength = $note.emotEditor("validCharLength");
            if (validCharLength < 1 || $note.emotEditor("content") == "") {
                $miniblogMessage.html('请输入您的微博内容！');
                clearTimeout(miniblogLib.miniblogTimer);
                miniblogLib.miniblogTimer = setTimeout(function () {
                    $miniblogMessage.html('');
                }, 2000);
                $note.emotEditor("focus");
                return false;
            }
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Miniblog&a=miniblogadd",
                data: {
                    'content': $note.emotEditor("content")
                },
                dataType: "json",
                success: function (data) {
                    if (data['error'] == 10007) {
                        $miniblogMessage.html('请输入您的微博内容！');
                        $note.emotEditor("focus");
                        clearTimeout(miniblogLib.miniblogTimer);
                        miniblogLib.miniblogTimer = setTimeout(function () {
                            $miniblogMessage.html('');
                        }, 2000);
                        return false;
                    } else if (data['error'] == 10006) {
                        $miniblogMessage.html('微博内容不能超过140个字！');
                        $note.emotEditor("focus");
                        clearTimeout(miniblogLib.miniblogTimer);
                        miniblogLib.miniblogTimer = setTimeout(function () {
                            $miniblogMessage.html('');
                        }, 2000);
                        return false;
                    } else if (data['error'] == 20001) {
                        user.userNotLogin('您需要先登录才能进行留言操作!');
                    } else if (data['error'] == 10002) {
                        $miniblogMessage.html('您操作的太频繁，请稍后再试！');
                        $note.emotEditor("focus");
                        clearTimeout(miniblogLib.miniblogTimer);
                        miniblogLib.miniblogTimer = setTimeout(function () {
                            $miniblogMessage.html('');
                        }, 2000);
                        return false;
                    } else if (data['error'] == 10005) {
                        $.tipMessage('三级以上用户才可以发表微博！', 1, 3000);
                        return false;
                    } else if (data['error'] == 10000) {
                        $note.emotEditor("reset");
                        location.href = location.href;
                    } else {
                        $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
                        return false;
                    }
                }
            });
            return false;
        });
    },

    //回复指定用户
    replayUserInit: function () {
        $(".comment").click(function () {
            var authorId = $(this).attr("authorId"); //指定用户id
            var nickname = $(this).attr("nickname"); //制定用户昵称
            $(".replayUser").show();
            $(".dells").show();
            $(".replayUser").html("回复@" + nickname + "[" + authorId + "]");
            $note = $('#note');
            $note.focus();
        });
    },

    //取消回复
    replayUserDelInit: function () {
        $(".dells").click(function () {
            $(".dells").hide();
            $(".replayUser").html("").hide();
            $note = $('#note');
            $note.focus();
        });
    },

    //发表评论或回复
    commentAddInit: function () {
        $("#note").elastic({
            maxHeight: 90
        });
        var num = ""; //评论数量
        $(".send").click(function () {
            var $uid = $("#uid"); //发表者id
            var $bid = $("#bid"); //恢复微博的id
            var $replyNum = $('#replyNum'); //显示评论数量的div
            var $replayUser = $("#replayUser"); //回复框
            var $note = $("#note"); //回复内容
            var $miniblogCommentList = $('#miniblogCommentList'); //回复列表
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Miniblog&a=commentadd",
                data: {
                    uid: $uid.val(),
                    wid: $bid.val(),
                    replayUser: $replayUser.html(),
                    content: $.trim($note.val())
                },
                dataType: "json",
                success: function (data) {
                    if (data['error'] == 10007) {
                        $.tipMessage('请先说点什么吧！', 1, 3000);
                        $note.focus();
                        return false;
                    } else if (data['error'] == 10006) {
                        $.tipMessage('回复内容不能超过140个字！', 1, 3000);
                        $note.focus();
                        return false;
                    } else if (data['error'] == 20001) {
                        user.userNotLogin('您需要先登录才能进行留言操作 ！');
                    } else if (data['error'] == 10002) {
                        $.tipMessage('您操作的太频繁，请稍后再试！', 1, 3000);
                        $note.focus();
                        return false;
                    } else if (data['error'] == 10004) {
                        $.tipMessage("微博已被删除", 1, 3000, 0, function () {
                            location.href = location.href;
                        });
                    } else if (data['error'] == 10000) {
                        $(".dells").hide();
                        $(".replayUser").html("").hide();
                        $note.val('');
                        $.tipMessage("微博评论回复成功！", 0, 2000, 0, function () {
                            location.href = location.href;
                        });
                        //num = $("#nums").attr("num");
                        //$replyNum.html("评论[" + num + "]");
                    } else {
                        $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
                    }
                }
            });
        });
    },

    //删除微博
    miniblogDelInit: function () {
        $(".del").click(function () {
            var $miniblogList = $('#miniblogList'); //微博列表
            var $currPage = $('#currPage'); //当前页码
            var uid = $(this).attr("uid");
            var showType = $('#showType').val(); //前台后台
            var bid = $(this).attr('bid');
            var dialogObj = $.dialog.get('delMiniblogAndComment');
            var kernelType = $("#kernelType").val(); //跟新哪个模版
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
            $.dialog({
                id: 'delMiniblogAndComment',
                title: false,
                border: false,
                follow: $(this)[0],
                content: '确认删除这条博文么？',
                okValue: '确认',
                ok: function () {
                    $.ajax({
                        type: "POST",
                        global: false, // 禁用全局Ajax事件.
                        url: _config['domainSite'] + "index.php?g=Member&m=Miniblog&a=miniblogdel",
                        data: {
                            'id': bid,
                            'showType': showType,
                            'currPage': $currPage.html(),
                            'uid': uid,
                            "kernelType": kernelType
                        },
                        dataType: "json",
                        success: function (data) {
                            if (data['error'] == 20001) {
                                user.userNotLogin('您需要先登录才能进行操作 ！');
                            } else if (data['error'] == 20002) {
                                $.tipMessage('对不起，您没有操作权限', 1, 3000);
                                return false;
                            } else if (data['error'] == 10005) {
                                $.tipMessage('本次操作失败了，请稍后重试', 1, 3000);
                                return false;
                            } else if (data['error'] == 10004) {
                                $.tipMessage("微博已被删除", 1, 3000, 0, function () {
                                    location.href = location.href;
                                });
                            } else if (data['error'] == 10000) {
                                if (showType == 1) {
                                    location.href = location.href; //前台
                                } else {
                                    location.href = location.href; //后台
                                }
                            } else {
                                $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
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

    //删除评论 
    commentDelInit: function () { //删除评论 
        var num = ""; //评论数量
        $(".dell").click(function () {
            var $miniblogCommentList = $('#miniblogCommentList'); //评论列表
            var $replyNum = $('#replyNum'); //显示评论的div
            var bid = $(".del").attr("id"); //所回复的微博id
            cid = $(this).attr('cid');
            var dialogObj = $.dialog.get('delMiniblogAndComment');
            if (typeof dialogObj === 'object') {
                dialogObj.close();
            }
            $.dialog({
                id: 'delMiniblogAndComment',
                title: false,
                border: false,
                follow: $(this)[0],
                content: '确认删除这条评论么？',
                okValue: '确认',
                ok: function () {
                    $.ajax({
                        type: "POST",
                        global: false, // 禁用全局Ajax事件.
                        url: _config['domainSite'] + "index.php?g=Member&m=Miniblog&a=commentdel",
                        data: {
                            'cid': cid,
                            'wid': bid
                        },
                        dataType: "json",
                        success: function (data) {
                            if (data['error'] == 20001) {
                                user.userNotLogin('您需要先登录才能进行操作 ！');
                            } else if (data['error'] == 20002) {
                                $.tipMessage('对不起，您没有操作权限', 1, 3000);
                                return false;
                            } else if (data['error'] == 10005) {
                                $.tipMessage('本次操作失败了，请稍后重试', 1, 3000);
                                return data['error'];
                            } else if (data == 10004) {
                                $.tipMessage("评论已被删除", 1, 3000, 0, function () {
                                    location.href = location.href;
                                });
                            } else if (data['error'] == 10000) {
                                $.tipMessage("评论删除成功", 0, 2000, 0, function () {
                                    location.href = location.href;
                                });
                                //$miniblogCommentList.html(data);
                                //num = $("#nums").attr("num");
                                //$replyNum.html("评论[" + num + "]");
                            } else {
                                $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
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

    //个人中心微博发布
    miniblogHomeAddInit: function () {
        var $note = $("#note"); //微博内容
        var noteContent = "发一条说说, 让大家知道你在做什么...";
        $note.emotEditor({
            emot: true,
            charCount: true,
            defaultText: noteContent,
            defaultCss: 'default_color'
        });
        $(".send").click(function () {
            var validCharLength = $note.emotEditor("validCharLength");
            if (validCharLength < 1 || $note.emotEditor("content") == "") {
                $.tipMessage('请输入您的微博内容！', 1, 3000);
                $note.emotEditor("focus");
                return false;
            }
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Miniblog&a=miniblogadd",
                data: {
                    'content': $note.emotEditor("content")
                },
                dataType: "json",
                success: function (data) {
                    if (data['error'] == 10007) {
                        $.tipMessage('请输入您的微博内容！', 1, 3000);
                        $note.emotEditor("focus");
                        return false;
                    } else if (data['error'] == 10006) {
                        $.tipMessage('微博内容不能超过140个字！', 1, 3000);
                        $note.emotEditor("focus");
                        return false;
                    } else if (data['error'] == 20001) {
                        user.userNotLogin('您需要先登录才能进行操作 ！');
                    } else if (data['error'] == 10002) {
                        $.tipMessage('您操作的太频繁，请稍后再试！', 1, 3000);
                        $note.emotEditor("focus");
                        return false;
                    } else if (data['error'] == 10005) {
                        $.tipMessage('您当前用户组没有发布权限！', 1, 3000);
                        return false;
                    } else if (data['error'] == 10000)  {
                        $note.emotEditor("reset");
                        $.tipMessage('微博发送成功！', 1, 2000,0,function(){
							$("#refresh").click();
						});
                    } else {
                        $.tipMessage(data['info'], 1, 3000,0,function(){if(data['referer']){location.href = data['referer'];}});
                        return false;
                    }
                }
            });
            return false;
        });
    }

}