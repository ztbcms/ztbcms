<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form name="myform" action="{:U('Member/connect')}" method="post">
    <div class="table_list">
      <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td  align="left" width="20"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></td>
            <td align="left">授权ID</td>
            <td align="left">用户UID</td>
            <td align="left">用户名</td>
            <td align="left">授权类型</td>
            <td align="left">标识(openid)</td>
            <td align="left">access_token</td>
            <td align="left">expires_in</td>
            <td align="left">操作</td>
          </tr>
        </thead>
        <tbody>
          <volist name="data" id="vo">
            <tr>
              <td align="center"><input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x" value="{$vo.connectid}" name="connectid[]"></td>
              <td align="center">{$vo.connectid}</td>
              <td align="center">{$vo.userid}</td>
              <td align="left"><img src="{:getavatar($vo['userid'])}" height=18 width=18 onerror="this.src='{$config_siteurl}statics/images/member/nophoto.gif'">{$vo.username}<a href="javascript:member_infomation({$vo.userid}, '{$vo.modelid}', '')"><img src="{$config_siteurl}statics/images/icon/detail.png"></a></td>
              <td align="left">{$vo.app}</td>
              <td align="left">{$vo.openid}</td>
              <td align="left">{$vo.accesstoken}</td>
              <td align="left"><if condition=" $vo['expires'] "> {$vo.expires|date="Y-m-d H:i:s",###}</if></td>
              <td align="left"><a href="javascript:confirmurl('{:U('Member/connect', array('connectid'=>$vo['connectid']) )}','确认要取消绑定吗？')">[取消绑定]</a></td>
            </tr>
          </volist>
        </tbody>
      </table>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        全选 <input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x">
        <button class="btn  mr10 J_ajax_submit_btn" type="submit">删除</button>
      </div>
  </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script>
//会员信息查看
function member_infomation(userid, modelid, name) {
	omnipotent("member_infomation", GV.DIMAUB+'index.php?g=Member&m=Member&a=memberinfo&userid='+userid+'', "个人信息",1)
}
</script>
</body>
</html>
