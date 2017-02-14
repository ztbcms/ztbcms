 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<style>
.design_page li {
	height: 218px;
}
</style>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="mb10 cc">共 <span class="org">{$count}</span> 套模板</div>
  <div class="design_page">
    <ul class="cc">
    <volist name="themes" id="vo">
      <li>
        <div class="img"><a title="点击进行模板主题切换" href="{:U("Template/Theme/chose",array("theme"=>$vo[name]))}"><img src="{$vo.preview}" width="210" height="140" lt="{$vo.name}"></a></div>
        <div class="title" title="default">{$vo.name}</div>
        <div class="ft"> <span class="org"><if condition="$vo['use']==1"><i>已使用</i><else/><a href="{:U("Template/Theme/chose",array("theme"=>$vo[name]))}">未使用</a></if></span></div>
      </li>
      </volist>
    </ul>
  </div>
</div>
</body>
</html>
