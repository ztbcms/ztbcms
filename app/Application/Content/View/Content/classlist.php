<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="nav">
    <ul class="cc">
      <li <if condition=" !isset($_GET['status']) ">class="current"</if>><a href="{:U('Content/classlist', array('catid'=>$catid)  )}">审核通过（{$sum}条）</a></li>
      <li <if condition=" isset($_GET['status']) AND $_GET['status'] == 1 ">class="current"</if>><a href="{:U('Content/classlist', array('catid'=>$catid ,'search'=>1 ,'status'=>1)  )}">待审核（{$checkSum}条）</a></li>
      <li <if condition=" isset($_GET['status']) AND $_GET['status'] == 0 ">class="current"</if>><a href="{:U('Content/classlist', array('catid'=>$catid ,'search'=>1 ,'status'=>0)  )}">审核未通过（{$uncheckSum}条）</a></li>
    </ul>
  </div>
  <div class="mb10">
		<a href="javascript:void(0)" onClick="javascript:openwinx('{:U("Content/add",array("catid"=>$catid))}','')" class="btn" title="添加内容"><span class="add"></span>添加内容</a>
         栏目列表生成：<select class="select_2" onChange="window.location.href=''+this.value+''">
       <option value="{:U('Createhtml/categoryhtml',array('catid'=>$catid))}" >列表生成</option>
       <option value="{:U('Createhtml/categoryhtml',array('catid'=>$catid))}">生成当前栏目列表</option>
       <if condition=" $parentid "> 
       <option value="{:U('Createhtml/categoryhtml',array('catid'=>$parentid))}">生成父栏目列表</option>
       </if>
    </select>
    <a href="{$url}" target="_blank"  class="btn" title="访问该栏目">访问该栏目</a>
  </div>
  <div class="h_a">搜索</div>
  <form method="post" action="{:U('classlist',array('catid'=>$catid))}">
  <input type="hidden" value="{$catid}" name="catid">
  <input type="hidden" value="0" name="steps">
  <input type="hidden" value="1" name="search">
    <div class="search_type cc mb10">
      <div class="mb10"> 
        <section style="display: inline;">
            <span class="mr20">时间：
            <input type="text" name="start_time" class="input length_2 J_date" value="{$Think.get.start_time}">-<input type="text" class="input length_2 J_date" name="end_time" value="{$Think.get.end_time}" >

        </section>

        <section style="display: inline;">
          <select name="_filter[0]" class="select_2">
            <option value="username" <if condition=" $_filter[0] == 'username' "> selected</if>>发布人</option>
            <option value="id" <if condition=" $_filter[0] == 'id' "> selected</if>>用户ID</option>
            <option value="title" <if condition=" $_filter[0] == 'title' "> selected</if>>标题</option>
          </select>

          <select name="_operater[0]" class="select_2">
            <option value="EQ" <if condition=" $_operater[0] == 'EQ' "> selected</if>>等于</option>
            <option value="NEQ" <if condition=" $_operater[0] == 'NEQ' "> selected</if>>不等于</option>
            <option value="GT" <if condition=" $_operater[0] == 'GT' "> selected</if>>大于</option>
            <option value="EGT" <if condition=" $_operater[0] == 'EGT' "> selected</if>>大于等于</option>
            <option value="LT" <if condition=" $_operater[0] == 'LT' "> selected</if>>小于</option>
            <option value="ELT" <if condition=" $_operater[0] == 'ELT' "> selected</if>>小于等于</option>
            <option value="LIKE" <if condition=" $_operater[0] == 'LIKE' "> selected</if>>模糊查询</option>
          </select>

          <input class="input length_2" type="text" name="_value[0]" value="{$_value[0]}">
        </section>

        <input type="hidden" name="status" value="{:I('get.status', 99)}">
        <button class="btn">搜索</button>
        </span>
      </div>
    </div>
  </form>
  <form class="J_ajaxForm" action="" method="post">
    <div class="table_list">
      <table width="100%">
        <thead>
          <tr>
            <td><label><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x"></label></td>
            <td>排序</td>
            <td align="center">ID</td>
            <td>标题</td>
            <td align="center">点击量</td>
            <td align="center">发布人</td>
            <td align="center"><span>发帖时间</span></td>
            <td align="center">管理操作</td>
          </tr>
        </thead>
        <volist name="data" id="vo">
          <tr>
            <td><input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x" name="ids[]" value="{$vo.id}"></td>
            <td><input name='listorders[{$vo.id}]' class="input mr5"  type='text' size='3' value='{$vo.listorder}'></td>
            <td align="center"><a href="{:U("Createhtml/batch_show", array("catid"=>$vo['catid'] ,"steps"=>"0" ,"ids"=>$vo['id'])  )}" title="点击生成">{$vo.id}</a></td>
            <td>
              <if condition=" $vo['status']==99 ">
                  <a href="{$vo.url}" target="_blank"><span style="" >{$vo.title}</span></a>
                <elseif condition=" $vo['status']==0 " />
                  <a href="{:U('public_preview',array('catid'=>$vo['catid'],'id'=>$vo['id']) )}" target="_blank"><font color="#FF0000">[审核未通过]</font> - {$vo.title}</a>
                <else/>
                  <a href="{:U('public_preview',array('catid'=>$vo['catid'],'id'=>$vo['id']) )}" target="_blank"><font color="#FF0000">[未审核]</font> - {$vo.title}</a>
              </if>
              <if condition=" $vo['thumb']!='' "> <img src="{$config_siteurl}statics/images/icon/small_img.gif" title="标题图片"> </if>
              <if condition=" $vo['posid'] "> <img src="{$config_siteurl}statics/images/icon/small_elite.gif" title="推荐位"> </if>
              <if condition=" $vo['islink'] "> <img src="{$config_siteurl}statics/images/icon/link.png" title="转向地址"> </if>
            </td>
            <td align="center">{$vo.views}</td>
            <td align="center"><if condition=" $vo['sysadd'] ">{$vo.username}
                <else />
                <font color="#FF0000">{$vo.username}</font><img src="{$config_siteurl}statics/images/icon/contribute.png" title="会员投稿"></if></td>
            <td align="center">{$vo.updatetime|date="Y-m-d H:i:s",###}</td>
            <td align="center">
            <?php
			$op = array();
			$op[] = '<a href="javascript:;;" onClick="javascript:openwinx(\''.U("Content/edit",array("catid"=>$vo['catid'],"id"=>$vo['id'])).'\',\'\')">修改</a>';
			$op[] = '<a href="'.U("Content/delete",array("catid"=>$vo['catid'],"id"=>$vo['id'])).'" class="J_ajax_del" >删除</a>';
			if(isModuleInstall('Comments')){
				$op[] = '<a href="'.U('Comments/Comments/index',array('searchtype'=>2,'keyword'=>'c-'.$vo['catid'].'-'.$vo['id'].'')).'" target="_blank">评论</a>';
			}
			echo implode(' | ',$op);
			?>
            </td>
          </tr>
        </volist>
      </table>
      <div class="p10"><div class="pages"> {$Page} </div> </div>
     
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <label class="mr20"><input type="checkbox" class="J_check_all" data-direction="y" data-checklist="J_check_y">全选</label>                
        <button class="btn J_ajax_submit_btn" type="submit" data-action="{:U('Content/listorder',array('catid'=>$catid))}">排序</button>
        <button class="btn J_ajax_submit_btn" type="submit" data-action="{:U('Content/public_check',array('catid'=>$catid))}">审核</button>
        <button class="btn J_ajax_submit_btn" type="submit" data-action="{:U('Content/public_nocheck',array('catid'=>$catid))}">取消审核</button>
        <button class="btn J_ajax_submit_btn" type="submit" data-action="{:U('Content/delete',array('catid'=>$catid))}">删除</button>
        <button class="btn" type="button" onClick="pushs()">推送</button>
        <button class="btn" type="button" id="J_Content_remove">批量移动</button>
        <button class="btn J_ajax_submit_btn" type="submit" data-action="{:U('Createhtml/batch_show',array('catid'=>$catid,'steps'=>0))}">批量生成HTML</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
setCookie('refersh_time', 0);
function refersh_window() {
    var refersh_time = getCookie('refersh_time');
    if (refersh_time == 1) {
        window.location.reload();
    }
}
setInterval(function(){
	refersh_window()
}, 3000);
$(function () {
    Wind.use('ajaxForm','artDialog','iframeTools', function () {
        //批量移动
        $('#J_Content_remove').click(function (e) {
            var str = 0;
            var id = tag = '';
            $("input[name='ids[]']").each(function () {
                if ($(this).attr('checked')) {
                    str = 1;
                    id += tag + $(this).val();
                    tag = '|';
                }
            });
            if (str == 0) {
				art.dialog.through({
					id:'error',
					icon: 'error',
					content: '您没有勾选信息，无法进行操作！',
					cancelVal: '关闭',
					cancel: true
				});
                return false;
            }
            var $this = $(this);
            art.dialog.open("{$config_siteurl}index.php?g=Content&m=Content&a=remove&catid={$catid}&ids=" + id, {
                title: "批量移动"
            });
        });
    });
});

function view_comment(obj) {
	Wind.use('artDialog','iframeTools', function () {
         art.dialog.open($(obj).attr("data-url"), {
			close:function(){
				$(obj).focus();
			},
            title: $(obj).attr("data-title"),
			width:"800px",
            height: '520px',
			id:"view_comment",
            lock: true,
            background:"#CCCCCC",
            opacity:0
        });
    });
}

function pushs() {
    var str = 0;
    var id = tag = '';
    $("input[name='ids[]']").each(function () {
        if ($(this).attr('checked')) {
            str = 1;
            id += tag + $(this).val();
            tag = '|';
        }
    });
    if (str == 0) {
       art.dialog({
		   id:'error',
		   icon: 'error',
		   content: '您没有勾选信息，无法进行操作！',
		   cancelVal: '关闭',
		   cancel: true
		});
        return false;
    }
    Wind.use('artDialog','iframeTools', function () {
         art.dialog.open("{$config_siteurl}index.php?g=Content&m=Content&a=push&action=position_list&modelid={$modelid}&catid={$catid}&id=" + id, {
            title: "信息推送"
        });
    });
}
</script>
</body>
</html>
