 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap jj">
  <Admintemplate file="Common/Nav"/>
  <div class="common-form">
    <form method="post" class="J_ajaxForm" action="{:U('Menu/add')}">
      <div class="h_a">菜单信息</div>
      <div class="table_list">
        <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
          <tbody>
            <tr>
              <td width="140">上级:</td>
              <td><select name="parentid">
                  <option value="0">作为一级菜单</option>
                     {$select_categorys}
                </select></td>
            </tr>
            <tr>
              <td>名称:</td>
              <td><input type="text" class="input" name="name" value=""></td>
            </tr>
            <tr>
              <td>模块:</td>
              <td><input type="text" class="input" name="app" id="app" value="Admin"></td>
            </tr>
            <tr>
              <td>控制器:</td>
              <td><input type="text" class="input" name="controller" id="controller" value=""></td>
            </tr>
            <tr>
              <td>方法:</td>
              <td><input type="text" class="input" name="action" id="action" value=""></td>
            </tr>
            <tr>
              <td>参数:</td>
              <td><input type="text" class="input length_5" name="parameter" value="">
                例:groupid=1&amp;type=2</td>
            </tr>
            <tr>
              <td>备注:</td>
              <td><textarea name="remark" rows="5" cols="57">{$remark}</textarea></td>
            </tr>
            <tr>
              <td>状态:</td>
              <td><select name="status">
                  <option value="1" <eq name="status" value="1">selected</eq>>显示</option>
                  <option value="0" <eq name="status" value="0">selected</eq>>不显示</option>
                </select>需要明显不确定的操作时建议设置为不显示，例如：删除，修改等</td>
            </tr>
            <tr>
              <td>类型:</td>
              <td><select name="type">
                  <option value="1" selected>权限认证+菜单</option>
                  <option value="0" >只作为菜单</option>
                </select>
                注意：“权限认证+菜单”表示加入后台权限管理，纯粹是菜单项请不要选择此项。</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="">
        <div class="btn_wrap_pd">
          <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">添加</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
