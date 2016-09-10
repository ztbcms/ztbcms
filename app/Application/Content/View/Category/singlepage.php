<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<style>
.pop_nav{
	padding: 0px;
}
.pop_nav ul{
	border-bottom:1px solid #266AAE;
	padding:0 5px;
	height:25px;
	clear:both;
}
.pop_nav ul li.current a{
	border:1px solid #266AAE;
	border-bottom:0 none;
	color:#333;
	font-weight:700;
	background:#F3F3F3;
	position:relative;
	border-radius:2px;
	margin-bottom:-1px;
}

</style>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="pop_nav">
    <ul class="J_tabs_nav">
      <li class="current"><a href="javascript:;;">基本属性</a></li>
      <li class=""><a href="javascript:;;">选项设置</a></li>
      <li class=""><a href="javascript:;;">模板设置</a></li>
      <li class=""><a href="javascript:;;">生成设置</a></li>
      <li class=""><a href="javascript:;;">权限设置</a></li>
      <li class=""><a href="javascript:;;">扩展字段</a></li>
    </ul>
  </div>
  <form class="J_ajaxForms" name="myform" id="myform" action="{:U("Category/add")}" method="post">
    <div class="J_tabs_contents">
      <div>
        <div class="h_a">基本属性</div>
        <div class="table_full">
          <table width="100%" class="table_form ">
            <tr>
              <th width="200">上级栏目：</th>
              <td><select name="info[parentid]" id="parentid">
                  <option value='0'>≡ 作为一级栏目 ≡</option>
                  {$category}
                </select></td>
            </tr>
            <tr>
              <th>栏目名称：</th>
              <td><input type="text" name="info[catname]" id="catname" class="input" value=""></td>
            </tr>
            <tr id="catdir_tr">
              <th>英文目录：</th>
              <td><input type="text" name="info[catdir]" id="catdir" class="input" value=""></td>
            </tr>
            <tr>
              <th>是否终级栏目：</th>
              <td><input name="info[child]" type="checkbox" id="child"  value="0">
                终极栏目 (<font color="#FF0000">终极栏目才可以 编辑/修改 信息</font>)</td>
            </tr>
            <tr>
              <th>栏目缩略图：</th>
              <td><Form function="images" parameter="info[image],image,'',content"/><span class="gray"> 双击可以查看图片！</span></td>
            </tr>
            <tr>
              <th>栏目简介：</th>
              <td><textarea name="info[description]" maxlength="255" style="width:300px;height:60px;"></textarea></td>
            </tr>
            <tr>
              <th>关闭动态访问：</th>
              <td><label><input name="setting[listoffmoving]" type="checkbox" value="1">关闭前台动态地址访问</label></td>
            </tr>
            <tr>
              <th>指定栏目地址：</th>
              <td><input type="text" name="setting[seturl]" id="seturl" class="input length_6" value=""><span class="gray"> 指定地址后，栏目url将变成这个！</span></td>
            </tr>
          </table>
        </div>
      </div>
      <div style="display:none">
        <div class="h_a">选项设置</div>
        <div class="table_full">
          <table width="100%" class="table_form ">
            <tr>
              <th width="200">是否在导航显示：</th>
              <td><ul class="switch_list cc ">
                  <li>
                    <label>
                      <input type='radio' name='info[ismenu]' value='1' checked>
                      <span>在导航显示</span></label>
                  </li>
                  <li>
                    <label>
                      <input type='radio' name='info[ismenu]' value='0'  >
                      <span>不在导航显示</span></label>
                  </li>
                </ul></td>
            </tr>
            <tr>
              <th>显示排序：</th>
              <td><input type="text" name="info[listorder]" id="listorder" class="input" value="0"></td>
            </tr>
            <tr>
              <th ><strong>META Title（栏目标题）</strong><br/>
                针对搜索引擎设置的标题</th>
              <td><input name='setting[meta_title]' type='text' id='meta_title' class="input" value='' size='60' maxlength='60'></td>
            </tr>
            <tr>
              <th ><strong>META Keywords（栏目关键词）</strong><br/>
                关键字中间用半角逗号隔开</th>
              <td><textarea name='setting[meta_keywords]' id='meta_keywords' style="width:90%;height:40px"></textarea></td>
            </tr>
            <tr>
              <th ><strong>META Description（栏目描述）</strong><br/>
                针对搜索引擎设置的网页描述</th>
              <td><textarea name='setting[meta_description]' id='meta_description' style="width:90%;height:50px"></textarea></td>
            </tr>
          </table>
        </div>
      </div>
      <div style="display:none">
        <div class="h_a">模板设置</div>
        <div class="table_full">
          <table width="100%" class="table_form ">
            <tr>
              <th width="200">单页模板：</th>
              <td><select name="setting[page_template]" id="page_template">
                  <option value="page<?php echo C("TMPL_TEMPLATE_SUFFIX")?>" selected>默认内容页：page<?php echo C("TMPL_TEMPLATE_SUFFIX")?></option>
                  <volist name="tp_page" id="vo">
                    <option value="{$vo}">{$vo}</option>
                  </volist>
                </select>
                <span class="gray">新增模板以page_x<?php echo C("TMPL_TEMPLATE_SUFFIX")?>形式</span></td>
            </tr>
          </table>
        </div>
      </div>
      <div style="display:none">
        <div class="h_a">生成设置</div>
        <div class="table_full">
          <table width="100%" class="table_form ">
            <tr>
              <th width="200">生成Html：</th>
              <td><ul class="switch_list cc ">
                  <li>
                    <label>
                      <input type="radio" onClick="$('#category_php_ruleid').css('display','none');$('#category_html_ruleid').css('display','');$('#tr_domain').css('display','');$('.repagenum').css('display','');" value="1" name="setting[ishtml]">
                      <span>生成静态</span></label>
                  </li>
                  <li>
                    <label>
                      <input type="radio" onClick="$('#category_php_ruleid').css('display','');$('#category_html_ruleid').css('display','none');$('#tr_domain').css('display','none');$('.repagenum').css('display','none');" checked="" value="0" name="setting[ishtml]">
                      <span>不生成静态</span></label>
                  </li>
                </ul></td>
            </tr>
            <tr>
              <th>栏目页URL规则：</th>
              <td><div style="display:" id="category_php_ruleid"> {$category_php_ruleid} </div>
                <div style="display:none" id="category_html_ruleid"> {$category_html_ruleid} </div></td>
            </tr>
            <tr style="display:none" id="tr_domain">
              <th>绑定域名：</th>
              <td><input type="text" value="" size="50" class="input" id="url" name="info[url]">
                <span class="gray"> 域名可为空，格式应该为http://www.ztbcms.com/</span></td>
            </tr>
          </table>
        </div>
      </div>
      <div style="display:none">
        <div class="h_a">权限设置</div>
        <div class="table_full">
          <table width="100%" >
            <tr>
              <th width="200">角色权限：</th>
              <td><div class="user_group J_check_wrap">
                  <dl>
                  <volist name="Role_group" id="vo">
                    <dt>
                      <label><input  style="display: inline-block;" type="checkbox" data-direction="y" data-checklist="J_check_priv_roleid{$vo.id}" class="checkbox J_check_all" <if condition=" $vo['id'] eq 1 "> disabled</if> />{$vo.name}</label>
                    </dt>
                    <dd>
                      <label><input  class="J_check" type="checkbox" data-yid="J_check_priv_roleid{$vo.id}"  name="priv_roleid[]" <if condition=" $vo['id'] eq 1 "> disabled</if>  value="init,{$vo.id}" ><span>查看</span></label>
                      <label><input class="J_check" type="checkbox" data-yid="J_check_priv_roleid{$vo.id}" name="priv_roleid[]" <if condition=" $vo['id'] eq 1 "> disabled</if>  value="add,{$vo.id}" ><span>添加</span></label>
                      <label><input class="J_check" type="checkbox" data-yid="J_check_priv_roleid{$vo.id}" name="priv_roleid[]" <if condition=" $vo['id'] eq 1 "> disabled</if>  value="edit,{$vo.id}" ><span>修改</span></label>
                      <label><input class="J_check" type="checkbox" data-yid="J_check_priv_roleid{$vo.id}" name="priv_roleid[]" <if condition=" $vo['id'] eq 1 "> disabled</if>  value="delete,{$vo.id}" ><span>删除</span></label>
                      <label><input class="J_check" type="checkbox" data-yid="J_check_priv_roleid{$vo.id}" name="priv_roleid[]" <if condition=" $vo['id'] eq 1 "> disabled</if>  value="listorder,{$vo.id}" ><span>排序</span></label>
                      <label><input class="J_check" type="checkbox" data-yid="J_check_priv_roleid{$vo.id}" name="priv_roleid[]" <if condition=" $vo['id'] eq 1 "> disabled</if>  value="push,{$vo.id}" ><span>推送</span></label>
                      <label><input class="J_check" type="checkbox" data-yid="J_check_priv_roleid{$vo.id}" name="priv_roleid[]" <if condition=" $vo['id'] eq 1 "> disabled</if>  value="move,{$vo.id}" ><span>移动</span></label>
                    </dd>
                   </volist>
                  </dl>
                </div></td>
            </tr>
          </table>
        </div>
      </div>
      <div style="display:none;">
        <div class="h_a">添加字段</div>
        <div class="table_full">
        <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
          <tbody>
            <tr>
              <td width="50">键名:</td>
              <td><input type="text" class="input" name="extend_add[fieldname]" value="">
                注意：只允许英文、数组、下划线</td>
            </tr>
            <tr>
              <td>名称:</td>
              <td><input type="text" class="input" name="extend_add[setting][title]" value=""></td>
            </tr>
            <tr>
              <td>类型:</td>
              <td><select name="extend_add[type]" onChange="extend_type(this.value)">
                  <option value="input" >单行文本框</option>
                  <option value="textarea" >多行文本框</option>
                  <option value="password" >密码输入框</option>
                  <option value="radio" >单选框</option>
                  <option value="checkbox" >多选框</option>
                </select></td>
            </tr>
            <tr>
              <td>提示:</td>
              <td><input type="text" class="input length_4" name="extend_add[setting][tips]" value=""></td>
            </tr>
            <tr>
              <td>样式:</td>
              <td><input type="text" class="input length_4" name="extend_add[setting][style]" value=""></td>
            </tr>
            <tr class="setting_radio" style="display:none">
              <td>选项:</td>
              <td><textarea name="extend_add[setting][option]" disabled="true" style="width:380px; height:150px;">选项名称1|选项值1</textarea>
                注意：每行一个选项</td>
            </tr>
          </tbody>
        </table>
        </div>
        <div class="btn_wrap_pd add_extend"><a href="javascript:;;">添加字段</a></div>
        <div class="h_a">扩展字段列表(提示：请使用 <b>getCategory(栏目ID,'extend.<font color="#FF0000">键名</font>')</b> 的方式获取该值)</div>
        <div class="table_full">
        <table width="100%"  class="table_form extend_list">
        </table>
        </div>
      </div>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <input name="type" type="hidden" value="1">
        <button class="btn btn_submit mr10 " type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script type="text/javascript" src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script type="text/javascript">
