<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap"> 
  <!--搜索开始-->
  <div class="h_a">有关“<span class="red">{$keyword}</span>”的搜索结果</div>
  <div class="search_list">
  <?php
  foreach($menuData as $k=>$v):
  ?>
    <h2><?php echo str_replace($keyword,"<font color=\"red\">".$keyword."</font>",$menuName[$k])?></h2>
    <dl>
      <?php
	  foreach($v as $id=>$men):
	     $url = $men['app']."/".$men['model']."/".$men['action'];
	  ?>
      <dd><a class="J_search_items" href="{:U(''.$url.'',array('menuid'=>$men['id']) )}" data-id="{$men.id}{$men.app}"><?php echo str_replace($keyword,"<font color=\"red\">".$keyword."</font>",$men['name'])?></a></dd>
      <?php
	  endforeach;
	  ?>
    </dl>
  <?php
  endforeach;
  ?>
  </div>
  <!--搜索结束-->
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
$(function(){
	$('a.J_search_items').on('click', function(e){
		e.preventDefault();
		var $this = $(this);
		var data_id = $(this).attr('data-id');
		var href = this.href;
		parent.window.iframeJudge({
			elem: $this,
			href: href,
			 id: data_id
		});
	});

});
</script>
</body>
</html>