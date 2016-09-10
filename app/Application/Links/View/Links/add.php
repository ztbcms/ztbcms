<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">链接详情</div>
  <form name="myform" action="{:U('Links/add')}" method="post" class="J_ajaxForm">
  <div class="table_full">
  <table width="100%">
        <tbody>
        <tr>
          <th width="200">链接名称</th>
          <td><input type="text" name="name" value="" class="input" id="name" size="30"></td>
        </tr>
        <tr>
          <th>地址</th>
          <td><input type="text" name="url" value="" class="input" id="url" size="30" style=" width:300px;"></td>
        </tr>
        <tr>
          <th>所属分类</th>
          <td>
          <select name="termsid">
            <option value="">==分类选择==</option>
            <volist name="Terms" id="vo">
            <option value="{$vo.id}">{$vo.name}</option>
            </volist>
          </select> 创建新分类：<input type="text" name="terms[name]" value="" class="input" size="30"></td>
        </tr>
        <tr>
          <th>Logo</th>
          <td><?php echo Form::images('image', 'image', '', 'links');?></td>
        </tr>
        <tr>
          <th>打开方式</th>
          <td>
          <select name="target">
            <option value="_self">默认打开方式</option>
            <option value="_blank">新窗口打开</option>
          </select></td>
        </tr>
        <tr>
          <th>链接是否可见</th>
          <td>
          <select name="visible">
            <option value="1">可见</option>
            <option value="0">不可见</option>
          </select></td>
        </tr>
        <tr>
          <th>RSS地址</th>
          <td><input type="text" name="rss" value="" class="input" id="rss" size="30" style=" width:300px;"></td>
        </tr>
        <tr>
          <th>链接描述</th>
          <td><textarea name="description" rows="2" cols="20" id="description" class="inputtext" style="height:100px;width:500px;"></textarea></td>
        </tr>
        <tr>
          <th>链接详细介绍</th>
          <td><textarea name="notes" rows="2" cols="20" id="notes" class="inputtext" style="width:700px;"></textarea></td>
        </tr>
      </tbody>
      </table>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>

</body>
</html>
