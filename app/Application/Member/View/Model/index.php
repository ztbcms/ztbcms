<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text"> 1、请谨慎删除模型，当模型里存在会员时请使用“移动”功能将该模型里的会员移动到其他会员模型中。<br />
    2、移动模型会员，将会把原有模型里的会员信息删除，将不能修复。 </div>
  <div class="table_list">
    <table width="100%" cellspacing="0">
      <thead>
        <tr>
          <td align="left">ID</td>
          <td align="left">模型名称</td>
          <td align="left">模型描述</td>
          <td align="left">数据表名</td>
          <td align="center">状态</td>
          <td align="center">操作</td>
        </tr>
      </thead>
      <tbody>
        <volist name="data" id="vo">
          <tr>
            <td align="left">{$vo.modelid}</td>
            <td align="left">{$vo.name}</td>
            <td align="left">{$vo.description}</td>
            <td align="left"><?php echo C("DB_PREFIX");?>{$vo.tablename}</td>
            <td align="center"><font color="red">
              <if condition=" $vo['disabled'] eq '1' ">Ⅹ
                <else />
                √</if>
              </font></td>
            <td align="center"><a href="{:U('Field/index' , array("modelid"=>$vo['modelid']) )}">字段管理</a> | <a href="{:U('Model/edit' , array("modelid"=>$vo['modelid']) )}">修改</a> | <a href="{:U('Model/move' , array("modelid"=>$vo['modelid']) )}">移动</a> | <a href="javascript:;" onClick="model_delete(this,'{$vo.modelid}','确认要删除 『 {$vo.name} 』 吗？删除后，数据表将同时被删除！',0)">删除</a></td>
          </tr>
        </volist>
      </tbody>
    </table>
  </div>
</div>
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script type="text/javascript">
//删除模型
function model_delete(obj, id, name, items) {
    if (items) {
        isalert('该模型下已经有内容，不能删除');
        return false;
    }
    Wind.use('artDialog', 'iframeTools', function () {
        art.dialog({
            content: name,
            fixed: true,
            style: 'confirm',
            id: 'model_delete'
        }, function () {
            $.get(GV.DIMAUB + 'index.php?g=Member&m=Model&a=delete&modelid=' + id + '', function (data) {
                if (data.status) {
                    $(obj).parent().parent().fadeOut("slow");
                } else {
					 isalert(data.info,'error');
                }
            }, "json")
        });
    });
};
</script>
</body>
</html>
