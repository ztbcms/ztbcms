 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form method='post'  class="J_ajaxForm"  action="{:U('Config/extend')}">
  <input type="hidden" name="action" value="add"/>
  <div class="h_a">添加扩展配置项</div>
  <div class="table_list">
    <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
      <tbody>
        <tr>
          <td width="60">键名</td>
          <td><input type="text" class="input" name="fieldname" value=""> 注意：只允许英文、数组、下划线</td>
        </tr>
        <tr>
          <td>名称</td>
          <td><input type="text" class="input" name="setting[title]" value=""></td>
        </tr>
        <tr>
          <td>类型</td>
          <td><select name="type" onChange="extend_type(this.value)">
              <option value="input" >单行文本框</option>
              <option value="select" >下拉框</option>
              <option value="textarea" >多行文本框</option>
              <option value="radio" >单选框</option>
              <option value="password" >密码输入框</option>
            </select></td>
        </tr>
        <tr>
          <td>提示</td>
          <td><input type="text" class="input length_4" name="setting[tips]" value=""></td>
        </tr>
        <tr>
          <td>样式</td>
          <td><input type="text" class="input length_4" name="setting[style]" value=""></td>
        </tr>
        <tr class="setting_radio" style="display:none">
          <td>选项</td>
          <td><textarea name="setting[option]" disabled="true" style="width:380px; height:150px;">选项名称1|选项值1</textarea> 注意：每行一个选项</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="btn_wrap_pd"><button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">添加</button></div>
  </form>
  <div class="h_a">扩展配置 ，用法：模板调用标签：<literal>{:cache('Config</literal>.键名')}，PHP代码中调用：<literal>cache('Config</literal>.键名');</div>
  <div class="table_full">
    <form method='post'   id="myform" class="J_ajaxForm"  action="{:U('Config/extend')}">
      <table width="100%"  class="table_form">
        <volist name="extendList" id="vo">
        <php>$setting = unserialize($vo['setting']);</php>
        <tr>
          <th width="200">{$setting.title} <a href="{:U('Config/extend',array('fid'=>$vo['fid'],'action'=>'delete'))}" class="J_ajax_del" title="删除该项配置" style="color:#F00">X</a><span class="gray"><br/>键名：{$vo.fieldname}</span></th>
          <th class="y-bg">
          <switch name="vo.type">
             <case value="input">
             <input type="text" class="input" style="{$setting.style}"  name="{$vo.fieldname}" value="{$Site[$vo['fieldname']]}">
             </case>
             <case value="select">
             <select name="{$vo.fieldname}">
             <volist name="setting['option']" id="rs">
             <option value="{$rs.value}" <if condition=" $Site[$vo['fieldname']] == $rs['value'] ">selected</if>>{$rs.title}</option>
             </volist>
             </select>
             </case>
             <case value="textarea">
             <textarea name="{$vo.fieldname}" style="{$setting.style}">{$Site[$vo['fieldname']]}</textarea>
             </case>
             <case value="radio">
             <volist name="setting['option']" id="rs">
             <input name="{$vo.fieldname}" value="{$rs.value}" type="radio"  <if condition=" $Site[$vo['fieldname']] == $rs['value'] ">checked</if>> {$rs.title}
             </volist>
             </case>
             <case value="password">
             <input type="password" class="input" style="{$setting.style}"  name="{$vo.fieldname}" value="{$Site[$vo['fieldname']]}">
             </case>
          </switch>
           <span class="gray"> {$setting.tips}</span>
          </th>
        </tr>
        </volist>
      </table>
      <div class="btn_wrap">
        <div class="btn_wrap_pd">
          <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
function extend_type(type){
	if(type == 'radio' || type == 'select'){
		$('.setting_radio').show();
		$('.setting_radio textarea').attr('disabled',false);
	}else{
		$('.setting_radio').hide();
		$('.setting_radio textarea').attr('disabled',true);
	}
}
</script>
</body>
</html>
