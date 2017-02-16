 
<Admintemplate file="Common/Head"/>
<body>
<div class="wrap">
	<div class="h_a">缓存更新</div>
	<div class="table_full">
		<table width="100%">
			<col class="th" />
			<col width="400" />
			<col />
			<tr>
				<th>更新站点数据缓存</th>
				<td>
					<a class="btn" href="{:U('Index/cache',array('type'=>'site'))}">提交</a>
				</td>
				<td><div class="fun_tips">修改过站点设置，或者栏目管理，模块安装等时可以进行缓存更新</div></td>
			</tr>
			<tr>
				<th>更新站点模板缓存</th>
				<td>
					<a class="btn" href="{:U('Index/cache',array('type'=>'template'))}">提交</a>
				</td>
				<td><div class="fun_tips">当修改模板时，模板没及时生效可以对模板缓存进行更新</div></td>
			</tr>
            <tr>
				<th>清除网站运行日志</th>
				<td>
					<a class="btn" href="{:U('Index/cache',array('type'=>'logs'))}">提交</a>
				</td>
				<td><div class="fun_tips">网站运行过程中会记录各种错误日志，以文件的方式保存</div></td>
			</tr>
		</table>
	</div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
</body>
</html>
