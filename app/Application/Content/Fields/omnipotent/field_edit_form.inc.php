<table cellpadding="2" cellspacing="1" width="98%">
    <tr> 
        <td width="50">表单</td>
        <td><textarea name="setting[formtext]" rows="2" cols="20" id="options" style="height:100px;width:99%;"><?php echo htmlspecialchars($setting['formtext']); ?></textarea>
            <br/>例如：&lt;input type='text' name='info[<font style="color: #F00">当前字段名</font>]' id='voteid' value='<font style="color: #F00">{FIELD_VALUE}</font>' style='50' &gt;
            <br/><font style="color: #F00">{FIELD_VALUE}</font> 当前万能字段的值，<font style="color: #F00">{MODELID}</font> 当前模型ID，<font style="color: #F00">{ID}</font>当前信息ID，添加时为0。
            <br/>除了以上特定标签外，可以直接使用 “<font style="color: #F00"><b>$</b>字段名</font>”的方式，获取其他字段的值。
            <br/>在“表单”里可以直接使用<font style="color: #F00">php语法</font>或者<font style="color: #F00">模板标签</font>。
            <br/>提示：在这里，你可以把表单需要的任何效果，做成HTML+JS甚至是配合php来实现~
            <br/><font style="color: #F00">如果要保存数组类的值，请字段类型选择“text，mediumtext，longtext”</font>
        </td>
    </tr>
    <tr> 
        <td>字段类型</td>
        <td>
            <select name="setting[fieldtype]">
                <option value="varchar" <?php if ($setting['fieldtype'] == 'varchar') echo 'selected'; ?>>字符型0-255字节(VARCHAR)</option>
                <option value="char" <?php if ($setting['fieldtype'] == 'char') echo 'selected'; ?>>定长字符型0-255字节(CHAR)</option>
                <option value="text" <?php if ($setting['fieldtype'] == 'text') echo 'selected'; ?>>小型字符型(TEXT)</option>
                <option value="mediumtext" <?php if ($setting['fieldtype'] == 'mediumtext') echo 'selected'; ?>>中型字符型(MEDIUMTEXT)</option>
                <option value="longtext" <?php if ($setting['fieldtype'] == 'longtext') echo 'selected'; ?>>大型字符型(LONGTEXT)</option>
                <option value="tinyint" <?php if ($setting['fieldtype'] == 'tinyint') echo 'selected'; ?>>整数 TINYINT(3)</option>
                <option value="smallint" <?php if ($setting['fieldtype'] == 'smallint') echo 'selected'; ?>>整数 SMALLINT(5)</option>
                <option value="mediumint" <?php if ($setting['fieldtype'] == 'mediumint') echo 'selected'; ?>>整数 MEDIUMINT(8)</option>
                <option value="int" <?php if ($setting['fieldtype'] == 'int') echo 'selected'; ?>>整数 INT(10)</option>
                <option value="bigint" <?php if ($setting['fieldtype'] == 'bigint') echo 'selected'; ?>>超大数值型(BIGINT)</option>
                <option value="float" <?php if ($setting['fieldtype'] == 'float') echo 'selected'; ?>>数值浮点型(FLOAT)</option>
                <option value="double" <?php if ($setting['fieldtype'] == 'double') echo 'selected'; ?>>数值双精度型(DOUBLE)</option>
                <option value="date" <?php if ($setting['fieldtype'] == 'date') echo 'selected'; ?>>日期型(DATE)</option>
                <option value="datetime" <?php if ($setting['fieldtype'] == 'datetime') echo 'selected'; ?>>日期时间型(DATETIME)</option>
            </select> <span id="minnumber">数字类型：<input type="radio" name="setting[minnumber]" value="1" <?php if ($setting['minnumber'] == 1) echo 'checked'; ?>/> <font color='red'>正整数</font> <input type="radio" name="setting[minnumber]" value="-1" <?php if ($setting['minnumber'] == -1) echo 'checked'; ?>/> 整数</span>
        </td>
    </tr>
</table>