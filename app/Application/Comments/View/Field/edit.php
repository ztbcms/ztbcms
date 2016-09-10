<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <div class="nav">
  <ul class="cc">
        <div class="return"><a href="{$menuReturn.url}">{$menuReturn.name}</a></div>
        <li><a href="{:U('Field/index')}">字段管理</a></li>
        <li><a href="{:U('Field/add')}">添加字段</a></li>
      </ul>
</div>
  <div class="h_a">字段属性</div>
  <form name="myform" class="J_ajaxForm" action="{:U("Comments/Field/edit")}" method="post">
  <div class="table_full">
  <table width="100%" class="table_form contentWrap">
        <tbody>
          <tr>
            <th width="200"><strong>字段类型</strong><br></th>
            <td><select name="ftype" id="formtype">
                <option value="">请选择字段类型</option>
                <option value="VARCHAR" <if condition=" $data['ftype'] eq 'VARCHAR' ">selected</if>>字符型0-255字节(VARCHAR)</option>
                <option value="TEXT" <if condition=" $data['ftype'] eq 'TEXT' ">selected</if>>小型字符型(TEXT)</option>
                <option value="MEDIUMTEXT" <if condition=" $data['ftype'] eq 'MEDIUMTEXT' ">selected</if>>中型字符型(MEDIUMTEXT)</option>
                <option value="LONGTEXT" <if condition=" $data['ftype'] eq 'LONGTEXT' ">selected</if>>大型字符型(LONGTEXT)</option>
                <option value="TINYINT" <if condition=" $data['ftype'] eq 'TINYINT' ">selected</if>>小数值型(TINYINT)</option>
                <option value="SMALLINT" <if condition=" $data['ftype'] eq 'SMALLINT' ">selected</if>>中数值型(SMALLINT)</option>
                <option value="INT" <if condition=" $data['ftype'] eq 'INT' ">selected</if>>大数值型(INT)</option>
                <option value="BIGINT" <if condition=" $data['ftype'] eq 'BIGINT' ">selected</if>>超大数值型(BIGINT)</option>
                <option value="FLOAT" <if condition=" $data['ftype'] eq 'FLOAT' ">selected</if>>数值浮点型(FLOAT)</option>
                <option value="DOUBLE" <if condition=" $data['ftype'] eq 'DOUBLE' ">selected</if>>数值双精度型(DOUBLE)</option>
            </select></td>
          </tr>
          <tr>
            <th><strong>存放表</strong></th>
            <td><input type="radio" name="issystem" value="1" <if condition=" $data['issystem'] eq '1' ">checked</if> disabled="disabled">主表  <input type="radio" name="issystem" value="0" <if condition=" $data['issystem'] eq '0' ">checked</if> disabled="disabled">副表</td>
          </tr>
          <tr>
            <th width="25%"><font color="red">*</font> <strong>字段名</strong><br>
              只能由英文字母、数字和下划线组成，并且仅能字母开头，不以下划线结尾 </th>
            <td>{$data.f}</td>
          </tr>
          <tr>
            <th><font color="red">*</font> <strong>字段标识</strong><br>
              例如：文章标题</th>
            <td><input class="input" name="fname" type="text" id="fname" value="{$data.fname}"></td>
          </tr>
          <tr>
            <th><strong>字段长度</strong><br></th>
            <td><input class="input" name="flen" type="text" id="flen" value="{$data.flen}" size="6"></td>
          </tr>
          <tr>
            <th><strong>是否必填项</strong></th>
            <td><input type="radio" name="ismust" value="1" <if condition=" $data['ismust'] eq '1' ">checked</if>>是  <input type="radio" name="ismust" value="0" <if condition=" $data['ismust'] eq '0' ">checked</if>>否</td>
          </tr>
          <tr>
            <th><strong>数据校验正则</strong><br>
              系统将通过此正则校验表单提交的数据合法性，如果不想校验数据请留空</th>
            <td><input type="text" name="regular" id="pattern" value="{$data.regular}" size="40" class="input">
              <select name="pattern_select" onChange="javascript:$('#pattern').val(this.value)">
                <option value="">常用正则</option>
                <option value="/^[0-9.-]+$/">数字</option>
                <option value="/^[0-9-]+$/">整数</option>
                <option value="/^[a-z]+$/i">字母</option>
                <option value="/^[0-9a-z]+$/i">数字+字母</option>
                <option value="/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/">E-mail</option>
                <option value="/^[0-9]{5,20}$/">QQ</option>
                <option value="/^http:\/\//">超级链接</option>
                <option value="/^(1)[0-9]{10}$/">手机号码</option>
                <option value="/^[0-9-]{6,13}$/">电话号码</option>
              </select></td>
          </tr>
          <tr>
            <th><strong>字段提示</strong><br>
              当没有输入的时候作为提示</th>
            <td><textarea name="fzs" rows="2" cols="20" id="tips" style="height:40px; width:80%">{$data.fzs}</textarea></td>
          </tr>
        </tbody>
      </table>
  </div>
  <div class="">
      <div class="btn_wrap_pd">             
        <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
        <input name="fid" value="{$data.fid}" type="hidden" />
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
