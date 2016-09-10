<?php if (!defined('CMS_VERSION')) exit(); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=7">
<title>{$subject}</title>
<meta name="keywords" content="">
<meta name="description" content="">
<link href="{$config_siteurl}statics/css/admin_style.css" rel="stylesheet" />
<link href="{$config_siteurl}statics/css/vote.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="{$config_siteurl}statics/js/jquery.js"></script>
</head>
<body class="body_none">
<!--main-->
<div class="wrap ">
  <form name="myform" id="myform" action="<?php echo U('Vote/Index/post','subjectid='.$subjectid);?>" method="post">
    <div class="vote_result">
      <div class="tit"><span class="r">总票数：<strong>{$vote_data['total']}</strong></span>
        <h5>标题: {$subject}</h5>
      </div>
      <div class="c_box wrap">
        <input type="hidden" name="subjectid" value="{$subjectid}">
        <table width="100%" border="1" cellspacing="0" cellpadding="0" class="tp">
          <tbody>
          <volist name="options" id="r">
            <tr>
              <th>{$i}</th>
              <td class="tp_tit"><input type=<if condition="$subject_arr['ischeckbox']=='0'">"radio"<else />"checkbox"</if> name="radio[]" id="radio" value="{$r['optionid']}" {$check_status}/>  </td>
              <td class="ls">{$r['option']}</td>
              <td class="tdcol3"><font color="red">{$r.stat}</font> 票</td>
            </tr>
          </volist>
          </tbody>
        </table>
      </div>
    </div>
    <div class="shuru_btn">
      <button class="tp_btn" type="submit" style=""></button>
      <a href="{:U('Vote/Index/result',array("subjectid"=>$subjectid))}">[查看投票结果] </a></div>
  </form>
</div>
</body>
</html>
