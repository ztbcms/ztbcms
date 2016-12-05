/**
 * 获取省份
 */
function get_province() {
    var url = '/index.php?g=Shop&m=Api&a=getRegion&level=1&parent_id=0';
    $.ajax({
        type: "GET",
        url: url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option value="0">选择省份</option>' + v;
            $('#province').empty().html(v);
        }
    });
}


/**
 * 获取城市
 * @param t  省份select对象
 */
function get_city(t) {
    var parent_id = $(t).val();
    if (!parent_id > 0) {
        return;
    }
    $('#twon').empty().css('display', 'none');
    var url = '/index.php?g=Shop&m=Api&a=getRegion&level=2&parent_id=' + parent_id;
    $.ajax({
        type: "GET",
        url: url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option value="0">选择城市</option>' + v;
            $('#city').empty().html(v);
        }
    });
}

/**
 * 获取地区
 * @param t  城市select对象
 */
function get_area(t) {
    var parent_id = $(t).val();
    if (!parent_id > 0) {
        return;
    }
    var url = '/index.php?g=Shop&m=Api&a=getRegion&level=3&parent_id=' + parent_id;
    $.ajax({
        type: "GET",
        url: url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = '<option>选择区域</option>' + v;
            $('#district').empty().html(v);
        }
    });
}
// 获取最后一级乡镇
function get_twon(obj) {
    var parent_id = $(obj).val();
    var url = '/index.php?m=Home&c=Api&a=getTwon&parent_id=' + parent_id;
    $.ajax({
        type: "GET",
        url: url,
        success: function(res) {
            if (parseInt(res) == 0) {
                $('#twon').empty().css('display', 'none');
            } else {
                $('#twon').css('display', 'block');
                $('#twon').empty().html(res);
            }
        }
    });
}


/**
 * 输入为空检查
 * @param name '#id' '.id'  (name模式直接写名称)
 * @param type 类型  0 默认是id或者class方式 1 name='X'模式
 */
function is_empty(name, type) {
    if (type == 1) {
        if ($('input[name="' + name + '"]').val() == '') {
            return true;
        }
    } else {
        if ($(name).val() == '') {
            return true;
        }
    }
    return false;
}

/**
 * 邮箱格式判断
 * @param str
 */
