<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
      <literal>
    <p>实现伪静态地址，规则需要自己写，同时也要服务器支持！<br/>
      例如实现伪静态这样的：http://www.ztbcms.com/tag-标签.html ，URL规则是：tag-{$tag}.html|/tag-{$tag}-{$page}.html，服务器上设置，如果是.htaccess则规则是：RewriteRule ^(tag|Tag)-(.*?)\.html$ /Tags/Index/index/tag/$2</p>
      </literal>
  </div>
  <div class="h_a">规则内容</div>
  <form action="{:U("Urlrule/add")}" method="post" name="myform" class="J_ajaxForm">
    <div class="table_full">
      <table width="100%" cellpadding="2" cellspacing="1" class="table_form">
        <tr>
          <th width="100">URL规则名称 :</th>
          <td><input type="text" class="input" name="file" id="file" size="20" value=""></td>
        </tr>
        <tr>
          <th>模块名称 :</th>
          <td><select name='module' id='module'>
              <volist name="Module" id="r">
                <option value="{$r['module']}" >{$r['name']}</option>
              </volist>
            </select></td>
        </tr>
        <tr>
          <th>是否生成静态？ :</th>
          <td><input type="radio" value="1" name="ishtml"/>
            是
            <input type="radio" value="0" name="ishtml" checked />
            否 </td>
        </tr>
        <tr>
          <th>URL示例 :</th>
          <td><input type="text" class="input length_5" name="example" id="example" value="{$data.example}"></td>
        </tr>
        <tr>
          <th>URL规则 :</th>
          <td><input type="text" class="input length_6" name="urlrule" id="urlrule" value="{$data.urlrule}">
          <br/>如果以“=”开头，表示以自定义处理函数返回路径。函数存放于Content\Common\urlrule.php文件中。</td>
        </tr>
        <tr>
          <th>可用变量 :</th>
          <td><span>父栏目路径：
            <input type="text" class="input" name="f1" value="<literal>{$categorydir}</literal> " size="15" >
            ，栏目目录：
            <input type="text" class="input" name="f1" value="<literal>{$catdir}</literal>" size="10" >
            <div class="bk6"></div>
            年：
            <input type="text" class="input" name="f1" value="<literal>{$year}</literal>" size="7" >
            月：
            <input type="text" class="input" name="f1" value="<literal>{$month}</literal>" size="9" >
            ，日：
            <input type="text" class="input" name="f1" value="<literal>{$day}</literal>" size="7" >
            ID：
            <input type="text" class="input" name="f1" value="<literal>{$id}</literal>" size="4" >
            ， 分页：
            <input type="text" class="input" name="f1" value="<literal>{$page}</literal>" size="7" ></span></td>
        </tr>
      </table>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
