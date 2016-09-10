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
      <li class="current"><a href="javascript:;;">网址规则</a></li>
      <li class=""><a href="javascript:;;">内容规则</a></li>
      <li class=""><a href="javascript:;;">自定义规则</a></li>
      <li class=""><a href="javascript:;;">高级配置</a></li>
    </ul>
  </div>
  <form name="myform" action="{:U('Node/add')}" method="post" class="J_ajaxForm">
    <div class="J_tabs_contents">
      <div class="table_full">
        <div class="h_a">基本信息</div>
        <table width="100%">
          <tr>
            <th width="120">采集项目名：</th>
            <td><input type="text" name="data[name]" id="name"  class="input" value="" /></td>
          </tr>
          <tr>
            <th width="120">采集页面编码：</th>
            <td><label><input type="radio" name="data[sourcecharset]" id="_gbk" checked value="gbk">
              GBK</label>
              <label><input type="radio" name="data[sourcecharset]" id="_utf-8"  value="utf-8">
              UTF-8</label>
              <label><input type="radio" name="data[sourcecharset]" id="_big5"  value="big5">
              BIG5 </label></td>
          </tr>
        </table>
        <div class="h_a">网址采集</div>
        <table width="100%" class="table_form">
          <tr>
            <th width="120">网址类型：</th>
            <td><label>
                <input type="radio" name="data[sourcetype]" onClick="show_url_type(this.value)" id="_1" checked value="1">
                序列网址</label>
              <label>
                <input type="radio" name="data[sourcetype]" onClick="show_url_type(this.value)" id="_2"  value="2">
                多个网页</label>
              <label>
                <input type="radio" name="data[sourcetype]" onClick="show_url_type(this.value)" id="_3"  value="3">
                单一网页</label>
              <label>
                <input type="radio" name="data[sourcetype]" onClick="show_url_type(this.value)" id="_4"  value="4">
                RSS </label></td>
          </tr>
          <tbody id="url_type_1" >
            <tr>
              <th width="120">网址配置：</th>
              <td align="left" valign="middle"><input type="text" name="urlpage1" id="urlpage_1" size="100" value=""  class="input">
                <input type="button" class="btn" onClick="show_url()" value="测试">
                <br />
                (如：http://www.ztbcms.com/list-(*).html,页码使用<a href="javascript:insertText('urlpage_1', '(*)')">(*)</a>做为通配符。<br />
                页码从:
                <input type="text" name="data[pagesize_start]" value="1" size="4"  class="input">
                到
                <input type="text" name="data[pagesize_end]" value="10" size="4"  class="input">
                每次增加
                <input type="text" name="data[par_num]" size="4" value="1" class="input"></td>
            </tr>
          </tbody>
          <tbody id="url_type_2"   style="display:none">
            <tr>
              <th width="120">网址配置：</th>
              <td><textarea rows="10" cols="80" name="urlpage2" id="urlpage_2" ></textarea>
                <br>
                每行一条 </td>
            </tr>
          </tbody>
          <tbody id="url_type_3"   style="display:none">
            <tr>
              <th width="120">网址配置：</th>
              <td><input type="text" name="urlpage3" id="urlpage_3" size="100" value="" class="input"></td>
            </tr>
          </tbody>
          <tbody id="url_type_4"   style="display:none">
            <tr>
              <th width="120">网址配置：</th>
              <td><input type="text" name="urlpage4" id="urlpage_4" size="100" value="" class="input"></td>
            </tr>
          </tbody>
          <tr>
            <th width="120">网址配置：</th>
            <td> 网址中必须包含
              <input type="text" name="data[url_contain]"  value="" class="input">
              网址中不得包含
              <input type="text" name="data[url_except]"  value="" class="input"></td>
          </tr>
          <tr>
            <th width="120">Base配置：</th>
            <td><input type="text" name="data[page_base]"  value="" size="100" class="input" >
              <br>
              如果目标网站配置了Base请设置。 </td>
          </tr>
          <tr>
            <th width="120">网址区域：</th>
            <td> 从
              <textarea rows="10" cols="40" name="data[url_start]"></textarea>
              到
              <textarea rows="10" name="data[url_end]" cols="40"></textarea>
              结束 <br/>
              <span class="gray"> （通过限定采集网址的开始和结束点，更进一步对要采集的网址缩小范围。）</span></td>
          </tr>
          <tr>
            <th width="120">网址：</th>
            <td> <textarea rows="10" cols="40" name="data[url_regular]" id="url_regular"></textarea>
            <br />
            <span class="gray"> 截取的地方加上<a href="javascript:insertText('url_regular', '[网址]')">[网址]</a>，如：&lt;a href="信息链接"&gt;标题&lt;/a&gt;
正则就是：&lt;a href="<font color="#FF0000">[网址]</font>"&gt;<font color="#FF0000">*</font>&lt;/a&gt;</span>
            </td>
          </tr>
        </table>
      </div>
      <div class="table_full" style="display:none">
        <div class="h_a">内容规则</div>
        <div class="prompt_text">
          <div class="h_a">功能说明</div>
          <ol>
            <li>匹配规则请设置开始和结束符，具体内容使用“[内容]”做为通配符 。</li>
            <li>过滤选项格式为“要过滤的内容[|]替换值”，要过滤的内容支持正则表达式，每行一条。
            	<br/>同时还支持函数模式，例如：“fun=str_replace|新浪,sina,<font color="#FF0000">###</font>”表示对采集的内容执行替换后返回（<font color="#FF0000">###</font>表示采集到的内容，多个参数用“,”隔开）。
            	<br/><font color="#FF0000">注</font>：函数可以添加到 Collection 模块目录下的 Funs 文件夹下的 funs.php 文件中。</li>
          </ol>
        </div>
        <div class="h_a">标题规则</div>
        <table width="100%" class="table_form">
          <tr>
            <th width="120">匹配规则：</th>
            <td><textarea rows="5" cols="40" name="data[title_rule]" id="title_rule"><title>[内容]</title>
</textarea>
              <br>
              使用"<a href="javascript:insertText('title_rule', '[内容]')">[内容]</a>"作为通配符 </td>
            <th width="120">过滤选项：</th>
            <td><textarea rows="5" cols="50" name="data[title_html_rule]" id="title_html_rule" ></textarea>
              <input type="button" value="选择" class="btn"  onclick="html_role('data[title_html_rule]')"></td>
          </tr>
        </table>
        <div class="h_a">作者规则</div>
        <table width="100%">
          <tr>
            <th width="120">匹配规则：</th>
            <td><textarea rows="5" cols="40" name="data[author_rule]" id="author_rule"></textarea>
              <br>
              使用"<a href="javascript:insertText('author_rule', '[内容]')">[内容]</a>"作为通配符 </td>
            <th width="120">过滤选项：</th>
            <td><textarea rows="5" cols="50" name="data[author_html_rule]" id="author_html_rule" ></textarea>
              <input type="button" value="选择" class="btn"  onclick="html_role('data[author_html_rule]')"></td>
          </tr>
        </table>
        <div class="h_a">来源规则</div>
        <table width="100%">
          <tr>
            <th width="120">匹配规则：</th>
            <td><textarea rows="5" cols="40" name="data[comeform_rule]" id="comeform_rule"></textarea>
              <br>
              使用"<a href="javascript:insertText('comeform_rule', '[内容]')">[内容]</a>"作为通配符 </td>
            <th width="120">过滤选项：</th>
            <td><textarea rows="5" cols="50" name="data[comeform_html_rule]" id="comeform_html_rule"></textarea>
              <input type="button" value="选择" class="btn"  onclick="html_role('data[comeform_html_rule]')"></td>
          </tr>
        </table>
        <div class="h_a">时间规则</div>
        <table width="100%">
          <tr>
            <th width="120">匹配规则：</th>
            <td><textarea rows="5" cols="40" name="data[time_rule]" id="time_rule"></textarea>
              <br>
              使用"<a href="javascript:insertText('time_rule', '[内容]')">[内容]</a>"作为通配符 </td>
            <th width="120">过滤选项：</th>
            <td><textarea rows="5" cols="50" name="data[time_html_rule]" id="time_html_rule" ></textarea>
              <input type="button" value="选择" class="btn"  onclick="html_role('data[time_html_rule]')"></td>
          </tr>
        </table>
        <div class="h_a">内容规则</div>
        <table width="100%">
          <tr>
            <th width="120">匹配规则：</th>
            <td><textarea rows="5" cols="40" name="data[content_rule]" id="content_rule"></textarea>
              <br>
              使用"<a href="javascript:insertText('content_rule', '[内容]')">[内容]</a>"作为通配符 </td>
            <th width="120">过滤选项：</th>
            <td><textarea rows="5" cols="50" name="data[content_html_rule]" id="content_html_rule"></textarea>
              <input type="button" value="选择" class="btn"  onclick="html_role('data[content_html_rule]')"></td>
          </tr>
        </table>
        <div class="h_a">内容分页规则</div>
        <table width="100%">
          <tr>
            <td width="120">分页模式：</td>
            <td><input type="radio" name="data[content_page_rule]" onClick="show_nextpage(this.value)" id="_1" checked value="1">
              全部列出模式
              <input type="radio" name="data[content_page_rule]" onClick="show_nextpage(this.value)" id="_2"  value="2">
              上下页模式 </td>
          </tr>
          <tbody id="nextpage" style="display:none">
            <tr>
              <td width="120">下一页规则：</td>
              <td><input type="text" name="data[content_nextpage]" size="100" value="" class="input">
                <br>
                请填写下一页超链接中间的代码。如：<a href="http://www.ztbcms.com/page_1.html">下一页</a>，他的“下一页规则”为“下一页”。 </td>
            </tr>
          </tbody>
          <tr>
            <td width="120">匹配规则：</td>
            <td> 从
              <textarea rows="5" cols="40" name="data[content_page_start]" id="content_page_start"></textarea>
              到
              <textarea rows="5" cols="40" name="data[content_page_end]" id="content_page_end"></textarea></td>
          </tr>
        </table>
      </div>
      <div class="table_full" style="display:none">
        <div class="h_a">自定义规则 <input type="button" class="btn" value="添加项目" onClick="add_caiji()"></div>
        <table width="100%" id="customize_config">
          
        </table>
      </div>
      <div class="table_full" style="display:none">
        <div class="h_a">高级配置</div>
        <table width="100%" class="table_form" >
          <tr>
            <th width="120">下载图片：</th>
            <td><input type="radio" name="data[down_attachment]" id="_1"  value="1">
              下载图片
              <input type="radio" name="data[down_attachment]" id="_0" checked value="0">
              不下载
              <br />
            <span class="gray"> 注意：下载的是“内容规则”中的远程图片！ </span> </td>
          </tr>
          <tr>
            <th width="120">图片水印：</th>
            <td><input type="radio" name="data[watermark]" id="_1"  value="1">
              打水印
              <input type="radio" name="data[watermark]" id="_0" checked value="0">
              不打水印 </td>
          </tr>
          <tr>
            <th width="120">内容分页：</th>
            <td><input type="radio" name="data[content_page]" id="_0"  value="0">
              不分页
              <input type="radio" name="data[content_page]" id="_1" checked value="1">
              按原文分页 </td>
          </tr>
          <tr>
            <th width="120">导入顺序：</th>
            <td><input type="radio" name="data[coll_order]" id="_1"  value="1">
              与目标站相同
              <input type="radio" name="data[coll_order]" id="_2" checked  value="2">
              与目标站相反 </td>
          </tr>
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
(function($){
	$.fn.extend({
		insertAtCaret: function(myValue){
			var $t=$(this)[0];
			if (document.selection) {
				this.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
			}
			else 
				if ($t.selectionStart || $t.selectionStart == '0') {
					var startPos = $t.selectionStart;
					var endPos = $t.selectionEnd;
					var scrollTop = $t.scrollTop;
					$t.value = $t.value.substring(0, startPos) + myValue + $t.value.substring(endPos, $t.value.length);
					this.focus();
					$t.selectionStart = startPos + myValue.length;
					$t.selectionEnd = startPos + myValue.length;
					$t.scrollTop = scrollTop;
				}
				else {
					this.value += myValue;
					this.focus();
				}
		}
	})	
})(jQuery);
//在光标的位置插入
function insertText(id, text){
	$('#'+id).insertAtCaret(text);
}
//网址类型切换
function show_url_type(obj) {
    var num = 4;
    for (var i = 1; i <= num; i++) {
        if (obj == i) {
            $('#url_type_' + i).show();
        } else {
            $('#url_type_' + i).hide();
        }
    }
}
//测试
function show_url() {
	var type = $("input[type='radio'][name='data[sourcetype]']:checked").val();
	var args = 'sourcetype='+type+'&urlpage='+$('#urlpage_'+type).val()+'&pagesize_start='+$("input[name='data[pagesize_start]']").val()+'&pagesize_end='+$("input[name='data[pagesize_end]']").val()+'&par_num='+$("input[name='data[par_num]']").val();
	Wind.use("artDialog","iframeTools", function() {
        art.dialog.open(GV.DIMAUB + 'index.php?a=public_url&m=Node&g=Collection&' + args , {
            title: "测试地址",
            id: "show_url",
            width: '700px',
            height: '450px',
            lock: true,
            fixed: true,
            background: "#CCCCCC",
            opacity: 0
        });
    });	
}
//过滤规则
function html_role(id, type) {
	Wind.use("artDialog", function() {
        art.dialog({
            title: "过滤规则",
            id: 'test_url',
			content: '<label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_1"  value="&lt;p([^&gt;]*)&gt;(.*)&lt;/p&gt;[|]"> &lt;p&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_2"  value="&lt;a([^&gt;]*)&gt;(.*)&lt;/a&gt;[|]"> &lt;a&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_3"  value="&lt;script([^&gt;]*)&gt;(.*)&lt;/script&gt;[|]"> &lt;script&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_4"  value="&lt;iframe([^&gt;]*)&gt;(.*)&lt;/iframe&gt;[|]"> &lt;iframe&gt;</label><br/><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_5"  value="&lt;table([^&gt;]*)&gt;(.*)&lt;/table&gt;[|]"> &lt;table&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_6"  value="&lt;span([^&gt;]*)&gt;(.*)&lt;/span&gt;[|]"> &lt;span&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_7"  value="&lt;b([^&gt;]*)&gt;(.*)&lt;/b&gt;[|]"> &lt;b&gt;</label><br/><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_8"  value="&lt;img([^&gt;]*)&gt;[|]"> &lt;img&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_9"  value="&lt;object([^&gt;]*)&gt;(.*)&lt;/object&gt;[|]"> &lt;object&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_10"  value="&lt;embed([^&gt;]*)&gt;(.*)&lt;/embed&gt;[|]"> &lt;embed&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_11"  value="&lt;param([^&gt;]*)&gt;(.*)&lt;/param&gt;[|]"> &lt;param&gt;</label><br/><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_12"  value="&lt;div([^&gt;]*)&gt;[|]"> &lt;div&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_13"  value="&lt;/div&gt;[|]"> &lt;/div&gt;</label><label class="ib" style="width:120px"><input type="checkbox" name="html_rule" id="_14"  value="&lt;!--([^&gt;]*)--&gt;[|]"> &lt;!-- --&gt;</label><br><div class="bk15"></div>',
            width: '500px',
            height: '150px',
            lock: true,
            fixed: true,
            background: "#CCCCCC",
			ok:function(){
				var old = $("textarea[name='" + id + "']").val();
				var str = '';
				$("input[name='html_rule']:checked").each(function () {
					str += $(this).val() + "\n";
				});
				$((type == 1 ? "#" + id : "textarea[name='" + id + "']")).val((old ? old + "\n" : '') + str);
			},
            opacity: 0
        });
    });	
}
//自定义 添加采集项目
var i =0;
function add_caiji() {
	var html = '<tbody id="customize_config_'+i+'"><tr><th width="120">规则名：</th><td><input type="text" name="customize_config[name][]" class="input" /></td><th  width="120">规则英文名：</th><td><input type="text" name="customize_config[en_name][]" class="input" /></td></tr><tr><th width="120">匹配规则：</th><td><textarea rows="5" cols="40" name="customize_config[rule][]" id="rule_'+i+'"></textarea> <br>使用"<a href="javascript:insertText(\'rule_'+i+'\', \'[内容]\')">[内容]</a>"作为通配符</td><th width="120">过滤选项：</th><td><textarea rows="5" cols="50" name="customize_config[html_rule][]" id="content_html_rule_'+i+'"></textarea> <input type="button" value="选择" class="btn"  onclick="html_role(\'content_html_rule_'+i+'\', 1)"></td></tr></tbody>';
	$('#customize_config').append(html);
	i++;
}
</script>
</body>
</html>
