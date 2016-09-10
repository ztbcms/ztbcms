<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="h_a">新建发布方案</div>
  <form name="myform" action="{:U('Node/import_program_edit', array('nodeid'=>$nodeid,'type'=>$type,'ids'=>$ids,'catid'=>$catid,'id'=>$id) )}" method="post" >
    <div class="table_full">
      <table width="100%">
        <tr>
          <th width="120">方案名称：</th>
          <td> <input type="text" name="name" value=" {$name}"   class="input"> </td>
        </tr>
        <tr>
          <th width="120">栏目：</th>
          <td> {$cat['catname']} </td>
        </tr>
        <tr>
          <th width="120">自动提取摘要：</th>
          <td><label>
              <input name="add_introduce" type="checkbox"  value="1" <if condition=" $config['add_introduce'] eq '1' "> checked</if>>
              是否截取内容</label>
            <input type="text" name="introcude_length" value="{$config['introcude_length']}" size="3" class="input">
            字符至内容摘要 </td>
        </tr>
        <tr>
          <th width="120">自动提取缩略图：</th>
          <td><label><input type='checkbox' name='auto_thumb' value="1" <if condition=" $config['auto_thumb'] eq '1' "> checked</if>>是否获取内容第</label>
            <input type="text" name="auto_thumb_no" value="{$config['auto_thumb_no']}" size="2" class="input">张图片作为标题图片 </td>
        </tr>
        <tr>
          <th width="120">导入文章状态：</th>
          <td><label><input type="radio" name="content_status" id="_99" <if condition=" $config['content_status'] eq '99' "> checked</if> value="99"> 审核通过</label> <label><input type="radio" name="content_status" id="_1" value="1" <if condition=" $config['content_status'] eq '1' "> checked</if>> 待审核</label> </td>
        </tr>
      </table>
    </div>
    <div class="bk10"></div>
    <div class="h_a">标签与数据库对应关系</div>
    <div class="prompt_text">
       <div class="h_a">说明</div>
          <ol>
            <li>内容入库前可以添加 “处理函数” ，处理函数必须有返回值。</li>
            <li>处理函数可以添加到 <font color="#FF0000">Collection</font> 模块目录下的 <font color="#FF0000">Funs</font> 文件夹下的 <font color="#FF0000">funs.php</font> 文件中。</li>
            <li><b>常用函数</b>：过滤空格(<b>trim</b>)、MD5加密(<b>md5</b>)</li>
          </ol>
    </div>
    <div class="table_list">
      <table width="100%">
        <thead>
          <tr>
            <td align="left">原数据库字段</td>
            <td align="left">数据库字段说明</td>
            <td align="left">标签字段（采集填充结果）</td>
            <td align="left" width="200">当为空时填充内容</td>
            <td align="left" width="200">处理函数</td>
          </tr>
        </thead>
<?php
	foreach($model_field as $k=>$v) {
		if (in_array($v['formtype'], array('catid', 'typeid', 'posids', 'groupid', 'readpoint','template'))) continue;
?>
          <tr>
            <td>{$v.field}</td>
            <td>{$v.name}</td>
            <td><input type="hidden" name="model_field[]" value="<?php echo $v['field']?>">
			<?php echo Form::select($node_field, $config['map'][$v['field']], 'name="node_field[]"')?></td>
            <td><textarea name="default[]">{$config['default'][$v['field']]}</textarea></td>
            <td><input type="text" name="funcs[]" class="input" value="{$config['funcs'][$v['field']]}"></td>
          </tr>
<?php
	}
?>
      </table>
    </div>
    <div class="">
      <button type="submit" class="btn btn_submit J_ajax_submit_btn mr10">提交</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
