<table cellpadding="2" cellspacing="1" width="98%">
    <tr> 
        <td width="50">表单</td>
        <td><textarea name="setting[formtext]" rows="2" cols="20" id="options" style="height:100px;width:99%;"></textarea>
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
                <option value="varchar">字符型0-255字节(VARCHAR)</option>
                <option value="char">定长字符型0-255字节(CHAR)</option>
                <option value="text">小型字符型(TEXT)</option>
                <option value="mediumtext">中型字符型(MEDIUMTEXT)</option>
                <option value="longtext">大型字符型(LONGTEXT)</option>
                <option value="tinyint">整数 TINYINT(3)</option>
                <option value="smallint">整数 SMALLINT(5)</option>
                <option value="mediumint">整数 MEDIUMINT(8)</option>
                <option value="int">整数 INT(10)</option>
                <option value="bigint">超大数值型(BIGINT)</option>
                <option value="float">数值浮点型(FLOAT)</option>
                <option value="double">数值双精度型(DOUBLE)</option>
                <option value="date">日期型(DATE)</option>
                <option value="datetime">日期时间型(DATETIME)</option>
            </select> <span id="minnumber">数字类型：<input type="radio" name="setting[minnumber]" value="1" checked/> <font color='red'>正整数</font> <input type="radio" name="setting[minnumber]" value="-1" /> 整数</span>
        </td>
    </tr>
</table>