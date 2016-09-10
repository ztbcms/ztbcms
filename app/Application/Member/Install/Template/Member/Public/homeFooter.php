<?php if (!defined('CMS_VERSION')) exit(); ?>
<div class="user_copyright">  © 2016 ZtbCMS 驱动 执行时间：{:G('run', 'end')}s</div>
<script type="text/javascript" src="{$model_extresdir}js/jquery.artDialog.min.js"></script>
<script type="text/javascript">
	//监听消息
	listenMsg.start();
	//用户导航
	nav.userMenu();
	nav.init();
</script>  
