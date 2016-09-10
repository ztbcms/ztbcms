<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap jj">
  <div class="nav">
    <ul class="cc">
      <li class="current"><a href="{:U('Adminmanage/myinfo')}">修改个人信息</a></li>
    </ul>
  </div>
  <!--====================用户编辑开始====================-->
  <form class="J_ajaxForm" id="J_bymobile_form" action="{:U("Adminmanage/myinfo")}" method="post">
    <input type="hidden" value="{$data.id}" name="id"/>
    <input type="hidden" value="{$data.username}" name="username"/>
    <div class="h_a">用户信息</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col/>
        <thead>
          <tr>
            <th>ID</th>
            <td>{$data.id}</td>
          </tr>
        </thead>
        <tr>
          <th>用户名</th>
          <td>{$data.username}</td>
        </tr>
        <tr>
          <th>姓名</th>
          <td><input name="nickname" type="text" class="input length_5 required" value="{$data.nickname}">
           <span id="J_reg_tip_nickname" role="tooltip"></span></td>
        </tr>
        <tr>
          <th>E-mail</th>
          <td><input name="email" type="text" class="input length_5" value="{$data.email}"></td>
        </tr>
        <tr>
          <th>备注</th>
          <td><textarea id="J_textarea" name="remark" style="width:400px;height:100px;">{$data.remark}</textarea></td>
        </tr>
      </table>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button type="submit" class="btn btn_submit  J_ajax_submit_btn">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/jscript">
Wind.use('validate','ajaxForm', function(){
	//表单js验证开始
	$("#J_bymobile_form").validate({
		//当未通过验证的元素获得焦点时,并移除错误提示
		focusCleanup:true,
		//错误信息的显示位置
		errorPlacement:function(error, element){
			//错误提示容器
			$('#J_reg_tip_'+ element[0].name).html(error);
		},
		//获得焦点时不验证 
		focusInvalid : false,
		onkeyup: false,
		//设置验证规则
		rules:{
			nickname:{
				required:true,//验证条件：必填
				byteRangeLength: [3,15]
			}
		},
		//设置错误信息
		messages:{
			nickname:{
				required: "请填写用户名", 
				byteRangeLength: "用户名必须在3-15个字符之间(一个中文字算2个字符)"
			}
		}
	});
});
</script>
</body>
</html>