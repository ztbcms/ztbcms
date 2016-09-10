var account = { //领取积分
    doAccountInit: function () {
        $("#obtain").click(function () {
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: "/index.php?c=Account&a=doAccountUpdate",
                dataType: "text",
                success: function (data) {
                    if (data == 20001) {
                        user.userNotLogin('您未登录无法执行此操作！');
                        return false;
                    } else if (data == 10004) {
                        $.tipMessage('您暂时还没有未领积分！', 1, 2000);
                        return false;
                    } else {
                        $.tipMessage('积分已领取！', 0, 2000, 0, function () {
                            location.href = location.href;
                        });
                    }
                },
                error: function () {
                    alert('数据执行意外错误！');
                }
            });
        });
    },
    vipRenewals: function () { //续费
        $("#renewals").click(function () {
            $.tipMessage('充值系统即将开放，敬请期待！', 1, 2000);
            return false;
        });
    },
    vipRecharge: function () { //充值
        $("#recharge").click(function () {
            //location.href = "/index.php?c=Account&a=testRecharge";
            $.tipMessage('充值系统即将开放，敬请期待！', 1, 2000);
            return false;
        });
    },
    doListenAccountInit: function () {
        $("#listenScore").click(function () {
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: "/index.php?c=Account&a=doListenScoreUpdate",
                dataType: "text",
                success: function (data) {
                    if (data == 20001) {
                        user.userNotLogin('您未登录无法执行此操作！');
                        return false;
                    } else if (data == 10004) {
                        $.tipMessage('积分大于10分才可领取！', 1, 2000);
                        return false;
                    } else {
                        $.tipMessage('积分已领取！', 0, 2000, 0, function () {
                            location.href = location.href;
                        });
                    }
                },
                error: function () {
                    alert('数据执行意外错误！');
                }
            });
        });
    }
};