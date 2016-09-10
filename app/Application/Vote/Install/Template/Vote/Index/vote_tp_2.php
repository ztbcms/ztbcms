<?php if (!defined('CMS_VERSION')) exit(); ?><style type="text/css"> 
#vote-show{ border:1px solid #e5e5e5; padding:0 1px; color:#535353}
#vote-show h2{background:url("<?php echo $Config['siteurl'] ?>statics/images/vote/show_bg.png") repeat-x 0 0;height:26px; line-height:26px; padding-left:10px}
#vote-show h2 span{font-size:14px;background:url("<?php echo $Config['siteurl'] ?>statics/images/vote/vote_bg.gif") no-repeat scroll 0 1px transparent;padding-left:18px; color:#145aa3; }
#vote-show h3{ font-size:12px; font-weight:normal; line-height:26px; height:26px}
#vote-show .content{ padding:8px 10px 15px}
.vote_bar{width:100%; height:13px; border:1px solid #999; position:relative; overflow:hidden; margin-bottom:8px; background-color:#f6f6f7}
.vote_bar div{background: url(<?php echo $Config['siteurl'] ?>statics/images/admin_img/x_bg.png) repeat-x left -194px; float: left; position:relative; height:16px;_height:13px}
.vote_bar span{display:block; position:absolute; left:0; top:0; width:100%; text-align:center; height:16px; font-size:10px; line-height:12px; vertical-align:middle; z-index:200}
</style> 
<div id="vote-show">
  <form name="myform" id="myform"  action="<?php echo U('Vote/Index/post','subjectid='.$subjectid);?>" method="post" target="_blank">
    <h2 style="margin: 0;border-bottom: 0px dashed #7958D1;"><span>{$subject}</span></h2>
    <div class="content">
      <input type="hidden" name="subjectid" value="{$subjectid}">
    <volist name="options" id="r">
    <if condition="$vote_data['total'] eq 0">
        <php>$per=0;</php>
        <else />
        <php>$per=intval($r['stat']/$vote_data['total']*100);</php>
    </if>
      <h3>
        <input type=<if condition="$subject_arr['ischeckbox'] eq 0 ">"radio"<else />"checkbox"</if> name="radio[]" id="radio" value="{$r['optionid']}" {$check_status} />
        <label for="option_0">{$i}. {$r['option']}</label>
      </h3>
      <div class="vote_bar"><span>{$per}%</span>
        <div style="width: {$per}%;"></div>
      </div>
      </volist>
      <div class="bk10"></div>
      <div class="btn" style="{$display}">
        <input type="submit" value="提交">
      </div>
      &nbsp;&nbsp;<span class="f12">共有<font color="#0066FF">{$vote_data['total']}</font>人参与投票 </span> </div>
  </form>
</div>
