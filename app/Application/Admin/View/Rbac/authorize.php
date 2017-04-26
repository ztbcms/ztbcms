 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">{$name} - 角色授权</div>
  <form class="J_ajaxFsorm" action="{:U('Rbac/authorize')}" method="post">
    <div class="table_full">
      <ul id="treeDemo" class="ztree">
      </ul>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <input type="hidden" name="roleid" value="{$roleid}" />
        <input type="hidden" name="menuid" value="" />
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">授权</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script type="text/javascript">
//配置
var setting = {
	check: {
		enable: true,
		chkboxType:{ "Y" : "ps", "N" : "ps" }
	},
    data: {
        simpleData: {
            enable: true,
            idKey: "id",
            pIdKey: "parentid",
        }
    },
    callback: {
        beforeClick: function (treeId, treeNode) {
            if (treeNode.isParent) {
                zTree.expandNode(treeNode);
                return false;
            } else {
                return true;
            }
        },
		onClick:function(event, treeId, treeNode){
			//栏目ID
			var catid = treeNode.catid;
			//保存当前点击的栏目ID
			setCookie('tree_catid',catid,1);
		}
    }
};
//节点数据
var zNodes = JSON.parse('{$json}');
//zTree对象
var zTree = null;
Wind.css('zTree');
$(function(){
	Wind.use('cookie','zTree', function(){
		$.fn.zTree.init($("#treeDemo"), setting, zNodes);
		zTree = $.fn.zTree.getZTreeObj("treeDemo");
		zTree.expandAll(true);
	});
});


var ajaxForm_list = $('form.J_ajaxFsorm');
if (ajaxForm_list.length) {
    Wind.use('ajaxForm', 'artDialog', function () {

        $('button.J_ajax_submit_btn').bind('click', function (e) {
            e.preventDefault();
            /*var btn = $(this).find('button.J_ajax_submit_btn'),
					form = $(this);*/
            var btn = $(this),
                form = btn.parents('form.J_ajaxFsorm');

			//处理被选中的数据
			form.find('input[name="menuid"]').val("");
			var  nodes = zTree.getCheckedNodes(true); 
			var str = "";
			$.each(nodes,function(i,value){
				if (str != "") {
					str += ","; 
				}
				str += value.id;
			});
			form.find('input[name="menuid"]').val(str);
			
            form.ajaxSubmit({
                url: btn.data('action') ? btn.data('action') : form.attr('action'),
                //按钮上是否自定义提交地址(多按钮情况)
                dataType: 'json',
                beforeSubmit: function (arr, $form, options) {
                    var text = btn.text();

                    //按钮文案、状态修改
                    btn.text(text + '中...').attr('disabled', true).addClass('disabled');
                },
                success: function (data, statusText, xhr, $form) {
                    var text = btn.text();
                    //按钮文案、状态修改
                    btn.removeClass('disabled').text(text.replace('中...', '')).parent().find('span').remove();
                    if (data.state === 'success') {
                        $('<span class="tips_success">' + data.info + '</span>').appendTo(btn.parent()).fadeIn('slow').delay(1000).fadeOut(function () {
                            if (data.url) {
                                //返回带跳转地址
                                if (window.parent.art) {
                                    //iframe弹出页
                                    window.parent.location.href = data.url;
                                } else {
                                    window.location.href = data.url;
                                }
                            } else {
                                if (window.parent.art) {
                                    reloadPage(window.parent);
                                } else {
                                    //刷新当前页
                                    reloadPage(window);
                                }
                            }
                        });
                    } else if (data.state === 'fail') {
                        $('<span class="tips_error">' + data.info + '</span>').appendTo(btn.parent()).fadeIn('fast');
                        btn.removeProp('disabled').removeClass('disabled');
                    }
                }
            });
        });
    });
}
</script>
</body>
</html>
