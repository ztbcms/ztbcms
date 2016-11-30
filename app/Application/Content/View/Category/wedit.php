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
    </ul>
  </div>
  <form class="J_ajaxForms" name="myform" id="myform" action="{:U("Category/edit")}" method="post">
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
              <td><input type="text" name="info[catname]" id="catname" class="input" value="{$data.catname}"></td>
            </tr>
            <tr>
              <th>栏目缩略图：</th>
              <td><Form function="images" parameter="info[image],image,$data['image'],content"/><span class="gray"> 双击可以查看图片！</span></td>
            </tr>
            <tr>
              <th>栏目简介：</th>
              <td><textarea name="info[description]" maxlength="255" style="width:300px;height:60px;">{$data.description}</textarea></td>
            </tr>
            <tr>
              <th>链接地址：</th>
              <td><input type="text" name="info[url]" id="catname" class="input length_6" value="{$data.url}"></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <input name="catid" type="hidden" value="{$data.catid}">
        <input name="type" type="hidden" value="2">
        <input name="info[child]" type="hidden"  value="0">
        <input name="info[modelid]" type="hidden"  value="9999">
        <input name="info[catdir]" type="hidden"  value="0">
        <button class="btn btn_submit mr10 " type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script type="text/javascript" src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/javascript" src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script type="text/javascript">
$(function(){
    Wind.use('validate', 'ajaxForm', 'artDialog', function () {
        var form = $('form.J_ajaxForms');

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
				"info[url]":{
					required:true
				}
			},
            //验证未通过提示消息
            messages: {
				"info[catname]":{
					required:"栏目名称不能为空！"
				},
				"info[url]":{
					required:"链接地址不能为空！"
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
											name: '继续添加新栏目？',
											callback:function(){
												window.location.href = "{:U('Category/wadd')}";
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
