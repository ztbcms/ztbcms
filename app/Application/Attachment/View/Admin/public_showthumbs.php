 
<Admintemplate file="Common/Head"/>
<body class="body_none">
<style type="text/css">
.attachment-list{ width:480px}
.attachment-list .cu{
	dispaly:block;float:right; background:url({$config_siteurl}statics/images/cross.png) no-repeat 0px 100%;width:20px; height:16px; overflow:hidden;}
.attachment-list li{ width:120px; padding:0 20px 10px; float:left}
</style>
<div style="padding:10px;">
  <ul class="attachment-list">
    <volist name="thumbs" id="thumb">
      <li> <img src="{$thumb.thumb_url}" alt="{$thumb.width} X {$thumb.height}" width="120" /> <span class="cu" title="删除" onclick="thumb_delete('<?php echo urlencode($thumb['thumb_filepath'])?>',this)"></span> <?php echo $thumb['width']?> X <?php echo $thumb['height']?> </li>
    </volist>
  </ul>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script> 
<script type="text/javascript">
function thumb_delete(filepath, obj) {
    Wind.use('artDialog', function () {
        art.dialog({
            content: '确认删除？',
            icon: 'warning',
            fixed: true,
            lock: true,
            background: "#CCCCCC",
            opacity: 0,
            style: 'confirm',
            id: 'att_delete',
            ok: function () {
                $.get(GV.DIMAUB + 'index.php?a=public_delthumbs&m=Admin&g=Attachment&filepath=' + filepath, function (data) {
                    if (data == 1) {
                    		$(obj).parent().fadeOut("slow");
                    }else{
                    		alert(data);
                    }
                })
            },
            cancel: function () {

            }
        })
    });
};
</script>
</body>
</html>
