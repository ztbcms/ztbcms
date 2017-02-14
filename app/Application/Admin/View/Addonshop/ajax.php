
  <div class="table_list">
    <table width="100%">
      <thead>
        <tr>
          <td width="200">名称</td>
          <td width="100">标识</td>
          <td align="center">描述</td>
          <td align="center" width="100">作者</td>
          <td align="center" width="50">版本</td>
          <td align="center" width="227">操作</td>
        </tr>
      </thead>
      <volist name="data" id="vo">
      <tr>
        <td><if condition=" $vo['url'] "><a  href="{$vo.url}" target="_blank">{$vo.title}</a><else/>{$vo.title}</if></td>
        <td>{$vo.name}</td>
        <td>{$vo.description}</td>
        <td align="center">{$vo.author}</td>
        <td align="center">{$vo.version}</td>
        <td align="center">
          <?php
		  $op = array();
		  if(!D('Addons/Addons')->isInstall($vo['name'])){
			  $op[] = '<a href="'.U('install',array('sign'=>$vo['sign']?:$vo['name'])).'" class="btn btn_submit mr5 Js_install">安装</a>';
		  }else{
			 //有安装，检测升级
			 if($vo['upgrade']){
				 $op[] = '<a href="'.U('upgrade',array('sign'=>$vo['sign']?:$vo['name'])).'" class="btn btn_submit mr5 Js_upgrade" id="upgrade_tips_'.$vo['name'].'">升级到最新'.$vo['newVersion'].'</a>';
			 }
		  }
		  echo implode('  ',$op);
		  if($vo['price']){
			  echo "<br /><font color=\"#FF0000\">价格：".$vo['price']." 元</font>";
		  }
		  ?>
         </td>
      </tr>
      </volist>
    </table>
  </div>
  <div class="p10">
        <div class="pages">{$Page}</div>
   </div>