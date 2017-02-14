 
<?php
switch (CONTROLLER_NAME) {
	case 'Index':
			$user_menu = 'mfeed';
			break;
	case 'User':
			$user_menu = 'profilem';
			break;
	case 'Relation':
			$user_menu = 'fansm';
			break;
	case 'Message':
			$user_menu = ACTION_NAME=='notification'?'mnotification':'messagem';
			break;
	case 'Wall':
			$user_menu = 'wallm';
			break;
	case 'Miniblog':
			$user_menu = 'miniblogm';
			break;
	case 'Album':
			$user_menu = 'albumm';
			break;
	case 'Favorite':
			$user_menu = 'song';
			break;
	case 'Msg':
			$user_menu = 'messagem';
			break;
	case 'Account':
			$user_menu = 'account';
			break;
	default:
			$user_menu = 'feed';
            break;
}
?>
<div class="user_menu" id="{$user_menu}">
  <div class="userinfo">
    <div style=" width:160px;text-align:center;margin-bottom:2px;font-weight:bold">{$username}</div>
    <div class="face"> <img src="{:U('Api/Avatar/index',array('uid'=>$uid,'size'=>180))}" onerror="this.src='{$model_extresdir}images/noavatar.jpg'" id="menu-avatar"> </div>
  </div>
  <div class="menu_center">
    <ul>
      <li class="mprofile"> <a href="{:U('User/profile')}"> <span class="iconProfile">首页</span> </a> <em></em> </li>
      <li class="mshare"> <a href="{:U('Share/index')}" target="_self"> <span class="iconShare">分享管理</span> </a> <em></em> </li>
      <li class="msong"><a href="{:U('Favorite/index')}"><span class="iconSong">我的收藏</span></a><em></em></li>
      <li class="maccount"> <a href="{:U('Account/assets')}"> <span class="iconAccount">账户</span> </a> <em></em> </li>
      {:tag('view_member_menu',$User)}
    </ul>
  </div>
</div>
