 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">搜索</div>
  <form name="searchform" action="" method="get" >
    <input type="hidden" value="Member" name="g">
    <input type="hidden" value="Member" name="m">
    <input type="hidden" value="index" name="a">
    <input type="hidden" value="1" name="search">
    <div class="search_type cc mb10">
      <div class="mb10">

        <div class="mr20">

          <section style="display: inline;">
            注册时间：
            <input type="text" name="start_time" class="input length_2 J_date" value="{$Think.get.start_time}" style="width:80px;">
            -
            <input type="text" class="input length_2 J_date" name="end_time" value="{$Think.get.end_time}" style="width:80px;">

          </section>

          <section style="display: inline;">
            状态:
            <input class="input length_2" type="hidden" name="_filter[0]" value="islock">
            <input class="input length_2" type="hidden" name="_operator[0]" value="EQ">

            <select name="_value[0]" class="select_2">
              <option value='' <if condition=" $_value[0] == '' "> selected</if>>全部</option>
              <option value='1' <if condition=" $_value[0] == '1' "> selected</if>>锁定</option>
              <option value='2' <if condition=" $_value[0] == '2' "> selected</if>>正常</option>
            </select>
          </section>

          <section style="display: inline;">
              审核:
              <input class="input length_2" type="hidden" name="_filter[2]" value="checked">
              <input class="input length_2" type="hidden" name="_operater[2]" value="EQ">

              <select name="_value[2]" class="select_2">
                  <option value='' <if condition=" $_value[2] == '' "> selected</if>>全部</option>
                  <option value='1' <if condition=" $_value[2] == '1' "> selected</if>>审核通过</option>
                  <option value='0' <if condition=" $_value[2] == '0' "> selected</if>>待审核</option>
              </select>
          </section>


          <section style="display: inline;">
            <!-- 搜索字段 -->
            <select name="_filter[1]" class="select_2">
              <option value="username" <if condition=" $_filter[1] == 'username' "> selected</if>>用户名</option>
              <option value="userid" <if condition=" $_filter[1] == 'userid' "> selected</if>>用户ID</option>
              <option value="nickname" <if condition=" $_filter[1] == 'nickname' "> selected</if>>昵称</option>
            </select>
            <!-- 操作符 -->
            <select name="_operator[1]" class="select_2">
              <option value="EQ" <if condition=" $_operator[1] == 'EQ' "> selected</if>>等于</option>
              <option value="NEQ" <if condition=" $_operator[1] == 'NEQ' "> selected</if>>不等于</option>
              <option value="GT" <if condition=" $_operator[1] == 'GT' "> selected</if>>大于</option>
              <option value="EGT" <if condition=" $_operator[1] == 'EGT' "> selected</if>>大于等于</option>
              <option value="LT" <if condition=" $_operator[1] == 'LT' "> selected</if>>小于</option>
              <option value="ELT" <if condition=" $_operator[1] == 'ELT' "> selected</if>>小于等于</option>
              <option value="LIKE" <if condition=" $_operator[1] == 'LIKE' "> selected</if>>模糊查询</option>
            </select>
            <!-- 搜索值 -->
            <input class="input length_3" type="text" name="_value[1]" value="{$_value[1]}">
          </section>

          <button class="btn">搜索</button>
        </div>
      </div>
    </div>
  </form>
  <form name="myform" action="{:U('Member/delete')}" method="post" class="J_ajaxForm">
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
        <button class="btn  mr10 J_ajax_submit_btn" data-action="{:U('Member/Member/lock')}" type="submit">锁定</button>
        <button class="btn  mr10 J_ajax_submit_btn" data-action="{:U('Member/Member/unlock')}" type="submit">解锁</button>
        <button class="btn  mr10 J_ajax_submit_btn" type="submit">删除</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script src="{$config_siteurl}statics/js/content_addtop.js"></script>
<script>
//会员信息查看
function member_infomation(userid, modelid, name) {
	omnipotent("member_infomation", GV.DIMAUB+'index.php?g=Member&m=Member&a=memberinfo&userid='+userid+'', "个人信息",1)
}
</script>
</body>
</html>
