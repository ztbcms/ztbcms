 
<Admintemplate file="Common/Head"/>
<body class="body_none" style="width:600px;">
<div class="wrap_pop">
  <div class="nav">
    <ul class="cc">
      <li class="current"><a href="{:U('Content/Content/push',array('action'=>'position_list','modelid'=>$modelid,'catid'=>$catid,'id'=>$id))}">推送到推荐位</a></li>
      <li ><a href="{:U('Content/Content/push',array('action'=>'push_to_category','modelid'=>$modelid,'catid'=>$catid,'id'=>$id))}">推送到其他栏目</a></li>
    </ul>
  </div>
  <form class="J_ajaxForm" action="{:U('Content/push','action=position_list')}" method="post">
  <input type="hidden" name="modelid" value="{$modelid}">
  <input type="hidden" name="catid" value="{$catid}">
  <input type='hidden' name="id" value='{$id}'>
    <div class="pop_cont pop_table" style="overflow-x:hidden;">
      <table width="100%">
        <tr>
          <th width="80">推荐位：</th>
          <td class="y-bg"><?php
                foreach($Position as $id=>$name){
                ?>
            <label class="ib" style="width:120px">
              <input type="checkbox" name='posid[]'  value="<?php echo $id;?>">
              <?php echo $name;?></label>
            <?php
                }
                ?></td>
        </tr>
      </table>
    </div>
    <div class="pop_bottom">
      <button class="btn fr" id="J_dialog_close" type="button">取消</button>
      <button type="submit" class="btn btn_submit J_ajax_submit_btn fr mr10">提交</button>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
