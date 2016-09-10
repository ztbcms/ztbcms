(function($){
    $.fn.extend({
        insertAtCaret: function(myValue){
            var $t=$(this)[0];
            if (document.selection) {
                this.focus();
                sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            }
            else 
                if ($t.selectionStart || $t.selectionStart == '0') {
                    var startPos = $t.selectionStart;
                    var endPos = $t.selectionEnd;
                    var scrollTop = $t.scrollTop;
                    $t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
                    this.focus();
                    $t.selectionStart = startPos + myValue.length;
                    $t.selectionEnd = startPos + myValue.length;
                    $t.scrollTop = scrollTop;
                }
                else {
                    this.value += myValue;
                    this.focus();
                }
        }
    })  
})(jQuery);
//获取光标位置函数
function getCursortPosition (ctrl) {
    var CaretPos = 0;   // IE Support
    if (document.selection) {
    ctrl.focus ();
        var Sel = document.selection.createRange ();
        Sel.moveStart ('character', -ctrl.value.length);
        CaretPos = Sel.text.length;
    }
    // Firefox support
    else if (ctrl.selectionStart || ctrl.selectionStart == '0')
        CaretPos = ctrl.selectionStart;
    return (CaretPos);
}
(function (e, jQuery) {
    var n = e.document,
        u = n.getElementsByTagName("head")[0] || n.getElementsByTagName("body")[0],
        //初始化方法
        init = {
            version: 20131120,
            DEBUG: false,
            DOMAIN: GV.DIMAUB,
            EMBED_STYLESHEET: "statics/js/comment/css/embed.css?version=" + this.version,
            GET_JSONP: "index.php?g=Comments&m=Index&a=json",
            POST_JSONP: "index.php?g=Comments&m=Index&a=add",
            VERIFYURL: "index.php?g=Api&m=Checkcode&font_size=15&width=100&height=25&type=comment",
            LOAD: 0,
            LOCK: false,
            //分页信息
            cursor: {
                'total': 0, //总信息数
                'pagetotal': 0, //总分页数
                'page': 1, //当前分页
                'size': 20 //每页显示数量
            },
            catid: commentsQuery.catid,
            id: commentsQuery.id,
            //评论设置
            config: {
                'guest': 1, //是否运行游客评论
                'code': 0, //验证码
                'strlength': 300, //评论长度
                'expire': 60, //评论间隔
                'noallow': true //是否允许评论
            },
            //当前登陆用户基本信息
            users: {
                'user_id': 0,
                'name': '',
                'avatar': '',
                'email': ''
            },
            //评论数据
            response: {},
            getComment: function () {
                jQuery.ajax({
                    type: "GET",
                    url: this.DOMAIN + this.GET_JSONP,
                    dataType: "jsonp",
                    jsonp: 'callback',
                    data: {
                        'catid': this.catid,
                        'id': this.id,
                        'page': this.cursor.page,
                        'size': commentsQuery.size //每页显示评论数
                    },
                    success: function (data) {
                        if (data.status) {
                            init.response = data.data.response;
                            init.cursor = data.data.cursor;
                            init.config = data.data.config;
                            //当前登陆用户信息
                            init.users = data.data.users;
                            var username = '游客';
                            var avatar = '';
                            if (init.users.user_id > 0) {
                                username = init.users.name;
                            }
                            if (init.users.avatar == '') {
                                avatar = tool.getAvatar(init.users.user_id, LS.item('coment_author_email'));
                            } else {
                                avatar = init.users.avatar;
                            }
                            init.users.avatar = avatar;
                            init.users.username = username;

                            var thread = jQuery('#ds-reset');
                            if (init.LOAD == 0) {
                                jQuery('#ds-waiting').remove();
                                addModel.commentsInfo(thread);
                            } else {
                                addModel.comments(thread);
                            }
                            init.LOAD += 1;
                            init.binds();
                            if (init.DEBUG) {
                                console.log('LOAD次数', init.LOAD);
                                console.log('评论设置', init.config);
                                console.log('评论数据：', init.response);
                                console.log('分页数据：', init.cursor);
                                console.log('登录用户：', init.users);
                            }
                        }
                    }
                });
            },
            //加载样式
            injectStylesheet: function (e) {
                var t = n.createElement("link");
                t.type = "text/css", t.rel = "stylesheet", t.href = e, u.appendChild(t)
            },
			//加载js
			loadJS:function(id,url){
				var  xmlHttp = null;
				if(window.ActiveXObject)//IE
				{
					try {
						//IE6以及以后版本中可以使用
						xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
					}
					catch (e) {
						//IE5.5以及以后版本可以使用
						xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
					}
				}
				else if(window.XMLHttpRequest)//Firefox，Opera 8.0+，Safari，Chrome
				{
					xmlHttp = new XMLHttpRequest();
				}
				//采用同步加载
				xmlHttp.open("GET",url,false);
				//发送同步请求，如果浏览器为Chrome或Opera，必须发布后才能运行，不然会报错
				xmlHttp.send(null);
				//4代表数据发送完毕
				if ( xmlHttp.readyState == 4 )
				{
					//0为访问的本地，200到300代表访问服务器成功，304代表没做修改访问的是缓存
					if((xmlHttp.status >= 200 && xmlHttp.status <300) || xmlHttp.status == 0 || xmlHttp.status == 304)
					{
						var myHead = document.getElementsByTagName("HEAD").item(0);
						var myScript = document.createElement( "script" );
						myScript.language = "javascript";
						myScript.type = "text/javascript";
						myScript.id = id;
						try{
							//IE8以及以下不支持这种方式，需要通过text属性来设置
							myScript.appendChild(document.createTextNode(xmlHttp.responseText));
						}
						catch (ex){
							myScript.text = xmlHttp.responseText;
						}
						myHead.appendChild( myScript );
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					return false;
				}
			},
            //加载分页
            goPage: function () {
                var totalPage = this.cursor.pagetotal; //总页数
                var pageSize = this.cursor.size; //每页显示行数
                var currentPage = this.cursor.page; //当前页数
                var str = '';
                var strHighlight = 'class="ds-current"';
                if (currentPage > totalPage) {
                    currentPage = totalPage;
                }
                if (currentPage < 1) {
                    currentPage = 1;
                }
                //默认显示当前分页前两个
                var cPage = currentPage - 2;
                if (cPage > 1) {
                    //if(cPage > 2){
                    str = '<a data-page="1" href="javascript:void(0);">1</a> ';
                    //}
                    if (cPage > 2) {
                        str += '<span class="page-break">...</span>';
                    }
                    for (var i = cPage; i < currentPage; i++) {
                        str += '<a data-page="' + i + '" href="javascript:void(0);" >' + i + '</a> ';
                    }
                } else {
                    for (var i = 1; i < currentPage; i++) {
                        str += '<a data-page="' + i + '" href="javascript:void(0);">' + i + '</a> ';
                    }
                }
                //当前页
                str += '<a data-page="' + currentPage + '" href="javascript:void(0);" ' + strHighlight + '>' + currentPage + '</a> ';
                //显示后两个
                var hPage = currentPage + 2;
                if (totalPage >= hPage) {
                    for (var i = currentPage + 1; i <= hPage; i++) {
                        str += '<a data-page="' + i + '" href="javascript:void(0);">' + i + '</a> ';
                    }
                    if (totalPage > currentPage + 2) {
                        if (totalPage - hPage >= 2) {
                            str += '<span class="page-break">...</span>';
                        }
                        str += '<a data-page="' + totalPage + '" href="javascript:void(0);">' + totalPage + '</a> ';
                    }
                } else {
                    for (var i = currentPage + 1; i <= totalPage; i++) {
                        str += '<a data-page="' + i + '" href="javascript:void(0);">' + i + '</a> ';
                    }

                }
                return str;
            },
            //绑定各种事件
            binds: function () {
                //允许评论
                if (init.config.noallow) {
                    //对回复按钮绑定点击事件
                    jQuery('a.ds-post-reply').bind('click', function () {
                        var reply = jQuery(this);
                        var replyparent = reply.parent();
                        var replyactive = replyparent.next();
                        var comentid = reply.data('comentid');
                        jQuery('.ds-post .ds-replybox').hide();
                        jQuery('.ds-post .ds-comment-footer').removeClass('ds-reply-active');
                        //回复 高亮
                        replyparent.addClass('ds-reply-active');
                        //载入回复评论框
                        if (replyactive.html() == '') {
                            replyactive.html(addModel.replybox());
                        }
                        replyactive.show();
                        //加载登陆信息
                        addModel.getUser();
                        init.ajaxButton();
                        //设置回复id
                        replyactive.children("form").find('input[name="parent"]').attr('value', comentid);
                        tool.localStorages();
                        //表情
                        emote.init();
                    });
                    jQuery('a.ds-ReplyHide').bind('click', function () {
                        addModel.commetnReplyHide(jQuery(this).data("comentid"));
                    });
                    //表情
                    emote.init();
                    //加载登陆信息
                    addModel.getUser();
                    init.ajaxButton();
                } else {
                    jQuery('a.ds-post-reply').hide();
                }
                //记录用户输入
                tool.localStorages();
            },
            //ajax 提交
            ajaxButton: function () {
                var ajaxForm_list = jQuery('form.ds_form_post');
                if (ajaxForm_list.length) {
                    jQuery('button.ds-post-button').on('click', function (es) {
                        es.preventDefault();
                        var btn = jQuery(this),
                            form = btn.parents('form.ds_form_post');

                        //ie处理placeholder提交问题
                        if (jQuery.browser.msie) {
                            form.find('[placeholder]').each(function () {
                                var input = jQuery(this);
                                if (input.val() == input.attr('placeholder')) {
                                    input.val('');
                                }
                            });
                        }
                        LS.item('coment_author_url', form.find('input[name="author_url"]').val());
                        LS.item('coment_author', form.find('input[name="author"]').val());
                        LS.item('coment_author_email', form.find('input[name="author_email"]').val());
                        if (init.DEBUG) {
                            console.log('提交信息', form);
                        }
                        if (init.LOCK) {
                            return;
                        }
                        init.LOCK = true;
                        form.ajaxSubmit({
                            url: init.DOMAIN + init.POST_JSONP, //按钮上是否自定义提交地址(多按钮情况)
                            dataType: 'json',
                            beforeSubmit: function (arr, $form, options) {
                                var text = btn.text();
                                //按钮文案、状态修改
                                btn.text(text + '中...').prop('disabled', true).addClass('disabled');
                            },
                            success: function (data, statusText, xhr, $form) {
                                if(init.DEBUG){
                                    console.log('提交后服务器返回',data);
                                }
                                var text = btn.text();
                                init.LOCK = false;
                                //按钮文案、状态修改
                                btn.removeClass('disabled').text(text.replace('中...', '')).parent().find('span').remove();
                                if (data.status > 0) {
                                    btn.removeProp('disabled').removeClass('disabled');
                                    jQuery('textarea[name="content"]').val('')
                                    //重新加载数据
                                    init.getComment();
                                } else if (data.status == -1) {
                                    btn.removeProp('disabled').removeClass('disabled');
                                    jQuery('textarea[name="content"]').val('')
                                    alert(data.info);
                                }else{
                                    btn.removeProp('disabled').removeClass('disabled');
                                    alert(data.info);
                                }
                                //焦点
                                if(data.focus){
                                    form.find('input[name="'+data.focus+'"]').focus();
                                }
                            }
                        });
                    });
                }
            },
            htmls: function () {
                var thread = jQuery('#ds-reset');
                thread.append('<div id="ds-waiting"></div>');
                this.getComment();
            }
        },
        //表情处理
        emote = {
            init:function(){
                //点击表情后隐藏
                jQuery("#ds-smilies-tooltip").hide();
                var ts = this;
                //表情
                jQuery('a.ds-add-emote').bind('click',function(event){
                    ts.unbindclick();
                    //加载表情
                    ts.htmls();
                    //显示表情
                    jQuery("#ds-smilies-tooltip").show();
                    jQuery(document).one("click", function () {//对document绑定一个影藏Div方法
                        ts.unbindclick();
                        jQuery("#ds-smilies-tooltip").hide();
                    });
                    jQuery("#ds-smilies-tooltip").click(function (ev) {
                        ev.stopPropagation();
                    });
                    var emote = jQuery(this);
                    var winheight = jQuery(e).height();
                    var top = emote.offset().top - 236;
                    var left = emote.offset().left - 10;//按钮的位置左边距离
                    //表单对象
                    var form = emote.parents('.ds_form_post');
                    jQuery('#ds-reset #ds-smilies-tooltip').offset({ top: top, left: left });
                    jQuery(".ds-smilies-container img").bind('click',function(es){
                        var title = jQuery(this).attr('title');
                        form.find('textarea[name="content"]').insertAtCaret(title);
                        //点击表情后隐藏
                        jQuery("#ds-smilies-tooltip").hide();
                        ts.unbindclick();
                    });
                    event.stopPropagation();
                });
            },
            //去除原来绑定的click事件
            unbindclick:function(){
                jQuery(".ds-smilies-container img").unbind('click');
            },
            htmls:function(){
                if(jQuery('#ds-reset #ds-smilies-tooltip').length){
                    return;
                }
                var reset = jQuery("#ds-reset");
                var emote = '';
                var strhtml = '<div id="ds-smilies-tooltip" style="width: 600px;">\
				<h2>选择表情</h2>\
                                  <div class="ds-smilies-container">\
                                    <ul>Loading...</ul>\
                                  </div>\
								  <div id="ds-foot5">&nbsp;&nbsp;&nbsp; </div>\
								 </div>';
                reset.append(strhtml);
                jQuery.ajax({
                    type: "GET",
                    async:false,
                    url: init.DOMAIN + 'index.php?g=Comments&m=Index&a=json_emote',
                    dataType: "jsonp",
                    jsonp: 'callback',
                    success: function (json) {
                        if(json.data){
                            jQuery.each(json.data, function (lab, img) {
                                emote += '<li>'+img+'</li>';
                            })
                            jQuery('.ds-smilies-container ul').html(emote);
                        }
                    }
                });
            }
        },
        addModel = {
            //加载导航
            commentsInfo: function (thread) {
				//显示评论框
                this.newsCommentBox(thread);
                //显示评论数，和 最新，最早，最热排序导航
                thread.append('<div class="ds-comments-info">\
                                <div class="ds-sort" style="display:none;"><a class="ds-order-desc ds-current">最新</a><a class="ds-order-asc">最早</a><a class="ds-order-hot">最热</a></div>\
                                <span class="ds-comment-count"><a class="ds-comments-tab-duoshuo ds-current" href="javascript:void(0);"><span class="ds-highlight">0</span>条评论</a></span> \
                               </div>');
                //显示评论数
                jQuery('.ds-highlight').html(init.cursor.total);
                //加载评论
                this.comments(thread);
            },
            //加载评论
            comments: function (thread) {
                var post;
                //如果非第一次加载，不append插入元素
                if (init.LOAD) {
                    jQuery('.ds-comments').empty();
                } else {
                    thread.append('<ul class="ds-comments" style="opacity: 1; "></ul>');
                }
                post = jQuery('.ds-comments');
                //判断是否为空
                if (init.response == null || init.response == '') {
                    post.append('<li class="ds-post ds-post-placeholder">还没有评论，沙发等你来抢</li>');
                    //加载分页
                    this.paginator(thread);
                    return;
                }
                jQuery.each(init.response, function (i, rs) {
                    var comentId = rs.id;
                    if (rs.content) {
                        post.append('<li class="ds-post">\
                                      <div class="ds-post-self">\
                                          <div class="ds-avatar">\
                                            <a rel="nofollow author" href="javascript:;;" title="' + rs.author + '"><img src="' + tool.getAvatar(rs.user_id, rs.author_email) + '" alt="' + rs.author + '" onerror="this.src=\'' + init.DOMAIN + 'statics/images/member/nophoto.gif\'"></a>\
                                          </div>\
                                          <div class="ds-comment-body">\
                                            <div class="ds-comment-header"><a class="ds-user-name ds-highlight" href="javascript:;;" rel="nofollow" data-userid="' + rs.user_id + '">' + rs.author + '</a></div>\
                                            <p>' + rs.content + '</p>\
                                            <div class="ds-comment-footer ds-comment-actions"> \
                                                <span class="ds-time" title="' + tool.getYearsMonthDay(rs.date * 1000,"yyyy-MM-dd hh:mm:ss") + '">' + tool.getTimeBefore(rs.date * 1000) + '</span> \
                                                <a class="ds-post-reply" href="javascript:void(0);" data-comentid="' + comentId + '"><span class="ds-ui-icon"></span>回复</a> \
                                                <a class="ds-post-likes" style="display:none;" href="javascript:void(0);" data-comentid="' + comentId + '"><span class="ds-ui-icon"></span>顶</a> \
                                            </div>\
                                            <div class="ds-replybox ds-inline-replybox replybox_' + comentId + '" style="display:none;"></div>\
                                          </div>\
                                      </div>\
                                    </li>');
                        //加载回复列表
                        if (rs.child) {
                            addModel.commetnReply(rs);
                        }
                    }
                });
                //加载分页
                this.paginator(thread);
                return true;
            },
            //加载评论回复
            commetnReply: function (json) {
                var post = jQuery('.ds-comments');
                var strHtml = '';
                //加载回复
                if (json.child) {
                    jQuery.each(json.child, function (i, rs) {
                        var comentId = rs.id;
                        if (rs.display == 'none') {
                            strHtml += '<li class="ds-post">\
                                            <div class="ds-post-self">\
                                              <div class="ds-comment-body">\
                                                <p>已经省略一部分评论...<a href="javascript:;;" class="ds-ReplyHide" data-comentid="' + json.id + '">全部加载</a></p>\
                                              </div>\
                                            </div>\
                                          </li>';
                        } else {
                            strHtml += '\
                                  <li class="ds-post">\
                                    <div class="ds-post-self">\
                                      <div class="ds-avatar"><a rel="nofollow author" href="javascript:;;" title="' + rs.author + '"><img src="' + tool.getAvatar(rs.user_id, rs.author_email) + '" alt="' + rs.author + '"></a></div>\
                                      <div class="ds-comment-body">\
                                        <div class="ds-comment-header">\
                                            <a class="ds-user-name ds-highlight" data-qqt-account="" href="javascript:;;" rel="nofollow" data-userid="' + rs.user_id + '">' + rs.author + '</a>\
                                        </div>\
                                        <p>' + rs.content + '</p>\
                                        <div class="ds-comment-footer ds-comment-actions"> \
                                            <span class="ds-time" title="' + tool.getYearsMonthDay(rs.date * 1000,"yyyy-MM-dd hh:mm:ss") + '">' + tool.getTimeBefore(rs.date * 1000) + '</span> \
                                            <a class="ds-post-reply" href="javascript:void(0);" data-comentid="' + json.id + '"><span class="ds-ui-icon"></span>回复</a> \
                                            <a class="ds-post-likes" style="display:none;" href="javascript:void(0);" data-comentid="' + comentId + '"><span class="ds-ui-icon"></span>顶</a> \
                                        </div>\
                                        <div class="ds-replybox ds-inline-replybox replybox_' + comentId + '"></div>\
                                      </div>\
                                    </div>\
                                  </li>';
                        }
                    });
                    post.append('<ul class="ds-children" id="commetnReply_' + json.id + '">' + strHtml + '</ul>');
                }
            },
            //加载隐藏部分的评论
            commetnReplyHide: function (comentid) {
                if (comentid == '' || comentid == null) {
                    return;
                }
                var commetnReply = jQuery("#commetnReply_" + comentid);
                var strHtml = '';
                commetnReply.empty();
                jQuery.ajax({
                    type: "GET",
                    url: init.DOMAIN + 'index.php?g=Comments&m=Index&a=json_reply&parent=103',
                    dataType: "jsonp",
                    jsonp: 'callback',
                    data: {
                        'parent': comentid
                    },
                    success: function (data) {
                        if (data.status) {
                            //加载回复
                            if (data.data.response) {
                                jQuery.each(data.data.response, function (i, rs) {
                                    var comentId = rs.id;
                                    strHtml += '<li class="ds-post">\
                                    <div class="ds-post-self">\
                                      <div class="ds-avatar"><a rel="nofollow author" href="javascript:;;" title="' + rs.author + '"><img src="' + tool.getAvatar(rs.user_id, rs.author_email) + '" alt="' + rs.author + '"></a></div>\
                                      <div class="ds-comment-body">\
                                        <div class="ds-comment-header">\
                                            <a class="ds-user-name ds-highlight" data-qqt-account="" href="javascript:;;" rel="nofollow" data-userid="' + rs.user_id + '">' + rs.author + '</a>\
                                        </div>\
                                        <p>' + rs.content + '</p>\
                                        <div class="ds-comment-footer ds-comment-actions"> \
                                            <span class="ds-time" title="' + tool.getYearsMonthDay(rs.date * 1000,"yyyy-MM-dd hh:mm:ss") + '">' + tool.getTimeBefore(rs.date * 1000) + '</span> \
                                            <a class="ds-post-reply" href="javascript:void(0);" data-comentid="' + comentid + '"><span class="ds-ui-icon"></span>回复</a> \
                                            <a class="ds-post-likes" style="display:none;" href="javascript:void(0);" data-comentid="' + rs.id + '"><span class="ds-ui-icon"></span>顶</a> \
                                        </div>\
                                        <div class="ds-replybox ds-inline-replybox replybox_' + rs.id + '"></div>\
                                      </div>\
                                    </div>\
                                  </li>';
                                });
                                commetnReply.append(strHtml);
                            }
                        }
                    }
                });
            },
            //分页处理
            paginator: function (thread) {
                //如果有存在分页才载入
                if (init.cursor.pagetotal > 1) {
                    if (init.LOAD) {
                        jQuery('.ds-paginator').empty();
                        jQuery('.ds-paginator').append('<div class="ds-border"></div>' + init.goPage());
                    } else {
                        thread.append('<div class="ds-paginator" style="">\
                                          <div class="ds-border"></div>\
                                       </div>\
                                       <a name="respond"></a>');
                        jQuery('.ds-paginator').append(init.goPage());
                    }
                    //对分页加点击事件
                    jQuery('.ds-paginator a').click(function () {
                        init.cursor.page = jQuery(this).html();
                        jQuery(this).die("click");
                        init.getComment();
                    });
                }
            },
            //回复评论框
            replybox: function () {
                return '<form class="ds_form_post" method="post">\
                            <div class="ds-user"></div>\
                            <a class="ds-avatar" href="javascript:;;"><img src="' + init.users.avatar + '" alt="' + init.users.username + '" onerror="this.src=\'' + init.DOMAIN + 'statics/images/member/nophoto.gif\'"></a>\
                                <input  type="hidden" name="comment_catid" value="' + init.catid + '" />\
                                <input  type="hidden" name="comment_id" value="' + init.id + '" />\
                                <input  type="hidden" name="parent" value="" />\
                                <div class="ds-textarea-wrapper ds-rounded-top">\
                                    <textarea name="content" placeholder="说点什么吧…"></textarea>\
                                </div>\
                                <div class="ds-post-toolbar">\
                                    <div class="ds-post-options ds-gradient-bg"></div>\
                                    <button class="ds-post-button" type="submit">发布</button>\
                                    <div class="ds-toolbar-buttons"><a class="ds-toolbar-button ds-add-emote" title="插入表情"></a></div>\
                                </div>\
                            </form>';
            },
            //发表评论框
            newsCommentBox: function (thread) {
                if (init.LOAD) {
                    return true;
                }
                //关闭评论
                if (init.config.noallow == false) {
                    return true;
                }
                thread.append('<div class="ds-replybox" style="zoom:1;">\
                                <form class="ds_form_post" method="post">\
                                  <div class="ds-user"></div>\
                                  <a class="ds-avatar" href="javascript:;;"><img src="' + init.users.avatar + '" alt="' + init.users.username + '" onerror="this.src=\'' + init.DOMAIN + 'statics/images/member/nophoto.gif\'"></a>\
                                  <input  type="hidden" name="comment_catid" value="' + init.catid + '" />\
                                  <input  type="hidden" name="comment_id" value="' + init.id + '" />\
                                  <div class="ds-textarea-wrapper ds-rounded-top">\
                                    <textarea class="J_CmFormField" name="content" placeholder="说点什么吧…"></textarea>\
                                  </div>\
                                  <div class="ds-post-toolbar">\
                                    <div class="ds-post-options ds-gradient-bg"></div>\
                                    <button class="ds-post-button" type="submit">发布</button>\
                                    <div class="ds-toolbar-buttons"><a class="ds-toolbar-button ds-add-emote" title="插入表情"></a></div>\
                                  </div>\
                                </form>\
                              </div>');
            },
            //获取用户登陆信息或者显示输入框
            getUser: function () {
                var strHtml = '';
                var nichengHtml = '<input name="author" placeholder="用户名" value="' + init.users.username + '"/>';
                var emailHtml = '<input name="author_email" placeholder="请输入邮箱" value="' + init.users.email + '"/>';
                var tis = '';
                if (init.users.user_id) {
                    nichengHtml = init.users.username;
                    emailHtml = init.users.email;
                    tis = '尊敬的 ' + init.users.username + '，欢迎你评论！';
                }
                var userHtml = '<tr>\
                                    <td>昵称：</td>\
                                    <td>' + nichengHtml + '</td>\
                                    <td>邮箱：</td>\
                                    <td>' + emailHtml + '</td>\
                                  </tr>';
                var qtHtml = '<tr>\
                                  <td>网址：</td>\
                                    <td><input name="author_url" class="J_CmFormField" placeholder="http://"/></td>\
                                    <td></td>\
                                    <td>' + tis + '</td>\
                                  </tr>';
                if (init.users.user_id) {
                    userHtml = '';
                }
                if (init.config.code == '1') {
                    qtHtml = '<tr>\
                                  <td>网址：</td>\
                                    <td><input name="author_url" class="J_CmFormField" placeholder="http://"/></td>\
                                    <td>验证码：</td>\
                                    <td style="vertical-align:middle"><input name="verify" placeholder="验证码"/><img  id="code_img" src="' + init.DOMAIN + init.VERIFYURL + '"  alt="验证码" onClick="this.src = \'' + init.DOMAIN + init.VERIFYURL + '&refresh=1&time=' + Math.random() + '\'" style="vertical-align: middle ;"></td>\
                                  </tr>';
                }
                strHtml = '<table>' + userHtml + qtHtml + '</table>';
                //检查是否游客允许评论
                if(init.config.guest == 0 && init.users.user_id < 1){
                    strHtml = '游客不允许评论，请登陆后操作！o(∩_∩)o ';
                }
                jQuery('.ds-user').empty().append(strHtml);
            }
        },
        //工具
        tool = {
            //友好时间
            getTimeBefore: function (time) {
                var ret = "";
                var nowd = new Date();
                var now = nowd.getTime();
                var delay = now - time;
                var t = new Date(time);
                var getHours = t.getHours();
                var getMinutes = t.getMinutes();
                if (delay > (10 * 24 * 60 * 60 * 1000)) {
                    ret = tool.getYearsMonthDay(time, "yyyy-MM-dd hh:mm:ss");
                } else if (delay >= (24 * 60 * 60 * 1000)) {
                    delay = (delay / (24 * 60 * 60 * 1000));
                    var num = Math.floor(delay);
                    if (num == 1) {
                        ret = "昨天" + getHours + ":" + getMinutes;
                    } else if (num == 2) {
                        ret = "前天" + getHours + ":" + getMinutes;
                    } else {
                        ret = num + "天前";
                    }
                } else if (delay >= (60 * 60 * 1000)) {
                    delay = (delay / (60 * 60 * 1000))
                    ret = Math.floor(delay) + "小时前";
                } else if (delay >= (60 * 1000)) {
                    delay = (delay / (60 * 1000))
                    ret = Math.floor(delay) + "分钟前";
                } else if (delay >= (1000)) {
                    delay = (delay / (1000))
                    ret = Math.floor(delay) + "秒前";
                } else {
                    ret = "刚刚";
                }

                return ret;
            },
            //获取 年月日的时间格式
            getYearsMonthDay: function (time, format) {
                var dt = new Date(time);
				/*
				 * eg:format="yyyy-MM-dd hh:mm:ss";
				 */
				var o = {
					"M+": dt.getMonth() + 1, // month
					"d+": dt.getDate(), // day
					"h+": dt.getHours(), // hour
					"m+": dt.getMinutes(), // minute
					"s+": dt.getSeconds(), // second
					"q+": Math.floor((dt.getMonth() + 3) / 3), // quarter
					"S": dt.getMilliseconds()// millisecond
				}
				if (/(y+)/.test(format)) {
					format = format.replace(RegExp.$1, (dt.getFullYear() + "").substr(4 - RegExp.$1.length));
				}
				for (var k in o) {
					if (new RegExp("(" + k + ")").test(format)) {
						format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
					}
				}
				return format;
            },
            //获取头像地址
            getAvatar: function (uid, email) {
                if (Math.floor(uid) > 0) {
                    return init.DOMAIN + 'api.php?m=avatar&uid=' + uid;
                } else {
                    return init.DOMAIN + 'api.php?m=avatar&a=gravatar&email=' + email;
                }
            },
            //保存用户输入
            localStorages: function () {
                //记录用户输入
                jQuery('input[name="author_url"]').attr('value', LS.item('coment_author_url'));
                jQuery('input[name="author"]').attr('value', LS.item('coment_author'));
                jQuery('input[name="author_email"]').attr('value', LS.item('coment_author_email'));
                if (init.DEBUG) {
                    console.log('本地存储：', localStorage);
                }
            }
        },
        LS = {
            /**
             * 获取/设置存储字段
             * @param {String} name 字段名称
             * @param {String} value 值
             * @return {String}
             */
            item: function (name, value) {
                var val = null;
                if (LS.isSupportLocalStorage()) {
                    if (value) {
                        localStorage.setItem(name, value);
                        val = value;
                    } else {
                        val = localStorage.getItem(name);
                    }
                } else {
                    //不支持HTML5
                    return;
                }
                return val;
            },
            /**
             * 移除指定name的存储
             * @param {String} name 字段名称
             * @return {Boolean}
             */
            removeItem: function (name) {
                if (LS.isSupportLocalStorage()) {
                    localStorage.removeItem(name);
                } else {
                    //不支持HTML5
                    return false;
                }
                return true;
            },
            /**
             * 判断浏览器是否直接html5本地存储
             */
            isSupportLocalStorage: function () {
                var ls = LS,
                    is = ls.IS_HAS_LOCAL_STORAGE;
                if (is == null) {
                    if (window.localStorage) {
                        is = ls.IS_HAS_LOCAL_STORAGE = true;
                    }
                }
                return is;
            },
            IS_HAS_LOCAL_STORAGE: null
        };
	//判断ajaxForm是否加载
	if($.fn.ajaxSubmit == undefined){
		init.loadJS('ajaxForm',init.DOMAIN +'statics/js/ajaxForm.js');
	}
	//加载样式
    init.injectStylesheet(init.DOMAIN + init.EMBED_STYLESHEET);
    //加载html结构
    init.htmls();
})(window, jQuery);