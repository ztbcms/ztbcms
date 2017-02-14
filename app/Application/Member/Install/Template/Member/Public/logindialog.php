 
<form>
  <div class="user_dialog_login">
    <input type="hidden" id="refer" value="{$forward}"/>
    <div class="left">
      <div class="left_line">
        <div class="message"> <span id="errMessage"></span> </div>
      </div>
      <div class="left_line">
        <div class="dl_loginName">
          <input type="text" name="loginName" id="loginName" class="input_normal"  maxlength="70">
        </div>
      </div>
      <div class="left_line">
        <div class="dl_password">
          <input type="password" name="password" id="password" class="input_normal" maxlength="30">
        </div>
      </div>
      <if condition=" $Member_config['openverification'] ">
      <div class="left_line">
        <div id="loginName" class="input92cc">
          <input id="vCode" class="input_normal" type="text" style="width:81px;" name="rvCode" maxlength="4" value="请输入验证码">
        </div>
        <div class="vcode">
          <div class="noleft"> <img id="authCode" align="absmiddle" title="看不清？点击更换" src="{:U("Api/Checkcode/index","type=userlogin&code_len=4&font_size=14&width=80&height=24&font_color=&background=")}"> </div>
          <div class="reloadCode"> <a id="changeAuthCode" href="javascript:;">看不清？换一张</a> </div>
        </div>
      </div>
      </if>
      <div class="left_line">
        <div class="right"> <a href="{:U('Member/Index/lostpassword')}">忘记密码?</a> </div>
        <label>
          <input id="setCookieTime" class="check_box" type="checkbox" name="setCookieTime">
          下次自动登录 </label>
      </div>
      <div class="style">
        <input class="home_btn" type="submit" id="submit2" value="登 陆">
      </div>
    </div>
    <div class="right_line">
      <div class="title">还未开通？</div>
      <div class="reg">
        <p>赶快免费注册一个吧！</p>
        <a class="home_btn" href="{:U('Index/register')}"></a> </div>
      <div class="partner">
        <p class="tit">或使用合作网站账号登录</p>
        <if condition=" $Member_config['qq_akey'] && $Member_config['qq_skey'] ">
        <div class="qq"> <img src="{$model_extresdir}images/icon/qqconnect.gif"> <a href="{:U('Api/Connectqq/index')}">用QQ账号登录</a> </div>
        </if>
        <if condition=" $Member_config['sinawb_akey'] && $Member_config['sinawb_skey'] ">
        <div class="sina"> <img src="{$model_extresdir}images/icon/sinaconnect.png"> <a href="{:U('Api/Connectsina/index')}">用新浪微博登录</a> </div>
        </if>
      </div>
      <p></p>
    </div>
  </div>
</form>
<script>user.loginInit(1);</script>
