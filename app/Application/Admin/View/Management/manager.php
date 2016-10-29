<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
   <Admintemplate file="Common/Nav"/>
   <div class="table_list">
   <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td width="10%">序号</td>
            <td width="10%" align="left" >用户名</td>
            <td width="10%" align="left" >所属角色</td>
            <td width="10%"  align="left" >最后登录IP</td>
            <td width="10%"  align="left" >最后登录时间</td>
            <td width="15%"  align="left" >E-mail</td>
            <td width="10%"  align="left" >状态</td>
            <td width="10%">备注</td>
            <td width="15%" align="center">管理操作</td>
          </tr>
        </thead>
        <tbody>
        <foreach name="Userlist" item="vo">
          <tr>
            <td width="10%">{$vo.id}</td>
            <td width="10%" >{$vo.username}</td>
            <td width="10%" ><?php echo D('Admin/Role')->getRoleIdName($vo['role_id'])?></td>
            <td width="10%" >{$vo.last_login_ip}</td>
            <td width="10%"  >
            <if condition="$vo['last_login_time'] eq 0">
            该用户还没登录过
            <else />
            {$vo.last_login_time|date="Y-m-d H:i:s",###}
            </if>
            </td>
            <td width="15%">{$vo.email}</td>
            <td width="10%">
                  <if condition="$vo.status eq 1">
                      <span style="color: green;">开启</span>
                  </if>
                  <if condition="$vo.status eq 0">
                      <span style="color: red">禁用</span>
                  </if>
            </td>
            <td width="10%">{$vo.remark}</td>
            <td width="15%"  align="center">
                <if condition="$User['username'] eq $vo['username']">
                    <font color="#cccccc">修改</font> |
                    <font color="#cccccc">删除</font>
                <else />
                    <a href="{:U("Management/edit",array("id"=>$vo[id]))}">修改</a> |
                    <a class="J_ajax_del" href="{:U('Management/delete',array('id'=>$vo['id']))}">删除</a>
                </if>
            </td>
          </tr>
         </foreach>
        </tbody>
      </table>
      <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
   </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