function checkEmail(str) {
    var reg = /^[a-z0-9]([a-z0-9\\.]*[-_]{0,4}?[a-z0-9-_\\.]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+([\.][\w_-]+){1,5}$/i;
    if (reg.test(str)) {
        return true;
    } else {
        return false;
    }
}
/**
 * 手机号码格式判断
 * @param tel
 * @returns {boolean}
 */
function checkMobile(tel) {
    var reg = /(^1[3|4|5|7|8][0-9]{9}$)/;
    if (reg.test(tel)) {
        return true;
    } else {
        return false;
    };
}

/*
 * 上传图片 后台专用
 * @access  public
 * @null int 一次上传图片张图
 * @elementid string 上传成功后返回路径插入指定ID元素内
 * @path  string 指定上传保存文件夹,默认存在Public/upload/temp/目录
 * @callback string  回调函数(单张图片返回保存路径字符串，多张则为路径数组 )
 */
function GetUploadify(num, elementid, path, callback) {
    var upurl = '/index.php?g=Shop&m=Uploadify&a=upload&num=' + num + '&input=' + elementid + '&path=' + path + '&func=' + callback;
    var iframe_str = '<iframe frameborder="0" ';
    iframe_str = iframe_str + 'id=uploadify ';
    iframe_str = iframe_str + ' src=' + upurl;
    iframe_str = iframe_str + ' allowtransparency="true" class="uploadframe" scrolling="no"> ';
    iframe_str = iframe_str + '</iframe>';
    $("body").append(iframe_str);
    $("iframe.uploadframe").css("height", $(document).height()).css("width", "100%").css("position", "absolute").css("left", "0px").css("top", "0px").css("z-index", "999999").show();
    $(window).resize(function() {
        $("iframe.uploadframe").css("height", $(document).height()).show();
    });
}

/*
 * 上传图片 前台专用
 * @access  public
 * @null int 一次上传图片张图
 * @elementid string 上传成功后返回路径插入指定ID元素内
 * @path  string 指定上传保存文件夹,默认存在Public/upload/temp/目录
 * @callback string  回调函数(单张图片返回保存路径字符串，多张则为路径数组 )
 */
function GetUploadify2(num, elementid, path, callback) {
    var upurl = '/index.php?m=Home&c=Uploadify&a=upload&num=' + num + '&input=' + elementid + '&path=' + path + '&func=' + callback;
    var iframe_str = '<iframe frameborder="0" ';
    iframe_str = iframe_str + 'id=uploadify ';
    iframe_str = iframe_str + ' src=' + upurl;
    iframe_str = iframe_str + ' allowtransparency="true" class="uploadframe" scrolling="no"> ';
    iframe_str = iframe_str + '</iframe>';
    $("body").append(iframe_str);
    $("iframe.uploadframe").css("height", $(document).height()).css("width", "100%").css("position", "absolute").css("left", "0px").css("top", "0px").css("z-index", "999999").show();
    $(window).resize(function() {
        $("iframe.uploadframe").css("height", $(document).height()).show();
    });
}
/*
 * 删除组图input
 * @access   public
 * @val  string  删除的图片input
 */
function ClearPicArr(val) {
    $("li[rel='" + val + "']").remove();
    $.get(
        "{:U('Shop/Uploadify/delupload')}", { action: "del", filename: val },
        function() {}
    );
}
/*
 * 删除组图input
 * @access   public
 * @val  string  删除的图片input
 */
function ClearPicArr2(val) {
    $("li[rel='" + val + "']").remove();
    $.get(
        "{:U('Home/Uploadify/delupload')}", { action: "del", filename: val },
        function() {}
    );
}


// 点击收藏商品
function collect_goods(goods_id) {
    $.ajax({
        type: "GET",
        dataType: "json",
        url: "/index.php?m=Home&c=goods&a=collect_goods&goods_id=" + goods_id, //+tab,
        success: function(data) {
            alert(data.msg);
        }
    });
}


// 获取活动剩余天数 小时 分钟
//倒计时js代码精确到时分秒，使用方法：注意 var EndTime= new Date('2013/05/1 10:00:00'); //截止时间 这一句，特别是 '2013/05/1 10:00:00' 这个js日期格式一定要注意，否则在IE6、7下工作计算不正确哦。
//js代码如下：
function GetRTime(end_time) {
    // var EndTime= new Date('2016/05/1 10:00:00'); //截止时间 前端路上 http://www.51xuediannao.com/qd63/
    var EndTime = new Date(end_time); //截止时间 前端路上 http://www.51xuediannao.com/qd63/
    var NowTime = new Date();
    var t = EndTime.getTime() - NowTime.getTime();
    /*var d=Math.floor(t/1000/60/60/24);
    t-=d*(1000*60*60*24);
    var h=Math.floor(t/1000/60/60);
    t-=h*60*60*1000;
    var m=Math.floor(t/1000/60);
    t-=m*60*1000;
    var s=Math.floor(t/1000);*/

    var d = Math.floor(t / 1000 / 60 / 60 / 24);
    var h = Math.floor(t / 1000 / 60 / 60 % 24);
    var m = Math.floor(t / 1000 / 60 % 60);
    var s = Math.floor(t / 1000 % 60);
    if (s >= 0)
        return d + '天' + h + '小时' + m + '分' + s + '秒';
}


/**
 * 获取多级联动的商品分类
 */
function get_category(id, next, select_id) {
    var url = '/index.php?g=Shop&m=Api&a=get_category&parent_id=' + id;
    $.ajax({
        type: "GET",
        url: url,
        error: function(request) {
            alert("服务器繁忙, 请联系管理员!");
            return;
        },
        success: function(v) {
            v = "<option value='0'>请选择商品分类</option>" + v;
            $('#' + next).empty().html(v);
            (select_id > 0) && $('#' + next).val(select_id); //默认选中
        }
    });
}

// 读取 cookie
function getCookie(c_name) {
    if (document.cookie.length > 0) {
        c_start = document.cookie.indexOf(c_name + "=")
        if (c_start != -1) {
            c_start = c_start + c_name.length + 1
            c_end = document.cookie.indexOf(";", c_start)
            if (c_end == -1) c_end = document.cookie.length
            return unescape(document.cookie.substring(c_start, c_end))
        }
    }
    return "";
}

function setCookies(name, value, time) {
    var cookieString = name + "=" + escape(value) + ";";
    if (time != 0) {
        var Times = new Date();
        Times.setTime(Times.getTime() + time);
        cookieString += "expires=" + Times.toGMTString() + ";"
    }
    document.cookie = cookieString;
}

/**
 * 获取地址栏的推荐人id 写入cookie
 * 使用这个方法必须先导入 jqueryUrlGet.js
 */
function set_first_leader() {
    // 获取地址栏 分销推广链接id 将推荐人id 存入cookie
    var get_parameters = $.urlGet(); //获取URL的Get参数		  
    var first_leader = parseInt(get_parameters['first_leader']); //取得first_leader的值
    if (first_leader > 0) { // 将推荐人id 存入cookie			
        setCookies('first_leader', first_leader);
    }
}
// 判断是否手机浏览器
function isMobileBrowser() {
    var sUserAgent = navigator.userAgent.toLowerCase();
    var bIsIpad = sUserAgent.match(/ipad/i) == "ipad";
    var bIsIphoneOs = sUserAgent.match(/iphone os/i) == "iphone os";
    var bIsMidp = sUserAgent.match(/midp/i) == "midp";
    var bIsUc7 = sUserAgent.match(/rv:1.2.3.4/i) == "rv:1.2.3.4";
    var bIsUc = sUserAgent.match(/ucweb/i) == "ucweb";
    var bIsAndroid = sUserAgent.match(/android/i) == "android";
    var bIsCE = sUserAgent.match(/windows ce/i) == "windows ce";
    var bIsWM = sUserAgent.match(/windows mobile/i) == "windows mobile";
    if (bIsIpad || bIsIphoneOs || bIsMidp || bIsUc7 || bIsUc || bIsAndroid || bIsCE || bIsWM) {
        return true;
    } else
        return false;
}