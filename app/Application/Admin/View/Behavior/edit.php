<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap">
  <Admintemplate file="Common/Nav"/>
  <form class="J_ajaxForm" action="{:U('Behavior/edit')}" method="post">
    <div class="h_a">基本属性</div>
    <div class="table_full">
      <table width="100%" class="table_form contentWrap">
        <tbody>
          <tr>
            <th width="80">行为标识</th>
            <td><input type="test" name="name" class="input" id="name" value="{$info.name}">
              <span class="gray">输入行为标识 英文字母</span></td>
          </tr>
          <tr>
            <th>行为名称</th>
            <td><input type="test" name="title" class="input" id="title" value="{$info.title}">
              <span class="gray">输入行为名称</span></td>
          </tr>
          <tr>
            <th>行为类型</th>
            <td><select name="type">
					<option value="1" <if condition="$info['type'] eq 1 ">selected</if>>控制器</option>
                    <option value="2" <if condition="$info['type'] eq 2 ">selected</if>>视图</option>
                    </select>
                    <span class="gray">控制器表示是在程序逻辑中的，视图表示是在模板渲染过程中的</span></td>
          </tr>
          <tr>
            <th>行为描述</th>
            <td><textarea name="remark" rows="2" cols="20" id="remark" class="inputtext" style="height:100px;width:500px;">{$info.remark}</textarea></td>
          </tr>
          <tr>
            <th>行为规则</th>
            <td><div class="cross" style="width:100%;">
                <ul id="J_ul_list_addItem" class="J_ul_list_public" style="margin-left:0px;">
                  <li><span style="width:40px;">规则ID</span><span style="width:40px;">排序</span><span>规则</span></li>
                <volist name="info.ruleList" id="vo">
                  <li><span style="width:40px;">{$vo.ruleid}</span><span style="width:40px;"><input type="test" name="listorder[{$vo.ruleid}]" class="input" value="{$vo.listorder}" style="width:35px;"></span><span style="width:500px;"><input type="test" name="rule[{$vo.ruleid}]" class="input" value="{$vo.rule}" style="width:450px;"><if condition=" $vo['system'] eq 0 ">&nbsp;<a href="" class="J_ul_list_remove">删除</a></if></span></li>
                </volist>
                </ul>
              </div>
              <a href="" class="link_add Js_ul_list_add" data-related="addItem">添加规则</a></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <input type="hidden" name="id" value="{$info.id}"/>
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">修改</button>
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
		<span style="width:40px;"></span><span style="width:40px;"><input type="test" name="newlistorder[' + new_key + ']" class="input" value="" style="width:35px;"></span><span style="width:500px;"><input type="test" name="newrule[' + new_key + ']" class="input" value="" style="width:450px;"></span>\
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
