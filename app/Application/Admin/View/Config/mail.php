 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">邮箱配置</div>
  <div class="table_full">
    <form method='post'   id="myform" class="J_ajaxForm"  action="{:U('Config/mail')}">
      <table width="100%"  class="table_form">
        <tr>
          <th width="120">邮件发送模式</th>
          <th class="y-bg"><input name="mail_type" checkbox="mail_type" value="1"  type="radio"  checked>
            SMTP 函数发送 </th>
        </tr>
        <tbody id="smtpcfg" style="">
          <tr>
            <th>邮件服务器</th>
            <th class="y-bg"><input type="text" class="input" name="mail_server" id="mail_server" size="30" value="{$Site.mail_server}"/></th>
          </tr>
          <tr>
            <th>邮件发送端口</th>
            <th class="y-bg"><input type="text" class="input" name="mail_port" id="mail_port" size="30" value="{$Site.mail_port}"/></th>
          </tr>
          <tr>
            <th>发件人地址</th>
            <th class="y-bg"><input type="text" class="input" name="mail_from" id="mail_from" size="30" value="{$Site.mail_from}"/></th>
          </tr>
          <tr>
            <th>发件人名称</th>
            <th class="y-bg"><input type="text" class="input" name="mail_fname" id="mail_fname" size="30" value="{$Site.mail_fname}"/></th>
          </tr>
          <tr>
            <th>密码验证</th>
            <th class="y-bg"><input name="mail_auth" id="mail_auth" value="1" type="radio"  <if condition=" $Site['mail_auth'] == '1' ">checked</if>> 开启 
            <input name="mail_auth" id="mail_auth" value="0" type="radio" <if condition=" $Site['mail_auth'] == '0' ">checked</if>> 关闭</th>
          </tr>
          <tr>
            <th>验证用户名</th>
            <th class="y-bg"><input type="text" class="input" name="mail_user" id="mail_user" size="30" value="{$Site.mail_user}"/></th>
          </tr>
          <tr>
            <th>验证密码</th>
            <th class="y-bg"><input type="password" class="input" name="mail_password" id="mail_password" size="30" value="{$Site.mail_password}"/></th>
          </tr>
        </tbody>
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
</body>
</html>
