<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form action="{:U("Custompage/createhtml")}" method="post">
    <div class="table_list">
      <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td align="center" width="50"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x">全选</td>
            <td align="center" width="40">ID</td>
            <td width="200">名称</td>
            <td>生成路径</td>
            <td align="center" width="120">操作</td>
          </tr>
        </thead>
        <tbody>
          <volist name="data" id="vo">
            <tr>
              <td><input class="J_check" data-yid="J_check_y" data-xid="J_check_x" name="tempid[]" value="{$vo.tempid}" type="checkbox"></td>
              <td align="center">{$vo.tempid}</td>
              <td><a href="{$Config.siteurl|substr=###,0,-1}{$vo.temppath}{$vo.tempname}" target="_blank" >{$vo.name}</a></td>
              <td>{$vo.temppath}{$vo.tempname}</td>
              <td align="center">
              <?php
				$op = array();
				if(\Libs\System\RBAC::authenticate('edit')){
					$op[] = '<a href="'.U("Custompage/edit",array('tempid'=>$vo['tempid'])).'">修改</a>';
				}
				if(\Libs\System\RBAC::authenticate('delete')){
					$op[] = '<a class="J_ajax_del" href="'.U("Custompage/delete",array('tempid'=>$vo['tempid'])).'">删除</a>';
				}
				if(\Libs\System\RBAC::authenticate('createhtml')){
					$op[] = '<a href="'.U("Custompage/createhtml",array('tempid'=>$vo['tempid'])).'">更新</a>';
				}
				echo implode(' | ',$op);
			   ?>
              </td>
            </tr>
          </volist>
        </tbody>
      </table>
      <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <label class="mr20"><input type="checkbox" class="J_check_all" data-direction="y" data-checklist="J_check_y">全选</label> 
        <?php
		if(\Libs\System\RBAC::authenticate('createhtml')){
		?>
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">生成自定义页面</button>
        <?php
		}
		?>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
