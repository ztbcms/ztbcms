 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">搜索</div>
  <form name="searchform" action="{$config_siteurl}index.php" method="get" >
    <input type="hidden" value="Member" name="g">
    <input type="hidden" value="Member" name="m">
    <input type="hidden" value="index" name="a">
    <input type="hidden" value="1" name="search">
    <input type="hidden" value="879" name="menuid">
    <div class="search_type cc mb10">
      <div class="mb10"> <span class="mr20"> 注册时间：
        <input type="text" name="start_time" class="input length_2 J_date" value="{$Think.get.start_time}" style="width:80px;">
        -
        <input type="text" class="input length_2 J_date" name="end_time" value="{$Think.get.end_time}" style="width:80px;">
        <select name="status">
          <option value='0' >状态</option>
          <option value='1' >锁定</option>
          <option value='2' >正常</option>
        </select>
        <?php echo Form::select($groupCache, (int)$_GET['groupid'], 'name="groupid"', '会员组') ?> <?php echo Form::select($groupsModel, (int)$_GET['modelid'], 'name="modelid"', '会员模型'); ?>
        <select name="type">
          <option value='1' >用户名</option>
          <option value='2' >用户ID</option>
          <option value='3' >邮箱</option>
          <option value='4' >注册ip</option>
          <option value='5' >昵称</option>
        </select>
        <input name="keyword" type="text" value="{$Think.get.keyword}" class="input" />
        <button class="btn">搜索</button>
        </span> </div>
    </div>
  </form>
  <form name="myform" action="{:U('Member/userverify')}" method="post" class="J_ajaxForm">
    <div class="table_list">
      <table width="100%" cellspacing="0">
        <thead>
          <tr>
            <td  align="left" width="20"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></td>
            <td align="left"></td>
            <td align="left">用户ID</td>
            <td align="left">用户名</td>
            <td align="left">昵称</td>
            <td align="left">邮箱</td>
            <td align="left">模型名称</td>
            <td align="left">注册ip</td>
            <td align="left">最后登录</td>
            <td align="left">金钱总数</td>
            <td align="left">积分点数</td>
            <td align="left">操作</td>
          </tr>
        </thead>
        <tbody>
          <volist name="data" id="vo">
            <tr>
              <td align="left"><input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x"  value="{$vo.userid}" name="userid[]"></td>
              <td align="left"><if condition=" $vo['islock'] eq '1' "><img title="锁定" src="{$config_siteurl}statics/images/icon/icon_padlock.gif"></if>
                <if condition=" $vo['checked'] eq '0' "><img title="待审核" src="{$config_siteurl}statics/images/icon/info.png"></if></td>
              <td align="left">{$vo.userid}</td>
              <td align="left"><img src="{:getavatar($vo['userid'])}" height=18 width=18 onerror="this.src='{$config_siteurl}statics/images/member/nophoto.gif'">{$vo.username}<a href="javascript:member_infomation({$vo.userid}, '{$vo.modelid}', '')"><img src="{$config_siteurl}statics/images/icon/detail.png"></a></td>
              <td align="left">{$vo.nickname}</td>
              <td align="left">{$vo.email}</td>
              <td align="left">{$groupsModel[$vo['modelid']]}</td>
              <td align="left">{$vo.regip}</td>
              <td align="left"><if condition=" $vo['lastdate'] eq 0 ">还没有登录过
                  <else />
                  {$vo.lastdate|date='Y-m-d H:i:s',###}</if></td>
              <td align="left">{$vo.amount}</td>
              <td align="left">{$vo.point}</td>
              <td align="left"><a href="{:U('Member/edit', array('userid'=>$vo['userid']) )}">[修改]</a></td>
            </tr>
          </volist>
        </tbody>
      </table>
      <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button class="btn  mr10 btn_submit J_ajax_submit_btn" type="submit">审核通过</button>
        <button class="btn  mr10 J_ajax_submit_btn" data-action="{:U('Member/Member/delete')}" type="submit">删除</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
