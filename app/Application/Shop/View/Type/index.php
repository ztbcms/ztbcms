<include file="Public/min-header"/>
<div class="wrapper">
  <include file="Public/breadcrumb"/>
  <!-- Main content -->
  <section class="content">
    <div class="container-fluid">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title"><i class="fa fa-list"></i> 商品类型列表</h3>
        </div>
        <div class="panel-body">    
		<div class="navbar navbar-default">
            <div class="row navbar-form">
                <button type="submit" onclick="location.href='{:U('Type/addEditGoodsType')}'"  class="btn btn-primary pull-right"><i class="fa fa-plus"></i>新增商品类型</button>
            </div>
          </div>
                        
          <div id="ajax_return"> 
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th class="sorting text-center">ID</th>                                
                                <th class="sorting text-center">类型名</th>
                                <th class="sorting text-center">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <volist name="goodsTypeList" id="list">
                                <tr>
                                    <td class="text-center">{$list.id}</td>
                                    <td class="text-center">{$list.name}</td>
                                    <td class="text-center">
										<a href="{:U('Spec/index',array('type_id'=>$list['id']))}" data-toggle="tooltip" title="" class="btn btn-info" data-original-title="属性列表"><i class="fa fa-eye"></i></a>                                    
                                        <a href="{:U('Type/addEditGoodsType',array('id'=>$list['id']))}" data-toggle="tooltip" title="" class="btn btn-primary" data-original-title="编辑"><i class="fa fa-pencil"></i></a>
                                        <a href="javascript:del_fun('{:U('Type/delGoodsType',array('id'=>$list['id']))}');" id="button-delete6" data-toggle="tooltip" title="" class="btn btn-danger" data-original-title="删除"><i class="fa fa-trash-o"></i></a>
                                    </td>
                                </tr>
                            </volist>
                            </tbody>
                        </table>
                    </div>
                
                <div class="row">
                    <div class="col-sm-6 text-left"></div>
                    <div class="col-sm-6 text-right">{$show}</div>
                </div>
          
          </div>
        </div>
      </div>
    </div>
    <!-- /.row --> 
  </section>
  <!-- /.content --> 
</div>
<!-- /.content-wrapper --> 
</body>
</html>