<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">投票设置</div>
  <form action="{:U('Vote/edit',array("subjectid"=>$subjectid))}" method="post" name="myform" class="J_ajaxForm">
  <div class="table_full">
  <table width="100%"class="table_form contentWrap">
      <tr>
        <th width="100" align="right">投票标题：</th>
        <td><input name="subject[subject]" type="text" class="input" id="subject_title" value="{$info['subject']}" size="30"></td>
      </tr>
      <tr>
        <th align="right">选项类型：</th>
        <td>
        <select name="subject[ischeckbox]" id="" onchange="AdsType(this.value)">
            <option value="0" <if condition="$info['ischeckbox']=='0'"> selected="selected"</if> >单选</option>
            <option value="1" <if condition="$info['ischeckbox']=='1'"> selected="selected"</if> >多选</option>
          </select>
          </td>
      </tr>
      <tr id="SizeFormat" <if condition="$info['ischeckbox']=='0'"> style="display: none;"</if>>
        <th align="right"></th>
        <td><label>最少选项</label>
          
          <input name="subject[minval]" class="input" type="text" size="5" value="{$info['minval']}">
          项 
          <label>最多选项</label>
          
          <input
			name="subject[maxval]" type="text" class="input" size="5" value="{$info['maxval']}">
          项</td>
      </tr>
      <tr>
        <th align="right">投票选项：</th>
        <td>
        
         <input type="button" id="addItem" value="增加选项" class="button" onclick="add_option()">
          <div id="option_list_1">
              <volist name="options" id="r">
                <div id="option{$r['optionid']}"><br>
                  <input type="text" name="option[{$r['optionid']}]" size="40" value="{$r['option']}" require="true"  class="input"/> <if condition="$i gt 2"><input type="button" value="删除"  onclick="del_old({$r['optionid']})" class="button"/></if>
                </div>
              </volist>
          </div>
          <div id="new_option"></div></td>
      </tr>
      <tr>
        <th align="right">上线时间：</th>
        <td>
          <input type="text" name="subject[fromdate]" id="fromdate" value="{$info['fromdate']}" size="20" class="input J_datetime"></td>
      </tr>
      <tr>
        <th align="right">下线时间：</th>
        <td><input type="text" name="subject[todate]" id="todate" value="{$info['todate']}" size="20" class="input J_datetime" ></td>
      </tr>
      <tr>
        <th align="right">投票介绍：</th>
        <td><textarea name="subject[description]" id="description" cols="60" rows="6">{$info['description']}</textarea></td>
      </tr>
      <tr>
        <th align="right">查看投票结果：</th>
        <td>
         <input name="subject[allowview]" type="radio" value="1" <if condition="$info['allowview']== 1 ">checked</if> >
          允许
          <input name="subject[allowview]" type="radio" value="0" <if condition="$info['allowview']== 0 ">checked</if>>
          不允许
        </td>
      </tr>
      <tr>
        <th align="right">允许游客投票：</th>
        <td><input name="subject[allowguest]" type="radio" value="1" <if condition="$info['allowguest'] eq 1 ">checked</if>>
          是
          <input
			name="subject[allowguest]" type="radio" value="0" <if condition="$info['allowguest'] eq 0 ">checked</if>>
          否</td>
      </tr>
      <tr>
        <th align="right">奖励积分：</th>
        <td><input name="subject[credit]" type="text" value="{$info['credit']}" size='5' class="input"></td>
      </tr>
      <tr>
        <th align="right">投票时间间隔： </th>
        <td><input type="text" name="subject[interval]" value="{$info['interval']}" size='5' class="input"/>
          N分钟后可再次投票，<font color=red>0</font> 表示此IP地址只能投一次</td>
      </tr>
      <tr>
        <th align="right">投票项排序：</th>
        <td>
        <select name="setting[order]" id="style">
            <option value="0" <if condition="$info['setting']['order'] eq '0'"> selected="selected" </if>>默认排序</option>
            <option value="1" <if condition="$info['setting']['order'] eq '1'"> selected="selected" </if>>票数从小到大</option>
            <option value="2" <if condition="$info['setting']['order'] eq '2'"> selected="selected" </if>>票数从大到小</option>
        </select>
        </td>
      </tr>
      <tr>
        <th align="right">模版：</th>
        <td id="show_template">
          <select name="vote_subject[vote_tp_template]">
            <option value='' >请选择</option>
            <volist name="template_list" id="r">
                <option value="{$r}"  <if condition="$info['template'] eq $r"> selected="selected" </if> >{$r}</option>
            </volist>            
          </select>
        </td>
      </tr>
      <tr>
        <th align="right">是否启用：</th>
        <td><input name="subject[enabled]" type="radio" value="1" <if condition="$info['enabled']=='1'">checked</if>>
          是
          <input name="subject[enabled]" type="radio" value="0" <if condition="$info['enabled']=='0'">checked</if>>
          否</td>
      </tr>
    </table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
        <input type="hidden"name="from_api" value="">
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script language="javascript">
function AdsType(adstype) {
	$('#SizeFormat').css('display', 'none');
	if(adstype=='0') {
		
	} else if(adstype=='1') {
		$('#SizeFormat').css('display', '');
	}
}
var i = 1;
function add_option() {
	var htmloptions = '';
	htmloptions += '<div id='+i+'><span><br><input type="text" name="newoption[]" size="40"  value="" class="input"/> <input type="button" value="删除"  onclick="del('+i+')" class="button"/><br></span></div>';
	$(htmloptions).appendTo('#new_option'); 
	var htmloptions = '';
	i = i+1;
}
function del(o){
 $("div [id=\'"+o+"\']").remove();	
}
function del_old(id) {
	$.get(GV.DIMAUB+'index.php?g=Vote&m=Vote&a=public_ajax_option&optionid='+id,null,function (msg) { 
	    if (msg==1) { 
	     $("div [id=\'option"+id+"\']").remove(); 
	    } else {
	      alert(msg); 
	    } 
	}); 
} 
</script>
</body>
</html>
