<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">行为规则使用说明</div>
  <div class="prompt_text">
    <p><b>规则定义格式1：</b> </p>
    <ul style="color:#00F">
      <li>格式： table:$table|field:$field|condition:$condition|rule:$rule[|cycle:$cycle|max:$max]</li>
    </ul>
    <p><b>规则字段解释：</b></p>
    <ul>
      <li>table->要操作的数据表，不需要加表前缀</li>
      <li>field->要操作的字段</li>
      <li>condition-><literal>操作的条件，目前支持字符串。条件中引用行为参数，使用{$parameter}的形式（该形式只对行为标签参数是为数组的有效，纯碎的参数使用{$self}）！</literal></li>
      <li>rule->对字段进行的具体操作，目前支持加、减 </li>
      <li>cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次 </li>
      <li>max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）</li>
    </ul>
    <p><b>规则定义格式2：</b> </p>
    <ul style="color:#00F">
      <li>格式： phpfile:$phpfile[|module:$module]</li>
    </ul>
    <p><b>规则字段解释：</b></p>
    <ul>
      <li>phpfile->直接调用已经定义好的行为文件。</li>
      <li>module->行为所属模块，没有该参数时，自动定位到 app\Common\Behavior 目录。</li>
    </ul>
    <p><b>规则定义格式3：</b> </p>
    <ul style="color:#00F">
      <li>格式： sql:$sql[|cycle:$cycle|max:$max]</li>
    </ul>
    <p><b>规则字段解释：</b></p>
    <ul>
      <li>sql-><literal>需要执行的SQL语句，表前缀可以使用“ztbcms_”代替。参数可以使用 {$parameter}的形式（该形式只对行为标签参数是为数组的有效，纯碎的参数使用{$self}）！</literal></li>
      <li>cycle->执行周期，单位（小时），表示$cycle小时内最多执行$max次 </li>
      <li>max->单个周期内的最大执行次数（$cycle和$max必须同时定义，否则无效）</li>
    </ul>
  </div>
  <form class="J_ajaxForm" action="{:U('Behavior/add')}" method="post">
    <div class="h_a">基本属性</div>
    <div class="table_full">
      <table width="100%" class="table_form contentWrap">
        <tbody>
          <tr>
            <th width="80">行为标识</th>
            <td><input type="test" name="name" class="input" id="name">
              <span class="gray">输入行为标识 英文字母</span></td>
          </tr>
          <tr>
            <th>行为名称</th>
            <td><input type="test" name="title" class="input" id="title">
              <span class="gray">输入行为名称</span></td>
          </tr>
          <tr>
            <th>行为类型</th>
            <td><select name="type">
					<option value="1" selected>控制器</option>
                    <option value="2" >视图</option>
                    </select>
                    <span class="gray">控制器表示是在程序逻辑中的，视图，表示是在模板渲染过程中的！</span></td>
          </tr>
          <tr>
            <th>行为描述</th>
            <td><textarea name="remark" rows="2" cols="20" id="remark" class="inputtext" style="height:100px;width:500px;"></textarea></td>
          </tr>
          <tr>
            <th>行为规则</th>
            <td><div class="cross" style="width:100%;">
                <ul id="J_ul_list_addItem" class="J_ul_list_public" style="margin-left:0px;">
                  <li><span style="width:40px;">排序</span><span>规则</span></li>
                  <li><span style="width:40px;"><input type="test" name="listorder[0]" class="input" value="" style="width:35px;"></span><span style="width:500px;"><input type="test" name="rule[0]" class="input" value="" style="width:450px;"></span></li>
                </ul>
              </div>
              <a href="" class="link_add Js_ul_list_add" data-related="addItem">添加规则</a></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">添加</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript">
var Js_ul_list_add = $('a.Js_ul_list_add');
var new_key = 0;
if (Js_ul_list_add.length) {
    //添加
    Js_ul_list_add.click(function (e) {
        e.preventDefault();
        new_key++;
        var $this = $(this);
		//添加分类
		var _li_html = '<li>\
								<span style="width:40px;"><input type="test" name="listorder[' + new_key + ']" class="input" value="" style="width:35px;"></span>\
								<span style="width:500px;"><input type="test" name="rule[' + new_key + ']" class="input" value="" style="width:450px;"></span>\
							</li>';
        //"new_"字符加上唯一的key值，_li_html 由列具体页面定义
        var $li_html = $(_li_html.replace(/new_/g, 'new_' + new_key));
        $('#J_ul_list_' + $this.data('related')).append($li_html);
        $li_html.find('input.input').first().focus();
    });

    //删除
    $('ul.J_ul_list_public').on('click', 'a.J_ul_list_remove', function (e) {
        e.preventDefault();
        $(this).parents('li').remove();
    });
}
</script>
</body>
</html>