//扩展字段处理
function extend_type(type){
	if(type == 'radio' || type == 'checkbox'){
		$('.setting_radio').show();
		$('.setting_radio textarea').attr('disabled',false);
	}else{
		$('.setting_radio').hide();
		$('.setting_radio textarea').attr('disabled',true);
	}
}
$(function(){
	//添加扩展字段
	$('.add_extend a').click(function(){
		var fieldname = $('input[name="extend_add[fieldname]"]').val();
		var type = $('select[name="extend_add[type]"]').val();
		var setting = {};
		setting.title = $('input[name="extend_add[setting][title]"]').val();
		setting.tips = $('input[name="extend_add[setting][tips]"]').val();
		setting.style = $('input[name="extend_add[setting][style]"]').val();
		setting.option = $('textarea[name="extend_add[setting][option]"]').val();
		
		if(fieldname == ''){
			alert("键名不能为空！");
			return false;
		}else{
			if(fieldname.replace(/^[0-9a-zA-Z_]{1,}$/g) != 'undefined'){
				alert("键名只允许数字，字母，下划线！");
				return false;
			}
		}
		if(type == ''){
			alert("类型不能为空！");
			return false;
		}
		if(setting.title == ''){
			alert("名称不能为空！");
			return false;
		}
		
		//单选框
		if(type == 'input'){
			$('.extend_list').append('<tr>\
          <th width="120">'+setting.title+'(<a href="javascript:;;" class="extend_del">删除</a>)</th>\
          <th class="y-bg">\
          <input type="text" class="input" style="'+setting.style+'"  name="extend['+fieldname+']" value="" placeholder="'+setting.tips+'">\
		  <input type="hidden" name="extend_config['+fieldname+'][fieldname]" value="'+fieldname+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][type]" value="'+type+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][title]" value="'+setting.title+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][tips]" value="'+setting.tips+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][style]" value="'+setting.style+'"/>\
		  <textarea name="extend_config['+fieldname+'][setting][option]" style="display:none;">'+setting.option+'</textarea>\
          </th>\
         </tr>');
		}else if(type == 'textarea'){
			//多行文本框
			$('.extend_list').append('<tr>\
          <th width="120">'+setting.title+'(<a href="javascript:;;" class="extend_del">删除</a>)</th>\
          <th class="y-bg">\
          <textarea name="extend['+fieldname+']" style="'+setting.style+'" placeholder="'+setting.tips+'"></textarea>\
		  <input type="hidden" name="extend_config['+fieldname+'][fieldname]" value="'+fieldname+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][type]" value="'+type+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][title]" value="'+setting.title+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][tips]" value="'+setting.tips+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][style]" value="'+setting.style+'"/>\
		  <textarea name="extend_config['+fieldname+'][setting][option]" style="display:none;">'+setting.option+'</textarea>\
          </th>\
         </tr>');
		}else if(type == 'password'){
			//密码框
			$('.extend_list').append('<tr>\
          <th width="120">'+setting.title+'(<a href="javascript:;;" class="extend_del">删除</a>)</th>\
          <th class="y-bg">\
          <input type="password" class="input" style="'+setting.style+'"  name="extend['+fieldname+']" value="" placeholder="'+setting.tips+'">\
		  <input type="hidden" name="extend_config['+fieldname+'][fieldname]" value="'+fieldname+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][type]" value="'+type+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][title]" value="'+setting.title+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][tips]" value="'+setting.tips+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][style]" value="'+setting.style+'"/>\
		  <textarea name="extend_config['+fieldname+'][setting][option]" style="display:none;">'+setting.option+'</textarea>\
          </th>\
         </tr>');
		}else if(type == 'radio'){
			//单选框
			if(setting.option == ''){
				alert('选项不能为空！');
				return false;
			}
			var html = '';
			var op = setting.option.split("\n");
			$.each(op,function(i,rs){
				var at = rs.split("|");
				html += '<label><input name="extend['+fieldname+']" value="'+at[1]+'" type="radio" > '+at[0]+'</label>';
			});
			$('.extend_list').append('<tr>\
          <th width="120">'+setting.title+'(<a href="javascript:;;" class="extend_del">删除</a>)</th>\
          <th class="y-bg">'+html+'\
		  <input type="hidden" name="extend_config['+fieldname+'][fieldname]" value="'+fieldname+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][type]" value="'+type+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][title]" value="'+setting.title+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][tips]" value="'+setting.tips+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][style]" value="'+setting.style+'"/>\
		  <textarea name="extend_config['+fieldname+'][setting][option]" style="display:none;">'+setting.option+'</textarea>\
          </th>\
         </tr>');
		}else if(type == 'checkbox'){
			//复选框
			if(setting.option == ''){
				alert('选项不能为空！');
				return false;
			}
			var html = '';
			var op = setting.option.split("\n");
			$.each(op,function(i,rs){
				var at = rs.split("|");
				html += '<label><input name="extend['+fieldname+'][]" value="'+at[1]+'" type="checkbox" > '+at[0]+'</label>';
			});
			$('.extend_list').append('<tr>\
          <th width="120">'+setting.title+'(<a href="javascript:;;" class="extend_del">删除</a>)</th>\
          <th class="y-bg">'+html+'\
		  <input type="hidden" name="extend_config['+fieldname+'][fieldname]" value="'+fieldname+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][type]" value="'+type+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][title]" value="'+setting.title+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][tips]" value="'+setting.tips+'"/>\
		  <input type="hidden" name="extend_config['+fieldname+'][setting][style]" value="'+setting.style+'"/>\
		  <textarea name="extend_config['+fieldname+'][setting][option]" style="display:none;">'+setting.option+'</textarea>\
          </th>\
         </tr>');
		}
		//清空
		$('input[name="extend_add[fieldname]"]').val('');
		$('select[name="extend_add[type]"]').val('');
		$('input[name="extend_add[setting][title]"]').val('');
		$('input[name="extend_add[setting][tips]"]').val('');
		$('input[name="extend_add[setting][style]"]').val('');
		//删除扩展字段
		$('.extend_list .extend_del').click(function(){
			$(this).parent('th').parent('tr').remove();
		});
	});
    Wind.use('validate', 'ajaxForm', 'artDialog', function () {
        var form = $('form.J_ajaxForms');
        //ie处理placeholder提交问题
        if ($.browser.msie) {
            form.find('[placeholder]').each(function () {
                var input = $(this);
                if (input.val() == input.attr('placeholder')) {
                    input.val('');
                }
            });
        }
        //表单验证开始
        form.validate({
			//是否在获取焦点时验证
			onfocusout:false,
			//是否在敲击键盘时验证
			onkeyup:false,
			//当鼠标掉级时验证
			onclick: false,
            //验证错误
            showErrors: function (errorMap, errorArr) {
				//errorMap {'name':'错误信息'}
				//errorArr [{'message':'错误信息',element:({})}]
				try{
					$(errorArr[0].element).focus();
					art.dialog({
						id:'error',
						icon: 'error',
						lock: true,
						fixed: true,
						background:"#CCCCCC",
						opacity:0,
						content: errorArr[0].message,
						cancelVal: '确定',
						cancel: function(){
							$(errorArr[0].element).focus();
						}
					});
				}catch(err){
				}
            },
            //验证规则
            rules: {
				"info[catname]":{
					required:true
				},
				"info[catdir]":{
					required:true
				}
			},
            //验证未通过提示消息
            messages: {
				"info[catname]":{
					required:"栏目名称不能为空！"
				},
				"info[catdir]":{
					required:"栏目目录不能为空！"
				}
			},
            //给未通过验证的元素加效果,闪烁等
            highlight: false,
            //是否在获取焦点时验证
            onfocusout: false,
            //验证通过，提交表单
            submitHandler: function (forms) {
                $(forms).ajaxSubmit({
                    url: form.attr('action'), //按钮上是否自定义提交地址(多按钮情况)
                    dataType: 'json',
                    beforeSubmit: function (arr, $form, options) {
                        
                    },
                    success: function (data, statusText, xhr, $form) {
                        if(data.status){
							//添加成功
							Wind.use("artDialog", function () {
							    art.dialog({
							        id: "succeed",
							        icon: "succeed",
							        fixed: true,
							        lock: true,
							        background: "#CCCCCC",
							        opacity: 0,
							        content: data.info,
									button:[
										{
											name: '继续添加？',
											callback:function(){
												reloadPage(window);
												return true;
											},
											focus: true
										},{
											name: '返回栏目管理页',
											callback:function(){
												window.location.href = "{:U('Category/index',array('catid'=>$catid))}";
												return true;
											}
										}
									]
							    });
							});
						}else{
							isalert(data.info);
						}
                    }
                });
            }
        });
    });
});
</script>
</body>
</html>
