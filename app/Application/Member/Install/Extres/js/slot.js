var slotLib = {
    Init: function () {
        $('#button').click(function () {
            $(this).attr('disabled', "disabled");
            $.getJSON(_config['domainSite'] + "/account?a=doMoneyAdd&callback=?",
                function (data) {
                    if (data['error'] == 20001) {
                        $.tipMessage("您未登录，无法执行此操作。", 1, 3000, 0, function () {
                            location.href = location.href;
                        });
                    } else if (data['error'] == 10002) {
                        $.tipMessage("您已摇过一次，请中午12点以后再来。", 1, 3000, 0, function () {
                            location.href = location.href;
                        });
                    } else if (data['error'] == 10003) {
                        $.tipMessage("您今天摇奖资格已经用完，请明天再来。", 1, 3000, 0, function () {
                            location.href = location.href;
                        });
                    } else {

                        var num = $("#time1").text();
                        if (num != 0) {
                            var num1 = ++num;
                        } else {
                            var num1 = 1;
                        }
                        $("#time1").attr("title", "累计摇奖" + num1 + "次").text(num1);
                        $('#whree1').addClass('con').css('top', -0 + 'px');
                        $('#whree2').addClass('con').css('top', -0 + 'px');
                        $('#whree3').addClass('con').css('top', -0 + 'px');
                        setTimeout(function () {
                            $('#whree1').removeClass('con').css('top', -(data['right'][0] * 100) + 'px');
                            $('#slot').hide();
                            $('#random').show();
                            if (data['right'][0] != 0) {
                                $('#score').html(data['right'][0] + data['right'][1] + data['right'][2]);
                            } else {
                                $('#score').html(data['right'][1] + data['right'][2]);
                            }
                        }, 5000);
                        setTimeout(function () {
                            $('#whree2').removeClass('con').css('top', -(data['right'][1] * 100) + 'px');
                        }, 3500);
                        setTimeout(function () {
                            $('#whree3').removeClass('con').css('top', -(data['right'][2] * 100) + 'px');
                        }, 2000);
                    }
                });
            return false;

        });
        $("#close").click(function () {
            $("#rand").hide();

        });

    }
};