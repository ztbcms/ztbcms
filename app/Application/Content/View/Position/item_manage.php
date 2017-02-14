 
<Admintemplate file="Common/Head"/>
<body class="body_none" style="width:600px;">
<div class="wrap_pop ">
  <div class="h_a">信息管理</div>
  <form name="myform" action="{:U("item_manage")}" method="post" class="J_ajaxForm">
    <input type="hidden" name="posid" value="{$posid}"/>
    <input type="hidden" name="modelid" value="{$modelid}"/>
    <input type="hidden" name="id" value="{$id}"/>
    <div class="table_full"  style="overflow-x:hidden;">
    <table width="100%" class="table_form">
        <tr>
          <th  width="110">推荐位标题</th>
          <td><input type="text" name="data[title]" class="input" value="{$data['title']}" id="title" size="40">
            </input></td>
        </tr>
        <tr>
          <th>推荐位图片</th>
          <td><?php echo \Form::images('thumb','thumb',$data['thumb'],'content')?></td>
        </tr>
        <tr>
          <th>推荐时间</th>
          <td><?php echo \Form::date('data[inputtime]', date('Y-m-d H:i:s',$data['inputtime']), 1)?></td>
        </tr>
        <tr>
          <th>描述</th>
          <td><textarea name="data[description]" rows="2" cols="20" id="description" class="inputtext" style="height:200px;width:100%;">{$data['description']}</textarea></td>
        </tr>
        <tr>
          <th>原文章修改时同步？</th>
          <td>
          <input name="synedit"  value="0" type="radio" <if condition=" $synedit == '0' "> checked="checked"</if>>
            启用
            <input name="synedit" value="1" type="radio" <if condition=" $synedit == '1' "> checked="checked"</if>>
            关闭 </td>
        </tr>
      </table>
    </div>
     <div class="pop_bottom">
      <button class="btn fr" id="J_dialog_close" type="button">取消</button>
      <button type="submit" class="btn btn_submit J_ajax_submit_btn fr mr10">提交</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script type="text/javascript">
//信息管理
function item_manage() {
    Wind.use('ajaxForm', 'artDialog', 'iframeTools', function () {
        $(".J_ajaxForms").ajaxSubmit({
            url: $(".J_ajaxForms").attr('action'),
            //按钮上是否自定义提交地址(多按钮情况)
            dataType: 'json',
            beforeSubmit: function (arr, $form, options) {
				
            },
            success: function (data, statusText, xhr, $form) {
				setTimeout(function(){
					reloadPage(window);
				},1500);
                if (data.state === 'success') {
                    art.dialog.tips(data.info);
                } else if (data.state === 'fail') {
                    art.dialog.tips(data.info);
                }
            }
        });
    });
}
</script>
</body>
</html>
