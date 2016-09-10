<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="180">
    <iframe name="left" id="iframe_categorys" src="{:U('Content/public_categorys')}" style="height: 100%; width: 180px;"  frameborder="0" scrolling="auto"></iframe></td>
    <td width="3" bgcolor="#CCCCCC">
    </td>
    <td>
    <iframe name="right" id="iframe_categorys_list" src="{:U('Admin/Main/index')}"   style="height: 100%; width:100%;border:none;"   frameborder="0" scrolling="auto"></iframe></td>
  </tr>
</table>
<script type="text/javascript">
var B_frame_height = parent.$("#B_frame").height()-8;
$(window).on('resize', function () {
    setTimeout(function () {
		B_frame_height = parent.$("#B_frame").height()-8;
        frameheight();
    }, 100);
});
function frameheight(){
	$("#iframe_categorys").height(B_frame_height);
	$("#iframe_categorys_list").height(B_frame_height);
}
(function (){
	frameheight();
})();
</script>
</body>
</html>
