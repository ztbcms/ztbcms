 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">自定义列表模板使用说明</div>
  <literal>
  <div class="prompt_text">
    <p>模板支持如下变量：SEO输出（数组）->$SEO，查询数据（数组）->$listData，分页代码->$pages</p>
    <p>URL规则 可以在 内容->URL规则管理 添加。“模块名称” 选择 “模板管理”，“URL规则名称”填写 “list”然后 选择生成静态</p>
  </div>
  </literal>
  <div class="h_a">添加自定义页面</div>
  <form name="myform" id="myform" action="{:U("Template/Customlist/edit")}" method="post" class="J_ajaxForm">
    <div class="table_full">
      <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
        <tbody>
          <tr>
            <th width="120">自定义列表标题名称</th>
            <td><input type="text" class="input" name="name" value="{$info.name}" placeholder="请输入自定义列表标题名称，必填项" style="width:300px;"></td>
          </tr>
          <tr>
            <th width="120">网页标题</th>
            <td><input type="text" class="input" name="title" value="{$info.title}" ></td>
          </tr>
          <tr>
            <th width="120">网页关键词</th>
            <td><input type="text" class="input" name="keywords" value="{$info.keywords}"  style="width:300px;"> </td>
          </tr>
          <tr>
            <th width="120">网页描述</th>
            <td><textarea name="description" style="width:95%; height:100px">{$info.description}</textarea></td>
          </tr>
          <tr>
            <th width="120">数据统计SQL</th>
            <td><input type="text" class="input" name="totalsql" value="{$info.totalsql}"  style="width:95%;" placeholder="请输入数据统计SQL，必填项"> <br/>
            <span class="gray">如：select count(*) as total from cms_article where catid = 1</span></td>
          </tr>
          <tr>
            <th width="120">数据查询SQL</th>
            <td><input type="text" class="input" name="listsql" value="{$info.listsql}"  style="width:95%;" placeholder="请输入数据查询SQL，请不要在结尾出现LIMIT"> <br/>
            <span class="gray">如：select * from cms_article where catid = 1 order by id desc</span></td>
          </tr>
          <tr>
            <th width="120">每页显示多少条</th>
            <td><input type="text" class="input" name="lencord" value="{$info.lencord}"> </td>
          </tr>
          <tr>
            <th width="120">URL规则</th>
            <td><span class="gray"><label><input name="isurltype" type="radio" value="1" <if condition=" !empty($info['urlruleid'])  ">checked</if>>使用已有规则：<label></span>{$list_html_ruleid} 
            <span class="gray"><label><input name="isurltype" type="radio" value="2" <if condition=" !empty($info['urlrule'])  ">checked</if>>或者</label></span>  <input type="text" class="input" name="urlrule" value="{$info.urlrule}" style="width:300px;"> 
            <br/>
            <span class="gray"><literal>
            规则使用说明：
            <br/>1、只能使用以下变量 年->{$year}，月->{$month}，日->{$day}，自定义列表ID->{$id}，分页号->{$page}
            <br/>2、URL规则是定义生成目录路径，所以是从网站根目录开始算起。
            <br/>3、规则是成对，以“|”分开。例如：customlist/{$id}.html|customlist/{$id}_{$page}.html
            </span></literal>
            </td>
          </tr>
          <tr>
            <th width="120">列表模板</th>
            <td><select name="listpath" id="listpath" onChange="onlistpath(this.value)">
                  <option value="" selected>==使用模板代码==</option>
                  <volist name="tp_list" id="vo">
                    <option value="{$vo}" <if condition=" $vo eq $info['listpath'] ">selected</if>>{$vo}</option>
                  </volist>
                </select> </td>
          </tr>
          <tr id="template" <if condition=" !empty($info['listpath'])  ">style="display:none"</if>>
            <th>模板内容</th>
            <td>
            <span class="gray"><literal>
            基本使用：
            <br/>1、分页标签 {$pages}
            <br/>2、查询出的数据都保存在 $listData 变量，请使用相关循环标签进行循环读取。例如：Volist标签。
            </span></literal>
            <textarea name="template" style="width:95%; height:400px" validate="required:true, minlength:4">{$info.template}</textarea></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <input type="hidden" name="id" value="{$info.id}" />
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">编辑自定义列表</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
function onlistpath(value){
	if(value == ''){
		$('#template').show();
	}else{
		$('#template').hide();
	}
}
</script>
</body>
</html>
