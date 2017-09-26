<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
    <form name="myform" action="{:U('Member/delete')}" method="post" class="J_ajaxForm">
        <div class="table_full">
            <div class="h_a">基本信息</div>
            <input type="hidden" name="userid" value="{$userid}">
            <table width="100%" class="table_form">
                <tr>
                    <th width="80">用户名</th>
                    <td>{$username}</td>
                </tr>
                <tr>
                    <th>头像</th>
                    <td><img src="{$userpic}" onerror="this.src='{$config_siteurl}statics/images/member/nophoto.gif'"
                             height=90 width=90></td>
                </tr>
                <tr>
                    <th>是否审核</th>
                    <td>
                        <if condition=" $checked eq '1' "> 审核通过
                            <else/>
                            待审核
                        </if>
                    </td>
                </tr>
                <tr>
                    <th>昵称</th>
                    <td>{$nickname}</td>
                </tr>
                <tr>
                    <th>邮箱</th>
                    <td>{$email}</td>
                </tr>
                <tr>
                    <th>会员组</th>
                    <td><?php echo $groupCache[$groupid]; ?></td>
                </tr>
                <tr>
                    <th>积分点数</th>
                    <td>{$point}</td>
                </tr>
                <tr>
                    <th>钱金总额</th>
                    <td>{$amount}</td>
                </tr>
                <tr>
                    <th>会员模型</th>
                    <td><?php echo $groupsModel[$modelid]; ?></td>
                </tr>
            </table>
            <div class="h_a"> 详细信息</div>
            <table width="100%" class="table_form">
                <?php foreach ($Model_field as $k => $v) { ?>
                    <tr>
                        <th width="80"><?php echo $v['name'] ?>：</th>
                        <td><?php echo $output_data[$v['field']] ?></td>
                    </tr>
                <?php } ?>
            </table>
            <div class="btn_wrap" style="text-align: right;background: #F6F6F6;padding: 10px;">
                <button class="btn  mr10 J_ajax_submit_btn"
                        data-action="{:U('Member/Member/userverify')}" type="submit">审核
                </button>
                <button class="btn  mr10 J_ajax_submit_btn"
                        data-action="{:U('Member/Member/userunverify')}" type="submit">
                    取消审核
                </button>
                <button class="btn  mr10 J_ajax_submit_btn"
                        data-action="{:U('Member/Member/lock')}" type="submit">锁定
                </button>
                <button class="btn  mr10 J_ajax_submit_btn"
                        data-action="{:U('Member/Member/unlock')}" type="submit">解锁
                </button>
                <button class="btn  mr10 J_ajax_submit_btn" type="submit">删除</button>
            </div>
        </div>
    </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script src="{$config_siteurl}statics/js/content_addtop.js"></script>
</body>
</html>
