
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap jj">
  <div class="nav">
    <ul class="cc">
      <li class="current"><a href="{:U('Adminmanage/chanpass')}">修改密码</a></li>
    </ul>
  </div>
  <!--====================用户编辑开始====================-->
  <form class="J_ajaxForm" action="{:U('Admin/Adminmanage/chanpass')}" method="post">
    <div class="h_a">用户信息</div>
    <div class="table_full">
      <table width="100%">
        <col class="th" />
        <col/>
        <thead>
          <tr>
            <th>用户名</th>
            <td> {$userInfo.username}</td>
          </tr>
        </thead>
        <tr>
          <th>旧密码</th>
          <td><input name="password" type="password" class="input length_5 required" value=""><span id="J_reg_tip_password" role="tooltip"></span></td>
        </tr>
        <tr>
          <th>新密码</th>
          <td><input name="new_password" type="password" class="input length_5 required" value="">
           <span id="J_reg_tip_new_password" role="tooltip"></span></td>
        </tr>
        <tr>
          <th>重复新密码</th>
          <td><input name="new_pwdconfirm" type="password" class="input length_5 required" value=""><span id="J_reg_tip_new_pwdconfirm" role="tooltip"></span></td>
        </tr>
      </table>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button type="submit" class="btn btn_submit  chanpass_ajax_submit_btn">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script type="text/jscript">
$(function () {
    Wind.use('ajaxForm', function () {
		$('input[name=password]').on("focusout",function(e){
			var passwords = $('input[name=password]').fieldValue();
			$.get("{:U('Admin/Adminmanage/public_verifypass')}", { password: ""+passwords+"" } ,function(data){
				if(data.state == "fail"){
					$( '#J_reg_tip_password' ).html(' <span for="dbname" generated="true" class="tips_error" style="">旧密码不正确！</span>');
				}else{
					$( '#J_reg_tip_password' ).html('');
				}
			},"json");
		});
        $("button.chanpass_ajax_submit_btn").on("click", function (e) {
            //删除它的默认事件，提交
            e.preventDefault();
            var btn = $(this),
                form = btn.parents('form.J_ajaxForm');
            //Ajax提交
            form.ajaxSubmit({
                //按钮上是否自定义提交地址(多按钮情况)
                url: btn.data('action') ? btn.data('action') : form.attr('action'),
                dataType: "json",
                eforeSubmit: function (arr, $form, options) {
                    var text = btn.text();
                    //按钮文案、状态修改
                    btn.text(text + '中...').prop('disabled', true).addClass('disabled');
                },
                success: function (data, statusText, xhr, $form) {
                    var text = btn.text();
                    //按钮文案、状态修改
                    btn.removeClass('disabled').text(text.replace('中...', '')).parent().find('span').remove();
					if( data.state === 'success' ) {
						$( '<span class="tips_success">' + data.info + '</span>' ).appendTo(btn.parent()).fadeIn('slow').delay( 1000 ).fadeOut(function(){
							if(data.referer){
								window.location.href = data.referer;
							}else{
								reloadPage(window);
							}
						});
					}else if( data.state === 'fail' ) {
						$( '<span class="tips_error">' + data.info + '</span>' ).appendTo(btn.parent()).fadeIn( 'fast' );
						btn.removeProp('disabled').removeClass('disabled');
					}
                }
            });
        });
    });
});
</script>
</body>
</html>