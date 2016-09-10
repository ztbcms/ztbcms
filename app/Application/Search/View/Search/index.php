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
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <p>注意：全文检索模块需要mysql开启全文索引功能，开启方法：修改mysql配置文件：window服务器为my.ini，linux服务器为my.cnf，在 [mysqld] 后面加入一行“ft_min_word_len=1”，然后重启Mysql。</p>
  </div>
  <div class="pop_nav">
    <ul class="J_tabs_nav">
      <li class="current"><a href="javascript:;;">基本设置</a></li>
      <li class=""><a href="javascript:;;">Sphinx全文索引配置</a></li>
    </ul>
  </div>
  <form name="myform" action="{:U('Search/index')}" method="post" class="J_ajaxForm">
  <div class="J_tabs_contents">
    <div class="table_full">
      <div class="h_a">基本属性</div>
      <table width="100%" class="table_form">
        <tr>
          <th width="200">数据源设置</th>
          <td>
          <ul class="three_list cc J_ul_check">
          <volist name="model_list" id="vo">
             <li><label><input  name="setting[modelid][]" class="J_check" type="checkbox" <if condition=" in_array($vo['modelid'],$config['modelid']) ">checked</if> value="{$vo.modelid}" ><span>{$vo.name}</span></label></li>
          </volist>
          </ul></td>
        </tr>
        <tr>
          <th width="200">是否启用相关搜索</th>
          <td> 是<input type="radio" name="setting[relationenble]"  class="input-radio" <if condition=" $config['relationenble'] eq 1 ">checked</if> value='1'>
            否<input type="radio" name="setting[relationenble]"  class="input-radio" <if condition=" $config['relationenble'] eq 0 ">checked</if> value='0'> <span class="gray">（提示：此项功能会增大数据库压力！）</span></td>
        </tr>
        <tr>
          <th width="200">是否启用PHP简易分词</th>
          <td> 是<input type="radio" name="setting[segment]"  class="input-radio" <if condition=" $config['segment'] eq 1 ">checked</if> value='1'>
            否<input type="radio" name="setting[segment]"  class="input-radio" <if condition=" $config['segment'] eq 0 ">checked</if> value='0'> <span class="gray">（提示：只有在关闭Sphinx全文索引有效！<b>关闭</b>此项后将无法使用MySQL全文索引！）</span></td>
        </tr>
        <tr>
          <th width="200">分词接口</th>
          <td> <select name="setting[dzsegment]">
          <option value='0' <if condition=" !$config['dzsegment'] ">selected</if>>使用内置简单分词接口</option>
          <option value="1" <if condition=" $config['dzsegment'] ">selected</if>>使用Discuz在线分词接口</option>
          </select>
          <span class="gray">（提示：Discuz在线分词接口速度可能受网络影响！）</span>
          </td>
        </tr>
        <tr>
          <th width="200">搜索结果每页显示条数</th>
          <td><input type="text" class="input" name="setting[pagesize]" id="uc_api" value="{$config.pagesize}" /></td>
        </tr>
        <tr>
          <th width="200">搜索结果缓存时间</th>
          <td><input type="text" class="input" name="setting[cachetime]" id="uc_api" value="{$config.cachetime}" /> 秒</td>
        </tr>
      </table>
    </div>
    <div class="table_full" style="display:none">
      <div class="h_a">如果开启sphinx全文索引，以下所有项均为必填项。</div>
      <table width="100%" cellspacing="0" class="table_form">
        <tbody>
          <tr>
            <th width="150">是否启用sphinx全文索引：</th>
            <td><input type="radio" name="setting[sphinxenable]" value="1" <if condition=" $config['sphinxenable'] eq '1' ">checked</if> /> 是 
            <input type="radio" name="setting[sphinxenable]" value="0" <if condition=" $config['sphinxenable'] eq '0' ">checked</if> /> 否</td>
          </tr>
          <tr>
            <th>服务器主机地址：</th>
            <td><input type="text" class="input" name="setting[sphinxhost]" id="uc_api" value="{$config.sphinxhost}" /></td>
          </tr>
          <tr>
            <th>服务器端口号：</th>
            <td><input type="text" class="input" name="setting[sphinxport]" id="uc_api" value="{$config.sphinxport}" /></td>
          </tr>
        </tbody>
      </table>
    </div>
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
