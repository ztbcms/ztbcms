<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
    <Admintemplate file="Common/Nav"/>
    <div class="h_a">评论配置</div>
    <form method='post'   id="myform" class="J_ajaxForm"  action="{:U("Comments/Comments/config")}">
    <div class="table_full">
      <table width="100%"  class="table_form">
        <tr>
          <th width="180">是否开启评论：</th>
          <td class="y-bg"><input type="checkbox" name="status" value="1" <if condition="$data['status'] eq '1' ">checked</if> />开启评论</td>
        </tr>
        <tr>
          <th width="180">是否允许游客评论：</th>
          <td class="y-bg"><input type="checkbox" name="guest" value="1" <if condition="$data['guest'] eq '1' ">checked</if> />允许游客评论</td>
        </tr>
        <tr>
          <th width="180">是否需要审核：</th>
          <td class="y-bg"><input type="checkbox" class="" name="check" value="1"  <if condition="$data['check'] eq '1' ">checked</if>/>发表评论需要审核</td>
        </tr>
        <tr>
          <th width="180">是否开启验证码：</th>
          <td class="y-bg"><input type="checkbox" name="code" value="1"  <if condition="$data['code'] eq '1' ">checked</if>/>开启验证码</td>
        </tr>
        <tr>
          <th width="180">评论发表间隔时间：</th>
          <td class="y-bg"><input type="text" class="input" name="expire" value="{$data.expire}" /> 单位秒</td>
        </tr>
         <tr>
          <th width="180">评论内容允许最大长度：</th>
          <td class="y-bg"><input type="text" class="input" name="strlength" value="{$data.strlength}" /> 提示：1个汉字2个字节</td>
        </tr>
        <tr>
          <th width="180">前台评论排序：</th>
          <td class="y-bg"><input type="text" class="input" name="order" value="{$data.order}" /> 例如：id ASC</td>
        </tr>
        <tr>
          <th width="180">存储默认分表设置：</th>
          <td class="y-bg"><select name="stb">
          <?php
          for($i=1;$i<=(int)$data['stbsum'];$i++):
          ?>
            <option value="<?php echo $i?>" <?php if($data['stb']==$i): ?>selected<?php endif; ?>>comments_data_<?php echo $i?></option>
          <?php
          endfor;
          ?>
          </select> 
          <?php
		  if(\Libs\System\RBAC::authenticate('Comments/Comments/addfenbiao')){
		  ?>
          <a href="javascript:confirmurl('{:U("Comments/Comments/addfenbiao")}','确认要创建一张新的分表吗？')" >创建一张新的分表</a>
          <?php
		  }
		  ?>
          </td>
        </tr>
      </table>
    </div>
    <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
    </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
