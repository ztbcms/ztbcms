<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <p>绑定好域名后，还需要进入DNS域名设置，添加对应的域名指向到 <?php echo SITE_PATH;?>/ 目录才可以！<br/>同时默认首页需要以index.php为最高优先级。同时COOKIE和SESSION作用域需要修改成“.domain.com”这样的。</p>
  </div>
  <div class="table_list">
  <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td width="20" align="center">ID</td>
            <td width="200" align="center">模块</td>
            <td>域名</td>
            <td width="60" align="center">状态</td>
            <td width="180" align="center">操作</td>
          </tr>
        </thead>
        <tbody>
        <volist name="data" id="vo">
          <tr>
            <td align="center">{$vo.id}</td>
            <td align="center">{$vo.module}</td>
            <td>{$vo.domain}</td>
            <td align="center"><if condition=" $vo['status'] ">开启<else/>关闭</if></td>
            <td align="center"><a href="javascript:confirmurl('{:U("Domains/delete",array("id"=>$vo['id']))}','确认要删除吗？')">删除</a> | <a href="{:U("Domains/edit",array("id"=>$vo['id']))}">编辑</a></td>
          </tr>
         </volist>
        </tbody>
      </table>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
