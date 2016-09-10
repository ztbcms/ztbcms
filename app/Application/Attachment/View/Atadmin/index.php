<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">搜索</div>
  <form method="post" action="{:U('index')}">
    <div class="search_type cc mb10">
      <div class="mb10"> <span class="mr20">
      上传时间：
        <input type="text" name="start_uploadtime" class="input length_2 J_date" value="{$_GET.start_uploadtime}">
        -
        <input type="text" class="input length_2 J_date" name="end_uploadtime" value="{$_GET.end_uploadtime}">
        附件类型：
        <input type="text" class="input length_2" name="fileext" style="width:80px;" value="{$_GET.fileext}" placeholder="类型：jpg、png">
        使用状态：
        <select name="status" id="status" >
            <option value="" <if condition="$_GET.status eq '' "> selected</if>>全部</option>
            <option value="1" <if condition="$_GET.status eq '1' "> selected</if>>已经在使用</option>
            <option value="0" <if condition="$_GET.status eq '0' "> selected</if>>没有被使用</option>
          </select> 
          附件名称：
        <input type="text" class="input length_2" name="filename" style="width:200px;" value="{$_GET.filename}" placeholder="请输入附件名称...">
        <button class="btn">搜索</button>
        </span> </div>
    </div>
  </form>
  <form name="myform" action="{:U("Atadmin/delete")}" method="post" class="J_ajaxForm">
  <div class="table_list">
  <table width="100%" cellspacing="0">
      <thead>
        <tr>
          <td width="90" align="center"><input type="checkbox" class="J_check_all" data-direction="x" data-checklist="J_check_x">全选</td>
          <td width="40" align="center">ID</td>
          <td width="100" align="center" >上传用户ID </td>
          <td width="180" align="center" >栏目名称</td>
          <td>附件名称</td>
        <td width="100" align="center">附件大小</td>
        <td width="120" align="center">上传时间</td>
        <td width="100" align="center">管理操作</td>
      </tr>
      </thead>
      <tbody>
      <volist name="data" id="vo">
        <tr>
          <td align="center"><input type="checkbox" class="J_check" data-yid="J_check_y" data-xid="J_check_x" name="aid[]" value="{$vo.aid}" id="att_{$vo.aid}" /></td>
          <td align="center">{$vo.aid}</td>
          <td align="center"><if condition=" $vo['isadmin'] ">[后台]</if><if condition=" !$vo['userid'] ">游客<else/>{$vo.userid}</if></td>
          <td align="center"><if condition=" $vo['catid'] ">{:getCategory($vo['catid'],'catname')}</if></td>
          <td >
          <img src="{$config_siteurl}statics/images/ext/{$vo.fileext}.gif" />{$vo.filename} 
          <if condition=" $vo['thumb'] ">
          <img title="管理缩略图" src="{$config_siteurl}statics/images/icon/havthumb.png" onClick="showthumb({$vo.aid}, '{$vo.filename}')"/>
          </if>
          <if condition=" $vo['status'] ">
          <img title="该附件已被使用" src="{$config_siteurl}statics/images/icon/alink.png"/>
          </if>
          </td>
          <td align="center">{$vo.filesize} KB</td>
          <td align="center">{$vo.uploadtime|date="Y-m-d H:i:s",###}</td>
          <td align="center"><a href="javascript:preview({$vo.aid}, '{$vo.filename}','{$Config.sitefileurl}{$vo.filepath}')">预览</a> | <a class="J_ajax_del" href="{:U('Attachment/Atadmin/delete',array('aid'=>$vo['aid']))}">删除</a></td>
        </tr>
      </volist>
      </tbody>
    </table>
    <div class="p10">
        <div class="pages"> {$Page} </div>
      </div>
  </div>
  <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <label class="mr20"><input type="checkbox" class="J_check_all" data-direction="y" data-checklist="J_check_y">全选</label> 
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">删除附件</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
<script src="{$config_siteurl}statics/js/content_addtop.js?v"></script>
<script type="text/javascript">
//附件预览
function preview(id, name, filepath) {
    if (IsImg(filepath)) {
        Wind.use('artDialog', 'imgready', function () {
            imgReady(filepath, function () {
                var width = 0,
                    maxWidth = 500,
                    maxHeight = 500,
                    height = 0;
                var hRatio;
                var wRatio;
                var Ratio = 1;
                var w = this.height;
                var h = this.width;
                wRatio = 500 / this.height;
                hRatio = 500 / this.width;

                if (maxWidth == 0) { //
                    if (hRatio < 1) Ratio = hRatio;
                } else if (maxHeight == 0) {
                    if (wRatio < 1) Ratio = wRatio;
                } else if (wRatio < 1 || hRatio < 1) {
                    Ratio = (wRatio <= hRatio ? wRatio : hRatio);
                }
                if (Ratio < 1) {
                    w = w * Ratio;
                    h = h * Ratio;
                }
                width = h;
                height = w;

                art.dialog({
                    title: '预览',
                    fixed: true,
                    width: width,
                    height: height,
                    id: "image_priview",
                    lock: true,
                    background: "#CCCCCC",
                    opacity: 0,
                    content: '<img src="' + filepath + '"  width="' + width + '" height="' + height + '" />'
                });

            });
        });
    } else {
        Wind.use('artDialog', function () {
            art.dialog({
                title: '预览',
                padding: 0,
                width: 230,
                height: 150,
                content: '<a href="' + filepath + '" target="_blank"><img src="{$config_siteurl}statics/images/icon/down.gif">单击打开</a>',
                lock: true
            });
        });
    }
}

//缩图管理
function showthumb(id, name) {
    Wind.use('artDialog', 'iframeTools', function () {
        art.dialog.open(GV.DIMAUB + 'index.php?a=public_showthumbs&m=Admin&g=Attachment&aid=' + id, {
            title: '管理缩略图--' + name,
            padding: 0,
            width: '500px',
            height: '400px',
            lock: true,
            background: "#CCCCCC",
            opacity: 0
        });
    });
}
</script>
</body>
</html>
