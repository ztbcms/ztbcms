<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
    <div class="wrap J_check_wrap">
        <div class="nav">
            <ul class="cc">
                <li><a href="{:U('Style/index')}">模板管理</a></li>
                <?php
				if(\Libs\System\RBAC::authenticate('add')){
				?>
                <li><a href="{:U("Template/Style/add",array("dir"=>urlencode(str_replace('/','-',$dir))    ))}">在此目录下添加模板</a></li>
                <?php
				}
				?>
            </ul>
        </div>
        <div class="h_a">模板编辑</div>
        <form name="myform" id="myform" action="{:U("Template/Style/edit")}" method="post">
              <input type="hidden" name="dir" value="{$dir}"/>
            <input type="hidden" name="file" value="{$file}"/>
            <div class="table_full">
                <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
                    <tbody>
                        <tr>
                            <th width="120">文件名称</th>
                            <td><input type="text" class="input" validate="required:true, minlength:2, maxlength:30"  value="{$file}" readonly></td>
                        </tr>
                        <tr>
                            <th>内容</th>
                            <td><textarea id="content" name="content" style="width:95%; height:400px" validate="required:true, minlength:4" class="valid"><literal><?php echo $content;?></literal></textarea></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="btn_wrap">
                <div class="btn_wrap_pd">
                    <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
                </div>
            </div>
        </form>
    </div>
    <script language="Javascript" type="text/javascript" src="{$config_siteurl}statics/js/edit_area/edit_area_full.js"></script>
    <script language="Javascript" type="text/javascript">
        // initialisation
        editAreaLoader.init({
            id: "content"	// id of the textarea to transform		
            , start_highlight: true	// if start with highlight
            , allow_resize: "both"
            , allow_toggle: false
            , word_wrap: true
            , language: "zh"
            , syntax: "html"
        });
    </script>
</body>
</html>
