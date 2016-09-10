<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form class="J_ajaxForm" action="{:U("Field/listorder")}" method="post">
  <div class="table_list">
  <table width="100%" cellspacing="0" >
        <thead>
          <tr>
            <td width="70" align='center'>排序</td>
            <td width="200">字段名</td>
            <td>别名</td>
            <td width="140">字段类型</td>
            <td width="60" align='center'>主表</td>
            <td width="60" align='center'>必填</td>
            <td width="60" align='center'>搜索</td>
            <td width="60" align='center'>排序</td>
            <td width="60" align='center'>投稿</td>
            <td width="150" align='center'>管理操作</td>
          </tr>
        </thead>
        <tbody class="td-line">
        <volist name="data" id="vo">
          <tr>
            <td align='center'><input name='listorders[{$vo.fieldid}]' type='text' size='3' value='{$vo.listorder}' class='input'></td>
            <td>{$vo.field}</td>
            <td>{$vo.name}</td>
            <td>{$vo.formtype}</td>
            <td align='center'><if condition="$vo['issystem'] eq 1"><font color="blue">√</font><else /> <font color="red">╳</font></if></td>
            <td align='center'><if condition="$vo['minlength'] eq 1"><font color="blue">√</font><else /> <font color="red">╳</font></if></td>
            <td align='center'><if condition="$vo['issearch'] eq 1"><font color="blue">√</font><else /> <font color="red">╳</font></if></td>
            <td align='center'><if condition="$vo['isorder'] eq 1"><font color="blue">√</font><else /> <font color="red">╳</font></if></td>
            <td align='center'><if condition="$vo['isadd'] eq 1"><font color="blue">√</font><else /> <font color="red">╳</font></if></td>
            <td align='center'>
            <?php
			$operate = array();
			if(\Libs\System\RBAC::authenticate('edit')){
				$operate[] = '<a href="'.U("Field/edit",array("fieldid"=>$vo['fieldid'],"modelid"=>$vo['modelid'])).'">修改</a>';
			}
			if(\Libs\System\RBAC::authenticate('disabled')){
				if(in_array($vo['field'],$forbid_fields)){
					$operate[] = '<font color="#BEBEBE"> 隐藏 </font>';
				}else{
					if($vo['disabled'] == 0){
						$operate[] = '<a href="'.U("Field/disabled",array("fieldid"=>$vo['fieldid'],"modelid"=>$vo['modelid'],"disabled"=>0)).'">隐藏</a>';
					}else{
						$operate[] = '<a href="'.U("Field/disabled",array("fieldid"=>$vo['fieldid'],"modelid"=>$vo['modelid'],"disabled"=>1)).'"><font color="#FF0000">显示</font></a>';
					}
				}
			}
			if(\Libs\System\RBAC::authenticate('delete')){
				if(in_array($vo['field'],$forbid_delete)){
					$operate[] = '<font color="#BEBEBE"> 删除</font>';
				}else{
					$operate[] = '<a class="J_ajax_del" href="'.U("Field/delete",array("fieldid"=>$vo['fieldid'],"modelid"=>$vo['modelid'])).'">删除</a>';
				}
			}
			echo implode(' | ',$operate);
			?>
            </td>
          </tr>
        </volist>
        </tbody>
      </table>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">排序</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
