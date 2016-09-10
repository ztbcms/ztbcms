<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <form method="post" class="J_ajaxForm" action="{:U('Addons/create')}">
    <div class="h_a">基本设置</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>标识名</th>
          <td><input type="text" name="info[name]" class="input length_3" value="" ></td>
          <td><div class="fun_tips">只能是英文开头，且首字母大写，标识只能包含英文、数子、下划线</div></td>
        </tr>
        <tr>
          <th>插件名称</th>
          <td><input type="text" name="info[title]" class="input length_3" value="" ></td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>插件签名</th>
          <td><input type="text" name="info[sign]" class="input length_3" value="" ></td>
          <td><div class="fun_tips">用于标识插件唯一标识，请不要使用特殊字符，推荐英文、数子、字母组成，或者md5加密后作为签名</div></td>
        </tr>
        <tr>
          <th>版本</th>
          <td><input type="text" name="info[version]" class="input length_3" value="" ></td>
          <td><div class="fun_tips">例如：1.0.1</div></td>
        </tr>
        <tr>
          <th>作者</th>
          <td><input type="text" name="info[author]" class="input length_3" value="" ></td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>描述</th>
          <td><textarea name="info[description]" style="width:300px; height:100px;"></textarea></td>
          <td><div class="fun_tips"></div></td>
        </tr>
      </table>
    </div>
    <div class="h_a">高级设置</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th>插件是否有后台</th>
          <td><ul class="switch_list cc">
              <li>
                <label>
                  <input name="info[has_adminlist]" value="1" type="radio" >
                  <span>开启</span></label>
              </li>
              <li>
                <label>
                  <input name="info[has_adminlist]" value="0" type="radio" checked>
                  <span>关闭</span></label>
              </li>
            </ul></td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <tr>
          <th>是否需要外部访问</th>
          <td><ul class="switch_list cc">
              <li>
                <label>
                  <input name="info[has_outurl]" value="1" type="radio" >
                  <span>开启</span></label>
              </li>
              <li>
                <label>
                  <input name="info[has_outurl]" value="0" type="radio" checked>
                  <span>关闭</span></label>
              </li>
            </ul></td>
          <td><div class="fun_tips">开启后前台可以通过 http://域名/index.php?g=Addons&m=插件标识 进行访问</div></td>
        </tr>
        <tr>
          <th>是否需要配置</th>
          <td><ul class="switch_list cc">
              <li>
                <label>
                  <input name="info[has_config]" value="1" type="radio" >
                  <span>开启</span></label>
              </li>
              <li>
                <label>
                  <input name="info[has_config]" value="0" type="radio" checked>
                  <span>关闭</span></label>
              </li>
            </ul></td>
          <td><div class="fun_tips">可以进行插件相关的设置，代码中可以通过 getAddonConfig 方法获取配置！</div></td>
        </tr>
        <tr>
          <th>实现行为</th>
          <td><div class="cc ">
			<div class="fl">
				<h4>行为</h4>
				<select id="J_roles" size="10">
                <volist name="behaviorList" id="vo">
					<option value="{$key}">{$key}{$key}({$vo})</option>
				</volist>
                </select>
			</div>
			<a id="J_auth_role_add" href="" class="btn" style=" margin-top:5px;">添加 &gt;&gt;</a> <a id="J_auth_role_del" href="" class="btn" style=" margin-top:5px;">&lt;&lt; 移除</a>
			<div class="">
				<h4>拥有的行为</h4>
				<select id="J_user_roles" name="info[rule_list][]" size="10" multiple="multiple">
				</select>
			</div>
		</div></td>
          <td><div class="fun_tips"></div></td>
        </tr>
      </table>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script> 
<script>
//添加行为
$('#J_auth_role_add').click(function (e) {
    e.preventDefault();
    var sel_val = $('#J_roles').val(),
        has_role = $('#J_user_roles > option[value = "' + sel_val + '"]');
    if (sel_val && !has_role.length) {
        $('#J_roles option:selected').clone().appendTo($('#J_user_roles'));
		$('#J_user_roles > option').prop('selected', true);
    }
});
//移除
$('#J_auth_role_del').click(function (e) {
    e.preventDefault();
    var user_sel_val = $('#J_user_roles').val();
    if (user_sel_val) {
        $('#J_user_roles > option[value = "' + user_sel_val + '"]').remove();
		$('#J_user_roles > option').prop('selected', true);
    }
});
</script>
</body>
</html>