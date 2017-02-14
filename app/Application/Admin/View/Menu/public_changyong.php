 
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap">
  <div class="h_a">常用菜单</div>
  <form class="J_ajaxForm" action="{:U('Menu/public_changyong')}" method="post">
    <div class="table_full J_check_wrap">
      <table width="100%">
        <col class="th" />
        <col width="400" />
        <col />
        <tr>
          <th><label>
              <input disabled=&quot;true&quot; checked id="J_role_custom" class="J_check_all" data-direction="y" data-checklist="J_check_custom" type="checkbox">
              <span>常用</span></label></th>
          <td><ul data-name="custom" class="three_list cc J_ul_check">
              <li>
                <label>
                  <input disabled checked data-yid="J_check_custom" class="J_check" type="checkbox" >
                  <span>常用菜单</span></label>
              </li>
            </ul></td>
          <td><div class="fun_tips"></div></td>
        </tr>
        <volist name="data" id="menu">
        <?php $skey = $key;?>
          <tr>
            <th><label><input  id="J_role_{$key}" class="J_check_all" data-direction="y" data-checklist="J_check_{$key}" type="checkbox"><span><?php echo $name[ucwords($key)]?$name[ucwords($key)]:$key;?></span></label></th>
            <td>
                  <ul data-name="{$key}" class="three_list cc J_ul_check">
                  <volist name="menu" id="r">
                    <li><label><input  name="menu[]" data-yid="J_check_{$skey}" class="J_check" type="checkbox" value="{$r.id}" <if condition="  in_array($r['id'],$panel) ">checked</if>
><span>{$r.name}</span></label></li>
                    </volist>
                  </ul>
              </td>
            <td><div class="fun_tips"></div></td>
          </tr>
        </volist>
      </table>
    </div>
    <div class="btn_wrap">
      <div class="btn_wrap_pd">
        <button class="J_ajax_submit_btn btn btn_submit" type="submit">提交</button>
      </div>
    </div>
  </form>
</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>
</body>
</html>
