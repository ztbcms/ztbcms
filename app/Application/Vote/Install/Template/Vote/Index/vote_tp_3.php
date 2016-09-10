<?php if (!defined('CMS_VERSION')) exit(); ?><div class="content vote">
<form name="myform" id="myform"  action="<?php echo U('Vote/Index/post','subjectid='.$subjectid);?>" method="post" target="_blank">
<h4>{$subject}</h4>
<div class="hr bk6"><hr></div>
<input type="hidden" name="subjectid" value="{$subjectid}">
<volist name="options" id="r">
<if condition="$vote_data['total']=='0'">
    <php>$per=0;</php>
    <else />
    <php>$per=intval($vote_data[$r['optionid']]/$vote_data['total']*100);</php>
</if>
<label><input type=<if condition="$subject_arr['ischeckbox'] eq 0 ">"radio"<else />"checkbox"</if> name="radio[]" id="radio" value="{$r['optionid']}" {$check_status}/>  {$r['option']}</label>
</volist>
<div class="btn" style="{$display}"><input type="submit" value="提交" class="votebt"> <a href="<?php echo U('Vote/Index/result','subjectid='.$subjectid,true,false,true);?>" target="_blank"> [查看结果]</a></div>
</form>
</div>
