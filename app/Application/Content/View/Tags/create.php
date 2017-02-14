 
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">温馨提示</div>
  <div class="prompt_text">
    <p>数据操作会清空原有数据，进行从新建立，此操作不可恢复！</p>
  </div>
  <div class="h_a">数据重建</div>
  <form action="{:U('create',array('delete'=>'1'))}" method="post">
    <div class="table_full">
      <table cellpadding="2" cellspacing="1" class="table_form" width="100%">
        <tr>
          <th width="147">请选择需要重建的模型：</th>
          <td><?php echo \Form::select($Model, 0, 'name="modelid" id="modelid"', '重建全部模型');?></td>
        </tr>
      </table>
    </div>
    <div class="">
      <div class="btn_wrap_pd">
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
</body>
</html>
