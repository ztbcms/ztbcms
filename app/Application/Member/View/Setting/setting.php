<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<style>
.pop_nav{
	padding: 0px;
}
.pop_nav ul{
	border-bottom:1px solid #266AAE;
	padding:0 5px;
	height:25px;
	clear:both;
}
.pop_nav ul li.current a{
	border:1px solid #266AAE;
	border-bottom:0 none;
	color:#333;
	font-weight:700;
	background:#F3F3F3;
	position:relative;
	border-radius:2px;
	margin-bottom:-1px;
}

</style>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="pop_nav">
    <ul class="J_tabs_nav">
      <li class="current"><a href="javascript:;;">基本属性</a></li>
      <li class=""><a href="javascript:;;">UCenter</a></li>
      <li class=""><a href="javascript:;;">Connect</a></li>
    </ul>
  </div>
  <form name="myform" action="{:U('Setting/setting')}" method="post" class="J_ajaxForm">
  <div class="J_tabs_contents">
    <div class="table_full">
      <div class="h_a">基本属性</div>
      <table width="100%" class="table_form">
        <tr>
          <th width="200">通行证设置</th>
          <td> <?php echo Form::select($Interface,$setting['interface'],'name="setting[interface]"'); ?> <span>通行证可以放到<?php echo PROJECT_PATH . 'Libs/Driver/Passport/'?></span></td>
        </tr>
		<tr>
          <th width="200">允许新会员注册</th>
          <td> 是<input type="radio" name="setting[allowregister]"  class="input-radio" 
            <if condition=" $setting['allowregister'] eq '1' ">checked</if>
            value='1'>
            否<input type="radio" name="setting[allowregister]"  class="input-radio" 
            <if condition=" $setting['allowregister'] eq '0' ">checked</if>
            value='0'> </td>
        </tr>
        <tr>
          <th width="200">默认注册模型</th>
          <td><?php echo Form::select($groupsModel, $setting['defaultmodelid'], 'name="setting[defaultmodelid]"'); ?></td>
        </tr>
        <tr>
          <th width="200">新会员注册需要邮件验证</th>
          <td> 是<input type="radio" name="setting[enablemailcheck]"  class="input-radio" 
            <if condition=" $setting['enablemailcheck'] eq '1' ">checked</if>
            value='1' >
            否<input type="radio" name="setting[enablemailcheck]"  class="input-radio" 
            <if condition=" $setting['enablemailcheck'] eq '0' ">checked</if>
            value='0'> <font color=red>需填写邮箱配置，开启后会员注册审核功能无效
            </red></td>
        </tr>
        <tr>
          <th width="200">新会员注册需要管理员审核</th>
          <td> 是<input type="radio" name="setting[registerverify]"  class="input-radio" 
            <if condition=" $setting['registerverify'] eq '1' ">checked</if>
            value='1'>
            否<input type="radio" name="setting[registerverify]"  class="input-radio" 
            <if condition=" $setting['registerverify'] eq '0' ">checked</if>
            value='0'> </td>
        </tr>
        <tr>
          <th width="200">是否启用应用间积分兑换</th>
          <td> 是<input type="radio" name="setting[showapppoint]"  class="input-radio" 
            <if condition=" $setting['showapppoint'] eq '1' ">checked</if>
            value='1'>
            否<input type="radio" name="setting[showapppoint]"  class="input-radio" 
            <if condition=" $setting['showapppoint'] eq '0' ">checked</if>
            value='0'> </td>
        </tr>
        <tr>
          <th width="200">1元人民币购买积分数量</th>
          <td><input type="text" name="setting[rmb_point_rate]" id="rmb_point_rate" class="input" size="4" value="{$setting.rmb_point_rate}"></td>
        </tr>
        <tr>
          <th width="200">新会员默认点数</th>
          <td><input type="text" name="setting[defualtpoint]" id="defualtpoint" class="input" size="4" value="{$setting.defualtpoint}"></td>
        </tr>
        <tr>
          <th width="200">新会员注册默认赠送资金</th>
          <td><input type="text" name="setting[defualtamount]" id="defualtamount" class="input" size="4" value="{$setting.defualtamount}"></td>
        </tr>
        <tr>
          <th width="200">是否显示注册协议</th>
          <td> 是<input type="radio" name="setting[showregprotocol]"  class="input-radio" 
            <if condition=" $setting['showregprotocol'] eq '1' ">checked</if>
            value='1'>
            否<input type="radio" name="setting[showregprotocol]"  class="input-radio" 
            <if condition=" $setting['showregprotocol'] eq '0' ">checked</if>
            value='0'> </td>
        </tr>
        <tr>
          <th width="200">是否开启登录验证码</th>
          <td> 是<input type="radio" name="setting[openverification]"  class="input-radio" 
            <if condition=" $setting['openverification'] eq '1' ">checked</if>
            value='1'>
            否<input type="radio" name="setting[openverification]"  class="input-radio" 
            <if condition=" $setting['openverification'] eq '0' ">checked</if>
            value='0'> </td>
        </tr>
        <tr>
          <th width="200">会员注册协议</th>
          <td><textarea name="setting[regprotocol]" id="regprotocol" style="width:80%;height:120px;">{$setting.regprotocol}</textarea></td>
        </tr>
        <tr>
          <th width="200">邮件认证内容</th>
          <td><textarea name="setting[registerverifymessage]" id="registerverifymessage" style="width:80%;height:120px;">{$setting.registerverifymessage}</textarea></td>
        </tr>
        <tr>
          <th width="200">密码找回邮件内容</th>
          <td><textarea name="setting[forgetpassword]" id="forgetpassword" style="width:80%;height:120px;">{$setting.forgetpassword}</textarea></td>
        </tr>
      </table>
    </div>
    <div class="table_full" style="display:none">
      <div class="h_a">如果开启UCenter接口，以下所有项均为必填项。</div>
      <table width="100%" cellspacing="0" class="table_form">
        <tbody>
          <tr>
            <th width="140">Ucenter链接方式：</th>
            <td><input type="radio" name="setting[uc_connect]" value="mysql" 
              <if condition=" $setting['uc_connect'] eq 'mysql' ">checked</if>
              /> MySQL <input type="radio" name="setting[uc_connect]" value="" 
              <if condition=" $setting['uc_connect'] eq '' ">checked</if>
              /> 远程方式</td>
          </tr>
          <tr>
            <th width="140">Ucenter api 地址：</th>
            <td><input type="text" class="input" name="setting[uc_api]" id="uc_api" value="{$setting.uc_api}" />
              如 http://www.domain.com/ucenter 最后不要带斜线</td>
          </tr>
          <tr>
            <th width="140">Ucenter api IP：</th>
            <td><input type="text" class="input" name="setting[uc_ip]" id="uc_ip" value="{$setting.uc_ip}" />
              一般不用填写,遇到无法同步时,请填写ucenter主机的IP地址</td>
          </tr>
          <tr>
            <th>Ucenter 数据库主机名：</th>
            <td><input type="text" class="input" name="setting[uc_dbhost]" id="uc_dbhost" value="{$setting.uc_dbhost}" /></td>
          </tr>
          <tr>
            <th>Ucenter 数据库用户名：</th>
            <td><input type="text" class="input" name="setting[uc_dbuser]" id="uc_dbuser" value="{$setting.uc_dbuser}" /></td>
          </tr>
          <tr>
            <th>Ucenter 数据库密码：</th>
            <td><input type="password" class="input" name="setting[uc_dbpw]" id="uc_dbpw" value="{$setting.uc_dbpw}" /></td>
          </tr>
          <tr>
            <th>Ucenter 数据库名：</th>
            <td><input type="text" class="input" name="setting[uc_dbname]" id="uc_dbname" value="{$setting.uc_dbname}" /></td>
          </tr>
          <tr>
            <th>Ucenter 数据库表前缀：</th>
            <td><input type="text" class="input" name="setting[uc_dbtablepre]" id="uc_dbtablepre" value="{$setting.uc_dbtablepre}" />
              格式：数据库.前缀
              <input type="button" value="测试数据连连接" class="button"  onclick="mysql_test()" /></td>
          </tr>
            </tr>
          
          <tr>
            <th>Ucenter 数据库字符集：</th>
            <td><select name="setting[uc_dbcharset]"  id="uc_dbcharset"  />
              
              <option value="">请选择</option>
              <option value="gbk"  
              <if condition=" $setting['uc_dbcharset'] eq 'gbk' ">selected</if>
              >GBK
              </option>
              <option value="utf8"  
              <if condition=" $setting['uc_dbcharset'] eq 'utf8' ">selected</if>
              >UTF-8
              </option>
              </select></td>
          </tr>
            </tr>
          
          <tr>
            <th>应用id(APP ID)：</th>
            <td><input type="text" class="input" name="setting[uc_appid]" id="uc_appid" value="{$setting.uc_appid}" /></td>
          </tr>
            </tr>
          
          <tr>
            <th>Ucenter 通信密钥：</th>
            <td><input type="text" class="input" name="setting[uc_key]" id="uc_key" value="{$setting.uc_key}" /></td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="table_full" style="display:none">
      <div class="h_a">Connect</div>
      <table width="100%" cellspacing="0" class="table_form">
        <tbody>
          <tr>
            <th width="100">新浪微博</th>
            <td class="y-bg"><span> App Key
              <input type="text" class="input" name="setting[sinawb_akey]" id="sina_akey" size="20" value="{$setting.sinawb_akey}"/>
              App Secret
              <input type="text" class="input" name="setting[sinawb_skey]" id="sina_skey" size="40" value="{$setting.sinawb_skey}"/>
              <a href="http://open.weibo.com/connect" target="_blank">点击注册</a></span></td>
          </tr>
          <tr>
            <th>QQ空间登录</th>
            <td class="y-bg"><span> APP ID
              <input type="text" class="input" name="setting[qq_akey]" id="qq_akey" size="20" value="{$setting.qq_akey}"/>
              APP KEY
              <input type="text" class="input" name="setting[qq_skey]" id="qq_skey" size="40" value="{$setting.qq_skey}"/>
              <a href="http://connect.qq.com/intro/login/" target="_blank">点击注册</a></span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script language="JavaScript">
//mysql链接测试
function mysql_test() {
    $.get(GV.DIMAUB+'index.php?g=Member&m=Setting&a=myqsl_test', {
        host: $('#uc_dbhost').val(),
        username: $('#uc_dbuser').val(),
        password: $('#uc_dbpw').val()
    },

    function (data) {
        if (data == 1) {
            alert('连接成功！');
        } else {
            alert('连接失败！');
        }
    });
}
</script>
</body>
</html>
