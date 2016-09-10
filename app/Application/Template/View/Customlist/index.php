<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form action="{:U("Customlist/generate")}" method="post" class="J_ajaxForm">
    <div class="table_list">
      <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td align="center" width="50"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x">全选</td>
            <td align="center" width="40">ID</td>
            <td align="left">名称</td>
            <td align="center" width="100">预览</td>
            <td align="center" width="120">操作</td>
          </tr>
        </thead>
        <tbody>
          <volist name="data" id="vo">
            <tr>
              <td><input class="J_check" data-yid="J_check_y" data-xid="J_check_x" name="ids[]" value="{$vo.id}" type="checkbox"></td>
              <td align="center">{$vo.id}</td>
              <td align="left">{$vo.name}</td>
              <td align="center"><a href="{$vo.url}" target="_blank">点击预览</a></td>
              <td align="center">
              <?php
				$op = array();
				if(\Libs\System\RBAC::authenticate('edit')){
					$op[] = '<a href="'.U("Customlist/edit",array('id'=>$vo['id'])).'">修改</a>';
				}
				if(\Libs\System\RBAC::authenticate('delete')){
					$op[] = '<a class="J_ajax_del" href="'.U("Customlist/delete",array('id'=>$vo['id'])).'">删除</a>';
				}
				if(\Libs\System\RBAC::authenticate('generate')){
					$op[] = '<a href="'.U("Customlist/generate",array('id'=>$vo['id'])).'">更新</a>';
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
		if(\Libs\System\RBAC::authenticate('generate')){
		?>
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">生成列表</button>
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
