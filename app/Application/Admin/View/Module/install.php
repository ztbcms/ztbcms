 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">模块信息</div>
  <form class="J_ajaxForm" action="{:U('Module/install')}" method="post">
  <div class="table_full">
    <table width="100%"  class="table_form">
      <tr>
        <th width="150">模块名称：</th>
        <td >{$config.modulename}</td>
      </tr>
      <tr>
        <th>模块版本：</th>
        <td >{$config.version}</td>
      </tr>
      <tr>
        <th>ZTBCMS最低版本：</th>
        <td ><if condition=" $config['adaptation'] ">{$config.adaptation}<else /><font color="#FF0000">没有标注，存在风险</font></if>
        <if condition=" $version == false && isset($version) "><br/><font color="#FF0000">该模板最低只支持ZTBCMS {$config.adaptation} 版本，请升级后再安装，避免不必要是损失！</font></if>
        </td>
      </tr>
      <if condition=" !empty($config['depend']) ">
      <tr>
        <th>依赖模块：</th>
        <td ><?php echo implode('|',$config['depend']) ?></td>
      </tr>
      </if>
      <tr>
        <th>模块简介：</th>
        <td >{$config.introduce}</td>
      </tr>
      <tr>
        <th >作者：</th>
        <td >{$config.author}</td>
      </tr>
      <tr>
        <th>E-mail：</th>
        <td >{$config.authoremail}</td>
      </tr>
      <tr>
        <th>作者主页：</th>
        <td >{$config.authorsite}</td>
      </tr>
    </table>
    </div>
     <div class="">
      <div class="btn_wrap_pd">
        <input type="hidden" name="module" value="{$config.module}">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">确定安装</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
