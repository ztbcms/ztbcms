<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed" style=" padding-top:5px; padding-left:10px;">
<style>
body{
	overflow-x:auto;
	overflow-y:auto;
}
</style>
<script type="text/javascript">
//打开新窗口
function openwinx(url,name,w,h) {
    window.open(url);
}
//配置
var setting = {
    data: {
        key: {
            name: "catname"
        },
        simpleData: {
            enable: true,
            idKey: "catid",
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
var zNodes ={$json};
//zTree对象
var zTree = null;
Wind.css('zTree');
$(function(){
	Wind.use('cookie','zTree', function(){
		$.fn.zTree.init($("#treeDemo"), setting, zNodes);
		zTree = $.fn.zTree.getZTreeObj("treeDemo");
		$("#ztree_expandAll").click(function(){
			if($(this).data("open")){
				zTree.expandAll(false);
				$(this).data("open",false);
			}else{
				zTree.expandAll(true);
				$(this).data("open",true);
			}
		});
		//定位到上次打开的栏目，进行展开tree_catid
		var tree_catid = getCookie('tree_catid');
		if(tree_catid){
			var nodes = zTree.getNodesByParam("catid", tree_catid, null);
			zTree.selectNode(nodes[0]);
		}
	});
});
</script>
<div>
  <ul class="ztree" style="padding:0px;">
    <li> <a title="全部展开、折叠 "><span class="button ico_open" style="background:url({$config_siteurl}statics/images/application_side_expand.png) 0 0 no-repeat;"></span><span id="ztree_expandAll" data-open="false">全部展开、折叠 </span></a> </li>
  </ul>
  <ul id="treeDemo" class="ztree">
  </ul>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
