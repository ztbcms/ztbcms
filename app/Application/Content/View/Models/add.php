 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">模型属性</div>
  <form action="{:U("add")}" method="post" class="J_ajaxForm" >
    <div class="table_full">
      <table width="100%"  class="table_form">
        <tr>
          <th width="120">模型名称：</th>
          <td class="y-bg"><input type="text" class="input" name="name" id="name" size="30" value="" placeholder="中文名"/></td>
        </tr>
        <tr>
          <th>模型表键名：</th>
          <td class="y-bg"><input type="text" class="input" name="tablename" id="tablename" size="30" value="" placeholder="英文，小写"/></td>
        </tr>
        <tr>
          <th>描述：</th>
          <td class="y-bg"><input type="text" class="input" name="description" id="description" value=""  size="30"/></td>
        </tr>
       <!--  栏目模板选择 -->
        <tr>
          <th>栏目首页模板：</th>
          <td class="y-bg">
            <!-- <input type="text" class="input" name="category_template" id="category_template" value=""  size="30"/> -->
          <select name="category_template" id="category_template">
                  <option value="category<?php echo C("TMPL_TEMPLATE_SUFFIX")?>" selected>默认栏目首页：category<?php echo C("TMPL_TEMPLATE_SUFFIX")?></option>
                  <volist name="tp_category" id="vo">
                    <option value="{$vo}">{$vo}</option>
                  </volist>
                </select>
                <span class="gray">新增模板以category_x<?php echo C("TMPL_TEMPLATE_SUFFIX")?>形式</span>
          </td>
        </tr>
                <tr>
          <th>栏目列表模板：</th>
          <td class="y-bg">
            <!-- <input type="text" class="input" name="list_template" id="list_template" value=""  size="30"/> -->
            <select name="list_template" id="list_template">
                  <option value="list<?php echo C("TMPL_TEMPLATE_SUFFIX")?>" selected>默认列表页：list<?php echo C("TMPL_TEMPLATE_SUFFIX")?></option>
                  <volist name="tp_list" id="vo">
                    <option value="{$vo}">{$vo}</option>
                  </volist>
                </select>
                <span class="gray">新增模板以list_x<?php echo C("TMPL_TEMPLATE_SUFFIX")?>形式</span>
          </td>
        </tr>
                <tr>
          <th>内容详情模板：</th>
          <td class="y-bg">
            <!-- <input type="text" class="input" name="show_template" id="show_template" value=""  size="30"/> -->
            <select name="show_template" id="show_template">
                  <option value="show<?php echo C("TMPL_TEMPLATE_SUFFIX")?>" selected>默认内容页：show<?php echo C("TMPL_TEMPLATE_SUFFIX")?></option>
                  <volist name="tp_show" id="vo">
                    <option value="{$vo}">{$vo}</option>
                  </volist>
                </select>
                <span class="gray">新增模板以show_x<?php echo C("TMPL_TEMPLATE_SUFFIX")?>形式</span>
          </td>
        </tr>
        <!-- 模型设置后台模板，则直接使用模型设置的模板 -->
        <tr>
          <th>后台信息列表模板：</th>
          <td class="y-bg"><input type="text" class="input" name="list_customtemplate" id="list_customtemplate" value=""  size="30" placeholder="默认后台列表页，如admin_xx.php"/>
          <span class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Listtemplate/里增加文件</span>
          </td>
          
        </tr>

          <tr>
              <th>后台信息添加模板：</th>
              <td class="y-bg"><input type="text" class="input" name="add_customtemplate" value=""  size="30" placeholder="默认后台列表页，如add_xx.php"/>
                  <span class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Addtemplate/里增加文件</span>
              </td>
          </tr>
          <tr>
              <th>后台信息编辑模板：</th>
              <td class="y-bg"><input type="text" class="input" name="edit_customtemplate" value=""  size="30" placeholder="默认后台列表页，如edit_xx.php"/>
                  <span class="gray">模板名称<b>不需要</b>后缀，不设置为使用默认列表，增加列表模板可在/app/Application/Content/View/Edittemplate/里增加文件</span>
              </td>
          </tr>

      </table>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">添加</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
