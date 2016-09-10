<table cellpadding="2" cellspacing="1" width="98%">
    <tr> 
        <td width="120">允许上传的文件类型</td>
        <td><input type="text" name="setting[upload_allowext]" value="gif|jpg|jpeg|png|bmp" size="40" class="input"></td>
    </tr>
    <tr> 
        <td>是否从已上传中选择</td>
        <td><input type="radio" name="setting[isselectimage]" value="1"> 是 <input type="radio" name="setting[isselectimage]" value="0" checked> 否</td>
    </tr>
    <tr> 
        <td>允许同时上传的个数</td>
        <td><input type="text" name="setting[upload_number]" value="10" size=3 class="input"></td>
    </tr>
    <tr> 
        <td>下载统计字段</td>
        <td><input type="text" name="setting[statistics]" value="" class="input"> <span>下载次数统计字段只能在主表！</span></td>
    </tr>
    <tr>
        <td>文件链接方式</td>
        <td>
            <label><input name="setting[downloadlink]" value="0" type="radio"> 链接到真实软件地址 （无法进行验证和统计）</label>
            <label><input name="setting[downloadlink]" value="1" checked="checked" type="radio"> 链接到下载跳转页面</label>
        </td>
    </tr>		
</table>