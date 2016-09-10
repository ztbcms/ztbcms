var danceLib = {
    danceSelectInit: function() {
        $('#selectAll').click(function() { //全选
            var className = $(this).attr('class');
            if (className == "select_all") {
                $('#list :checkbox').each(function() {
                    $(this).attr('checked', 'checked');
                });
                $('#selectAll').removeAttr("class");
                $('#selectAll').attr("class", "on_select_all");
            } else {
                $('#list :checkbox').each(function() {
                    $(this).removeAttr('checked');
                });
                $('#selectAll').removeAttr("class");
                $('#selectAll').attr("class", "select_all");
            }
        });
    },
    //删除收藏
    likeDelInit: function() {
        $(".del").click(function() {
            var did = $(this).attr("did");
            $.dialog({
                id: 'delDance',
                title: false,
                border: false,
                follow: $(this)[0],
                content: '确认要删除这条收藏么？',
                okValue: '确认',
                ok: function() {
                    $.getJSON(_config['domainSite'] + "index.php?g=Member&m=Favorite&a=favoritedel&callback=?", "fid=" + escape(did),
                            function(data) {
                                if (data['error'] == 20001) {
                                    user.userNotLogin('您需要先登录才能进行此操作！');
                                    return false;
                                } else if (data['error'] == 20002) {
                                    $.tipMessage('没有该收藏记录！', 1, 2000);
                                    return false;
                                } else if (data['error'] == 10000) {
                                    $.tipMessage("收藏删除成功！", 0, 1500, 0, function() {
                                        location.href = location.href;
                                    });
                                } else {
                                    $.tipMessage(data['info'], 1, 2000);
                                    return false;
                                }
                            });
                },
                cancelValue: '取消',
                cancel: function() {

                }
            });
        });
    }
}