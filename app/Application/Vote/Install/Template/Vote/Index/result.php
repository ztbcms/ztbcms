<?php if (!defined('CMS_VERSION')) exit(); ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=7">
<title>{$subject_arr['subject']}</title>
<meta name="keywords" content="{$subject_arr['subject']}">
<meta name="description" content="{$subject_arr['description']}">
<link href="{$config_siteurl}statics/css/admin_style.css" rel="stylesheet" />
<link href="{$config_siteurl}statics/css/vote.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="{$config_siteurl}statics/js/jquery.js"></script>
</head>
<body class="body_none">
<!--main-->
<div class="wrap ">
  <div class="vote_result">
    <div class="tit"><span class="r">总票数：<strong>{$vote_data['total']}</strong></span>
      <h5>{$subject_arr['subject']}</h5>
    </div>
    <div class="c_box wrap">
      <table width="100%" border="1" cellspacing="0" cellpadding="0">
        <volist name="options" id="r">
          <if condition="$vote_data['total']=='0'">
            <php>$per=0;</php>
            <else />
            <php>$per=intval($r['stat']/$vote_data['total']*100);</php>
          </if>
          <tr>
            <th>{$i}</th>
            <td class="tp_tit">{$r['option']}</td>
            <td class="tdcol3">{$per} %</td>
            <td><div><img src="{$config_siteurl}statics/images/vote/tit_cs.jpg" width="{$per}%" /></div></td>
            <td class="tdcol3">{$r.stat}票</td>
          </tr>
        </volist>
          </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
