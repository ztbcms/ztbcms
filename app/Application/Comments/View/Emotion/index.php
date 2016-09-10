<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="nav">
    <div class="return"><a href="{:U('Comments/Comments/config',array('menuid'=>$_GET['menuid']))}">返回评论管理</a></div>
    <ul class="cc">
      <li class="current"><a href="{:U('Comments/Emotion/index')}">表情管理</a></li>
    </ul>
  </div>
  <div class="h_a">表情安装说明</div>
    <div class="prompt_text">
      <ol>
        <li>请将包含表情文件的文件夹通过ftp上传至 <span class="red">statics/images/emotion/</span> 目录下。</li>
        <li>“表情名称”请不要包含“[”，“]”还有其他字符！</li>
        <li>“表情名称”将会作为调用代码。</li>
      </ol>
  </div>
  <form class="J_ajaxForm" action="{:U('Comments/Emotion/index','action=save')}" method="post">
    <div class="J_check_wrap">
      <div class="table_list mb10">
        <div class="h_a">表情管理</div>
        <table width="100%">
          <tr>
            <td><label><input type="checkbox" data-direction="x" data-checklist="J_check_x1" class="J_check_all">启用</label></td>
            <td>顺序</td>
            <td>表情名称</td>
            <td>表情代码</td>
            <td>文件名</td>
            <td>预览</td>
            <td>操作</td>
          </tr>
          <volist name="data" id="vo">
          <tr>
            <td><input type="checkbox" name="isused[{$vo.emotion_id}]"  <if condition=" $vo['isused'] "> checked</if> value="{$vo.emotion_id}" class="J_check" data-yid="J_check_y1" data-xid="J_check_x1" />
              <input type="hidden" name="emotionid[]" value="{$vo.emotion_id}"/></td>
            <td><input type="number" class="input length_1" name="orderid[]" value="{$vo.vieworder}"/></td>
            <td><input type="text" class="input length_3" name="emotionname[]" value="{$vo.emotion_name}"/></td>
            <td>[{$vo.emotion_name}]</td>
            <td>{$vo.emotion_icon}</td>
            <td><img src="{$config_siteurl}statics/images/emotion/{$vo.emotion_icon}" /></td>
            <td><a href="javascript:confirmurl('{:U('Comments/Emotion/index',array('action'=>'delete','emotion_id'=>$vo['emotion_id']))}','确认要删除吗？')">[删除]</a></td>
          </tr>
          </volist>
        </table>
      </div>
      <div class="btn_wrap_pd">
        <label class="mr20">
          <input type="checkbox" data-direction="y" data-checklist="J_check_y1" class="J_check_all">
          全选</label>
        <button class="btn btn_submit J_ajax_submit_btn" type="submit" data-subcheck="true">提交</button>
      </div>
    </div>
  </form>
  <form class="J_ajaxForm" action="{:U('Comments/Emotion/index','action=add')}" method="post">
    <div class="J_check_wrap">
      <div class="table_list mb10">
        <div class="h_a">未添加的表情</div>
        <table width="100%">
          <tr>
            <td><label><input type="checkbox" data-direction="x" data-checklist="J_check_x2" class="J_check_all">全选</label></td>
            <td>顺序</td>
            <td>表情名称</td>
            <td>&nbsp;</td>
            <td>文件名</td>
            <td>预览</td>
            <td>操作</td>
          </tr>
          <volist name="noEmotion" id="vo">
          <tr>
            <td><input type="checkbox" name="emotionid[{$key}]" value="{$key}" class="J_check J_emotionid" data-yid="J_check_y2" data-xid="J_check_x2" /></td>
            <td><input type="number" class="input length_1 J_orderid" name="orderid[{$key}]" /></td>
            <td><input type="text" class="input length_3 J_emotionname" name="emotionname[{$key}] "/></td>
            <td>&nbsp;</td>
            <td>{$vo.filename}<input type="hidden" value="{$vo.filename}" name="icon[{$key}]" class="J_icon" /></td>
            <td><img src="{$config_siteurl}statics/images/emotion/{$vo.filename}" /></td>
            <td><a href="{:U('Comments/Emotion/index','action=add')}" class="J_emotion_add">[添加]</a></td>
          </tr>
          </volist>
        </table>
      </div>
      <div class="btn_wrap_pd">
        <label class="mr20"><input type="checkbox" data-direction="y" data-checklist="J_check_y2" class="J_check_all">全选</label>
        <button class="btn btn_submit J_ajax_submit_btn" type="submit">添加</button>
        <input type="hidden" value="2" name="catid"/>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script>
$(function(){
	//添加
	$('a.J_emotion_add').on('click', function(e){
		e.preventDefault();
		var tr = $(this).parents('tr');
		$.post($(this).attr('href'), {
			emotionid : [tr.find('input.J_emotionid').val()],
			orderid : [tr.find('input.J_orderid').val()],
			emotionname : [tr.find('input.J_emotionname').val()],
			icon : [tr.find('input.J_icon').val()],
		}, function(data){
			if(data.state === 'success') {
				reloadPage(window);
			}else if( data.state === 'fail' ) {
				isalert(data.info);
			}
		}, 'json');
	});
});
//询问
function isalert(message) {
    Wind.use("artDialog", "iframeTools", function() {
        art.dialog.alert(message);
    });
}
</script>
</body>
</html>
