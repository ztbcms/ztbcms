 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>我的分享 - {$Config.sitename}</title>
<template file="Member/Public/global_js.php"/>
<script type="text/javascript" src="{$model_extresdir}js/common.js"></script>
<link href="/favicon.ico" rel="shortcut icon">
<link type="text/css" href="{$model_extresdir}css/core.css" rel="stylesheet" />
<link rel="stylesheet" type="text/css" href="{$model_extresdir}css/common.css" />
<link type="text/css" href="{$model_extresdir}css/user.css" rel="stylesheet" media="all" />
</head>
<body>
<template file="Member/Public/homeHeader.php"/>
<div class="user">
  <div class="center">
    <div class="main_nav">
      <div class="title"></div>
      <ul>
        <li class="share"><a <if condition=" !$type">class="on"</if> href="{:U('Share/index')}">我分享的</a></li>
        <li class="deleting"><a <if condition=" $type eq  'check' ">class="on"</if> href="{:U('Share/index',array('type'=>'check'))}">已审核的</a></li>
        <li class="audit"><a <if condition=" $type eq  'checking' ">class="on"</if> href="{:U('Share/index',array('type'=>'checking'))}">审核中的</a></li>
      </ul>
      <div class="action"><a target="_self" href="{:U('Share/add')}">发布分享</a></div>
      <div class="return"><a title="返回个人中心" href="{:U('Index/home')}">个人中心</a></div>
    </div>
    <div class="main_nav2">
      <ul>
        <li  class="current"><a  href="{:U('Share/index')}">全部类型</a></li>
      </ul>
    </div>
    <div class="hint">
      <div class="hint_box_t">
        <div class="hint_box_t_l"></div>
        <div class="hint_box_t_r"></div>
      </div>
      <div class="hint_box_l">
        <div class="hint_box_r"><p><b>审核提示：网站管理员会尽快进行审核请您耐心等候，审核通过后，我们将及时通知您。</b></p></div>
      </div>
      <div class="hint_box_b">
        <div class="hint_box_b_l"></div>
        <div class="hint_box_b_r"></div>
      </div>
    </div>
    <div id="favoritesList" class="minHeight500">
      <if condition=" empty($share) ">
      <div class="favoritesList shareList">
          <div class="nothing">您还没有分享舞曲。</div>
      </div>
      <else />
      <div class="danceNewList">
        <form id="list" name="form1" method="post" action="">
          <ul>
            <li class="title">
              <div class="song">资讯名称</div>
              <div class="state">审核状态</div>
              <div class="class">发表栏目</div>
              <div class="time">发表时间</div>
              <div class="editing">编辑</div>
              <div class="deleting">删除</div>
            </li>
            <volist name="share" id="vo">
            <li>
              <div class="song">
                <div class="aleft"><a class="mname" href="{$vo.url}" target="_blank">{$vo.id}：{$vo.title}</a></div>
              </div>
              <div class="state"><if condition=" $vo['status'] eq 99 ">审核通过<else /><font color="#1D94C7">待审核</font></if></div>
              <div class="class"><a href="{:getCategory($vo['catid'],'url')}" target="_blank">{:getCategory($vo['catid'],'catname')}</a></div>
              <div class="time">{$vo.inputtime|format_date}</div>
              <if condition=" $vo['_setting']['member_admin'] ">
              <switch name="vo['_setting']['member_admin']" >
              	<case value="1">
                	<if condition=" $r['status'] eq 1 ">
                        <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                        <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                    <else />
                        <div class="action"><a class="edit" href="{:U('Share/edit',array('id'=>$vo['_shareid'],) )}" title="编辑"></a></div>
                        <div class="action"><a class="del" href="javascript:;" onclick="del({$vo._shareid})" title="删除" id="del{$vo._shareid}"></a></div>
                    </if>
                </case>
                <case value="2">
                	<if condition=" $r['status'] eq 1 ">
                        <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                        <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                    <else />
                        <div class="action"><a class="edit" href="{:U('Share/edit',array('id'=>$vo['_shareid'],) )}" title="编辑"></a></div>
                        <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                    </if>
                </case>
                <case value="3">
                	<if condition=" $r['status'] eq 1 ">
                        <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                        <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                    <else />
                        <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                        <div class="action"><a class="del" href="javascript:;" onclick="del({$vo._shareid})" title="删除" id="del{$vo._shareid}"></a></div>
                    </if>
                </case>
                <case value="4">
                	<div class="action"><a class="edit" href="{:U('Share/edit',array('id'=>$vo['_shareid'],) )}" title="编辑"></a></div>
                    <div class="action"><a class="del" href="javascript:;" onclick="del({$vo._shareid})" title="删除" id="del{$vo._shareid}"></a></div>
                </case>
                <case value="5">
                	<div class="action"><a class="edit" href="{:U('Share/edit',array('id'=>$vo['_shareid'],) )}" title="编辑"></a></div>
                    <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                </case>
                <case value="6">
                	<div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
                    <div class="action"><a class="del" href="javascript:;" onclick="del({$vo._shareid})" title="删除" id="del{$vo._shareid}"></a></div>
                </case>
              </switch>
              <else />
              <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
              <div class="action"><a class="operation" href="javascript:;" title="无法操作"></a></div>
              </if>
            </li>
            </volist>
          </ul>
        </form>
        <div class="page">
              {$Page}
        </div>
      </div>
      </if>
    </div>
  </div>
<template file="Member/Public/homeFooter.php"/>
</div>
<script>
function del(id) {
    $.dialog({
        id: 'delAll',
        title: false,
        border: false,
        follow: $("#del" + id)[0],
        content: '确认删除此分享吗？',
        okValue: '确认',
        ok: function () {
            $.ajax({
                type: "POST",
                global: false, // 禁用全局Ajax事件.
                url: _config['domainSite'] + "index.php?g=Member&m=Share&a=del",
                data: {
                    'id': id
                },
                dataType: "json",
                success: function (data) {
                    if (data['error'] == 20001) {
                        libs.userNotLogin('您未登录无法执行此操作！');
                    } else if (data['error'] == 20002) {
                        $.tipMessage("对不起，你无权删除", 1, 3000, 0, function () {
                            location.href = location.href;
                        });
                    } else if (data['error'] == 10000) {
                        location.href = location.href;
                    } else {
                        $.tipMessage(data['info'], 1, 3000, 0);
                    }
                }
            });
        },
        cancelValue: '取消',
        cancel: function () {

        }
    });
}
</script>
</body>
</html>
