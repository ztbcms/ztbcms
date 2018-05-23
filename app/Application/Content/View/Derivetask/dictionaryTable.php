<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
    <Admintemplate file="Common/Nav"/>
    <div class="h_a">数据字典 <a style="margin-left: 20px;" href="{:U('Models/index')}" class="btn btn-success">返回列表</a></div>

    <form action="{:U('dictionaryTable')}" method="post" enctype="multipart/form-data">
        表名:ztb_<input type="text" name="table" value="{$table}">
        <input type="submit" value="查询">
    </form

    <div class="table_full">
        <table width="100%" style="margin-top: 20px;"  class="table_form">
            <!--  栏目模板选择 -->
            <tr>
                <style type="text/css">
                    .y-bg p{
                        margin: 0 0 0 0px;
                    }
                </style>
                <th  class="y-bg">
                        <div style="width: 900px;">

                            <div style="float: left;">
                                <div  style="float: left; width: 100px;">
                                    <span>|&nbsp字段名&nbsp</span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span>|&nbsp字段备注&nbsp</span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span>|&nbsp类型&nbsp</span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span>|&nbsp字段长度&nbsp</span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span>|&nbsp小数点位数&nbsp</span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span>|&nbsp是否允许为空&nbsp</span>
                                </div>
                                </p>
                            </div>

                            <div style="float: left;">
                                <p>
                                <div  style="float: left; width: 100px;">
                                    <span>|:--- </span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span> |:---</span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span> |:---</span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span> |:---  |</span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span> |:---  |</span>
                                </div>
                                <div style="float: left; width: 100px;">
                                    <span> |:---  |</span>
                                </div>
                                </p>
                            </div>

                            <volist name="data" id="foo" >
                                <p>
                                <div style="float: left; margin-right: 50px;">
                                    <div  style="float: left; width: 100px;">
                                        <span>|&nbsp{$foo.column_name}</span>
                                    </div>
                                    <div style="float: left; width: 100px;">
                                        <span>|&nbsp{$foo.column_comment}</span>
                                    </div>
                                    <div style="float: left; width: 100px;">
                                        <span>|&nbsp{$foo.data_type}</span>
                                    </div>
                                    <div style="float: left; width: 100px;">
                                        <span>|&nbsp{$foo.character_maximum_length}&nbsp</span>
                                    </div>
                                    <div style="float: left; width: 100px;">
                                        <span>|&nbsp{$foo.numeric_scale}&nbsp</span>
                                    </div>
                                    <div style="float: left; width: 100px;">
                                        <span>|&nbsp{$foo.is_nullable}&nbsp|</span>
                                    </div>
                                </div>
                                </p>
                            </volist>
    </div>

    </th>
    </tr>

    </table>
</div>

</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
