 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="nav">
    <ul class="cc">
      <li class="current"><a href="{:U('Style/index')}">模板管理</a></li>
      <?php
	  if(\Libs\System\RBAC::authenticate('add')){
	  ?>
      <li><a href="{:U("Template/Style/add",array("dir"=>urlencode(str_replace('/','-',$dir))    ))}">在此目录下添加模板</a></li>
      <?php
	  }
	  ?>
    </ul>
  </div>
  <div class="table_list">
  <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td align="left">目录列表</td>
            <td align="center"  width="100">操作</td>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td align="left" colspan="3">当前目录：{$local}</td>
          </tr>
          <if condition="$dir neq '' && $dir neq '.' "> 
          <tr>
            <td align="left" colspan="3"><a href="{:U("Template/Style/index",array("dir"=>urlencode(  str_replace(basename($dir).'-','',str_replace('/','-',$dir))   )     )   )}"><img src="{$config_siteurl}statics/images/folder-closed.gif" />上一层目录</a></td>
          </tr>
          </if>
          <volist name="tplist" id="vo">
          <tr>
            <td align="left">
            <if condition=" '.'.fileext(basename($vo)) == C('TMPL_TEMPLATE_SUFFIX')">
            <img src="{$tplextlist[$vo]}" />
            <a href="{:U("Template/Style/edit",array("dir"=>urlencode(str_replace('/','-',$dir)),"file"=>basename($vo)))}"><b>{$vo|basename}</b></a></td>
            <td align="center"> 
            <?php
			$op = array();
			if(\Libs\System\RBAC::authenticate('edit')){
				$op[] = '<a href="'.U("Template/Style/edit",array("dir"=>urlencode(str_replace('/','-',$dir)) ,"file"=>basename($vo))).'">修改</a>';
			}
			if(\Libs\System\RBAC::authenticate('delete')){
				$op[] = '<a class="J_ajax_del" href="'.U("Template/Style/delete",array("dir"=>urlencode(str_replace('/','-',$dir)) ,"file"=>basename($vo))).'">删除</a>';
			}
			echo implode(' | ',$op);
			?>
            </td>
            <elseif condition="substr($tplextlist[$vo],-strlen($dirico))!=$dirico" />
            <img src="{$tplextlist[$vo]}" />
            <b>{$vo|basename}</b></td>
            <td></td>
            <else />
            <img src="{$tplextlist[$vo]}" />
            <a href="{:U("Template/Style/index",array("dir"=>urlencode(str_replace('/','-',$dir).basename($vo).'-') ))}"><b>{$vo|basename}</b></a></td>
            <td></td>
            </if>
          </tr>
          </volist>
        </tbody>
      </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
