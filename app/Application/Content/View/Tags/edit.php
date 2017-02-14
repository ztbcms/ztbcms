 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
    <Admintemplate file="Common/Nav"/>
    <div class="h_a">TAG编辑</div>
    <form action="{:U('edit')}" method="post" class="J_ajaxForm">
        <div class="table_full">
            <table width="100%" class="table_form">
                <tr>
                    <th width="120">标签：</th>
                    <td class="y-bg"><input type="text" name="tag" value="{$data.tag}" class="input"/>
                        <input type="hidden" name="_tag" value="{$tag}"/></td>
                </tr>
                <tr>
                    <th width="120">SEO标题：</th>
                    <td class="y-bg"><input type="text" name="seo_title" value="{$data.seo_title}" class="input"/></td>
                </tr>
                <tr>
                    <th width="120">SEO关键字：</th>
                    <td class="y-bg"><input type="text" name="seo_keyword" value="{$data.seo_keyword}" class="input"/>
                    </td>
                </tr>
                <tr>
                    <th width="120">SEO简介：</th>
                    <td class="y-bg">
                        <textarea name="seo_description" style="width:380px; height:150px;">{$data.seo_description}</textarea>
                    </td>
                </tr>
                <tr>
                    <th width="120">附加样式</th>
                    <td class="y-bg"><input type="text" name="style" value="{$data.style}" class="input"/>
                        <span>用于前台调用</span></td>
                </tr>
                <tr>
                    <th>点击量</th>
                    <td class="y-bg"><input type="text" name="hits" value="{$data.hits}" class="input"/></td>
                </tr>
            </table>
        </div>
        <div class="">
            <div class="btn_wrap_pd">
                <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
                <input type="hidden" name="tagid" value="{$data.tagid}"/>
                <input type="hidden" name="oldtagsname" value="{$data.tag}"/>
            </div>
        </div>
    </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
