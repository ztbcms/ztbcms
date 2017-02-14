 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">添加自定义页面</div>
  <form name="myform" id="myform" action="{:U("Template/Custompage/add")}" method="post">
    <div class="table_full">
      <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
        <tbody>
          <tr>
            <th width="120">名称</th>
            <td><input type="text" class="input" name="name" value="" >
              例如：最新评论</td>
          </tr>
          <tr>
            <th width="120">文件名</th>
            <td><input type="text" class="input" name="tempname" value="" >
              例如：d.html</td>
          </tr>
          <tr>
            <th width="120">存放路径</th>
            <td><input type="text" class="input" name="temppath" value="/"  style="width:300px;">
              从网站根目录开始，以"/"结尾</td>
          </tr>
          <tr>
            <th>页面内容</th>
            <td><textarea id="temptext" name="temptext" style="width:95%; height:400px" validate="required:true, minlength:4" class="valid"></textarea></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">创建</button>
      </div>
    </div>
  </form>
</div>
<script language="Javascript" type="text/javascript" src="{$config_siteurl}statics/js/edit_area/edit_area_full.js"></script>
<script language="Javascript" type="text/javascript">
		// initialisation
		editAreaLoader.init({
			id: "temptext"	// id of the textarea to transform		
			,start_highlight: true	// if start with highlight
			,allow_resize: "both"
			,allow_toggle: false
			,word_wrap: true
			,language: "zh"
			,syntax: "html"	
		});
	</script>
</body>
</html>
