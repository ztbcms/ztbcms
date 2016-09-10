<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">链接详情</div>
  <form name="myform" action="{:U('Links/edit')}" method="post" class="J_ajaxForm">
  <div class="table_full">
  <table width="100%" class="table_form contentWrap">
        <tbody>
        <tr>
          <th width="200">链接名称</th>
          <td><input type="text" name="name" value="{$name}" class="input" id="name" size="30"></td>
        </tr>
        <tr>
          <th>地址</th>
          <td><input type="text" name="url" value="{$url}" class="input" id="url" size="30" style=" width:300px;"></td>
        </tr>
        <tr>
          <th>所属分类</th>
          <td>
          <select name="termsid">
            <option value="">==分类选择==</option>
            <volist name="Terms" id="vo">
            <option value="{$vo.id}" <if condition=" $vo[id] == $termsid "> selected</if>>{$vo.name}</option>
            </volist>
          </select> 创建新分类：<input type="text" name="terms[name]" value="" class="input" size="30"></td>
        </tr>
        <tr>
          <th>Logo</th>
          <td><?php echo Form::images('image', 'image', $image , 'links');?></td>
        </tr>
        <tr>
          <th>打开方式</th>
          <td>
          <select name="target">
            <option value="_self" <if condition=" $target == '_self' "> selected</if>>默认打开方式</option>
            <option value="_blank" <if condition=" $target == '_blank' "> selected</if>>新窗口打开</option>
          </select></td>
        </tr>
        <tr>
          <th>链接是否可见</th>
          <td>
          <select name="visible">
            <option value="1" <if condition=" $visible == '1' "> selected</if>>可见</option>
            <option value="0" <if condition=" $visible == '0' "> selected</if>>不可见</option>
          </select></td>
        </tr>
        <tr>
          <th>RSS地址</th>
          <td><input type="text" name="rss" value="{$rss}" class="input" id="rss" size="30" style=" width:300px;"></td>
        </tr>
        <tr>
          <th>链接描述</th>
          <td><textarea name="description" rows="2" cols="20" id="description" class="inputtext" style="height:100px;width:500px;">{$description}</textarea></td>
        </tr>
        <tr>
          <th>链接详细介绍</th>
          <td><textarea name="notes" rows="2" cols="20" id="notes" class="inputtext" style="width:700px;">{$notes}</textarea></td>
        </tr>
      </tbody>
      </table>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
        <input name="id" type="hidden" value="{$id}" />
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>

</body>
</html>
