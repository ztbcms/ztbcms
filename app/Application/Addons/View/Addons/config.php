<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">{$meta_title}</div>
  <form method="post" class="J_ajaxForm" action="{:U('Addons/config')}">
  <div class="table_full">
  <table width="100%">
  <col class="th" />
  <col width="500" />
  <col />
  <foreach name="config" item="vo" key="o_key">
    <switch name="vo.type">
        <case value="text">
        <tr>
      		<th>{$vo.title}</th>
      		<td>
          <div class="controls">
            <input type="text" name="config[{$o_key}]" class="text input" value="{$vo.value}" style="{$vo.style}">
          </div>
        	</td>
    		<td><div class="fun_tips">{$vo.tips}</div></td>
    	</tr>
        </case>
        <case value="password">
        <tr>
      		<th>{$vo.title}</th>
      		<td>
          <div class="controls">
            <input type="password" name="config[{$o_key}]" class="text input" value="{$vo.value}" style="{$vo.style}">
          </div>
        	</td>
    		<td><div class="fun_tips">{$vo.tips}</div></td>
    	</tr>
        </case>
        <case value="hidden">
        <tr style="display:none">
      		<th>{$vo.title}</th>
      		<td>
          <input type="hidden" name="config[{$o_key}]" value="{$vo.value}">
        	</td>
    		<td><div class="fun_tips">{$vo.tips}</div></td>
    	</tr>
        </case>
        <case value="radio">
        <tr>
      		<th>{$vo.title}</th>
      		<td>
          <foreach name="vo.options" item="opt" key="opt_k">
            <label class="radio"> <input type="radio" name="config[{$o_key}]" value="{$opt_k}" <eq name="vo.value" value="$opt_k"> checked</eq>>{$opt} </label>
          </foreach>
        	</td>
    		<td><div class="fun_tips">{$vo.tips}</div></td>
    	</tr>
        </case>
        <case value="checkbox">
        <tr>
      		<th>{$vo.title}</th>
      		<td>
          <foreach name="vo.options" item="opt" key="opt_k">
            <label class="checkbox"> <input type="checkbox" name="config[{$o_key}][]" value="{$opt_k}" <if condition=" in_array($opt_k,$vo['value']) "> checked</if>>{$opt} </label>
          </foreach>
        	</td>
    		<td><div class="fun_tips">{$vo.tips}</div></td>
    	</tr>
        </case>
        <case value="select">
        <tr>
      		<th>{$vo.title}</th>
      		<td>
          <select name="config[{$o_key}]" style="{$vo.style}">
            <foreach name="vo.options" item="opt" key="opt_k"> 
            <option value="{$opt_k}" <eq name="vo.value" value="$opt_k"> selected</eq>>{$opt}</option>
            </foreach>
          </select>
        	</td>
    		<td><div class="fun_tips">{$vo.tips}</div></td>
    	</tr>
        </case>
        <case value="textarea">
        <tr>
      		<th>{$vo.title}</th>
      		<td>
          <label class="textarea">
            <textarea name="config[{$o_key}]" style="{$vo.style}">{$vo.value}</textarea>
          </label>
        	</td>
    		<td><div class="fun_tips">{$vo.tips}</div></td>
    	</tr>
        </case>
        <case value="file">
        <tr>
      		<th>{$vo.title}</th>
      		<td>
          <?php echo \Form::upfiles("config[".$o_key."]",$o_key,$vo['value'],MODULE_NAME,'',50,'input','',$vo['alowexts']); ?>
        	</td>
    		<td><div class="fun_tips">{$vo.tips}</div></td>
    	</tr>
        </case>
        <case value="editor">
        <tr>
      		<th>{$vo.title}</th>
      		<td>
          <script type="text/plain" id="{$o_key}" name="config[{$o_key}]">{$vo.value}</script>
		  <?php echo \Form::editor($o_key,$vo['toolbar'],MODULE_NAME); ?>
        	</td>
    		<td><div class="fun_tips">{$vo.tips}</div></td>
    	</tr>
        </case>
        <case value="group">
			  <?php
              $memberGroup = cache('Member_group');
              ?>
              <switch name="vo.showtype">
                <case value="radio">
                <tr>
                    <th>{$vo.title}</th>
                    <td>
                  <volist name="memberGroup" id="mgro">
                    <label class="radio"> <input type="radio" name="config[{$o_key}]" value="{$mgro.groupid}" <eq name="vo.value" value="$mgro.groupid"> checked</eq>>{$mgro.name} </label>
                  </volist>
                    </td>
                    <td><div class="fun_tips">{$vo.tips}</div></td>
                </tr>
                </case>
                <case value="checkbox">
                <tr>
                    <th>{$vo.title}</th>
                    <td>
                  <volist name="memberGroup" id="mgro">
                    <label class="checkbox"> <input type="checkbox" name="config[{$o_key}][]" value="{$mgro.groupid}" <if condition=" in_array($mgro['groupid'],$vo['value']) "> checked</if>>{$mgro.name} </label>
                  </volist>
                    </td>
                    <td><div class="fun_tips">{$vo.tips}</div></td>
                </tr>
                </case>
                <case value="select">
                <tr>
                    <th>{$vo.title}</th>
                    <td>
                  <select name="config[{$o_key}]" style="{$vo.style}">
                    <volist name="memberGroup" id="mgro">
                    <option value="{$opt_k}" <eq name="vo.value" value="$mgro.groupid"> selected</eq>>{$mgro.name}</option>
                    </volist>
                  </select>
                    </td>
                    <td><div class="fun_tips">{$vo.tips}</div></td>
                </tr>
                </case>
              </switch>
        </case>
    </switch>
  </foreach>
  </table>
</div>
<div class="btn_wrap">
  <div class="btn_wrap_pd">
    <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">提交</button>
  </div>
</div>
<input type="hidden" name="id" value="{$info.id}" />
</form>
</div>
<script type="text/javascript" src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>