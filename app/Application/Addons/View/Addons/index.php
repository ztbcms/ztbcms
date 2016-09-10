<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">说明</div>
  <div class="prompt_text">
    <ul>
      <li>插件管理可以很好的扩展网站运营中所需功能！</li>
      <li><font color="#FF0000">获取更多插件请到官方网站插件扩展中下载安装！安装非官方发表插件需谨慎，有被清空数据库的危险！</font></li>
      <li>官网地址：<font color="#FF0000">http://www.ztbcms.com</font>，<a href="http://www.ztbcms.com" target="_blank">立即前往</a>！</li>
    </ul>
  </div>
  <div class="table_list">
    <table width="100%">
      <thead>
        <tr>
          <td >名称</td>
          <td >标识</td>
          <td align="center">描述</td>
          <td align="center" width="50">状态</td>
          <td align="center" width="100">作者</td>
          <td align="center" width="50">版本</td>
          <td align="center" width="227">操作</td>
        </tr>
      </thead>
      <volist name="addons" id="vo">
      <tr>
        <td><if condition=" $vo['url'] "><a  href="{$vo.url}" target="_blank">{$vo.title}</a><else/>{$vo.title}</if></td>
        <td>{$vo.name}</td>
        <td>{$vo.description}</td>
        <td align="center"><if condition=" $vo['status'] eq null ">未安装<else/><if condition=" $vo['status'] eq 1 ">启用<else/>禁用</if></if></td>
        <td align="center">{$vo.author}</td>
        <td align="center">{$vo.version}</td>
        <td align="center">
          <if condition=" $vo['uninstall'] ">
          <a  href="{:U('Addons/install', array('addonename'=>$vo['name'])  )}" class="btn btn_submit btn_big">安装</a>
          <else/>
          <if condition=" $vo['config'] ">
          <a  href="{:U('Addons/config', array('id'=>$vo['id'])  )}" class="btn btn_submit btn_big">设置</a>
          </if>
          <if condition=" $vo['status'] ">
          <a  href="{:U('Addons/status', array('id'=>$vo['id'])  )}" class="btn btn_big">禁用</a>
          <else/>
          <a  href="{:U('Addons/status', array('id'=>$vo['id'])  )}" class="btn btn_big">启用</a>
          </if>
          <a  href="{:U('Addons/uninstall', array('id'=>$vo['id'])  )}" class="btn btn_big">卸载</a>
          </if>
          <a  href="{:U('Addons/unpack', array('addonname'=>$vo['name'])  )}" class="btn btn_big">打包</a>
         </td>
      </tr>
      </volist>
    </table>
  </div>
  <div class="p10">
        <div class="pages">{$Page}</div>
   </div>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
if ($('button.J_ajax_upgrade').length) {
    Wind.use('artDialog', function () {
        $('.J_ajax_upgrade').on('click', function (e) {
            e.preventDefault();
           var $_this = this,$this = $(this), url = $this.data('action'), msg = $this.data('msg'), act = $this.data('act');
            art.dialog({
                title: false,
                icon: 'question',
                content: '确定要卸载吗？',
                follow: $_this,
                close: function () {
                    $_this.focus();; //关闭时让触发弹窗的元素获取焦点
                    return true;
                },
                ok: function () {
                    $.getJSON(url).done(function (data) {
                        if (data.state === 'success') {
                            if (data.referer) {
                                location.href = data.referer;
                            } else {
                                reloadPage(window);
                            }
                        } else if (data.state === 'fail') {
                            art.dialog.alert(data.info);
                        }
                    });
                },
                cancelVal: '关闭',
                cancel: true
            });
        });

    });
}
</script>
</body>
</html>
