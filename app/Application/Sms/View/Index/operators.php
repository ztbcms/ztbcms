<?php if (!defined('CMS_VERSION')) exit(); ?>
<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">平台配置</div>
  <form action="{:U('Index/addOperator')}" method="post">
  <div class="table_full">
  <table class="table_form hidden" width="100%" cellspacing="0" id="operators">
  <tbody>
    <tr>
        <th>平台名称</th>
        <th>表名</th>
        <th>描述</th>
        <th>状态</th>
        <th>操作</th>
    </tr>
    <tr v-for="operator in operators" :operator="operator">
        <td>{{ operator.name }}</td>
        <td>{{ operator.tablename }}</td>
        <td>{{ operator.remark }}</td>
        <td v-if="1 == operator.enable">默认平台</td>
        <td v-else>
            <a v-if="0 == operator.enable" @click="choose" :data-operator="operator.tablename" href="javascript:">设为默认</a>
        </td>
        <td>
            <a @click="model" :data-operator="operator.tablename" href="javascript:">模型管理</a> |
            <a @click="conf" :data-operator="operator.tablename" href="javascript:">短信模版管理</a> |
            <a @click="del" :data-operator="operator.tablename" href="javascript:">删除平台</a>
        </td>
    </tr>
	</tbody>
    </table>
  </div>
  </form>
</div>


<script src="//cdn.bootcss.com/vue/2.1.5/vue.min.js"></script>
<script>
    $.get("{:U('Sms/Index/get_operators')}",null,function(data){
        if (data.status){
            new Vue({
                el:"#operators",
                data:data.datas,
                methods:{
                    choose:function(e){
                        window.location.href = "{:U('Index/choose')}&operator=" + $(e.toElement).data('operator');
                    },

                    model:function(e){
                        window.location.href = "{:U('Index/model')}&operator=" + $(e.toElement).data('operator');
                    },

                    conf:function(e){
                        window.location.href = "{:U('Index/modules')}&operator=" + $(e.toElement).data('operator');
                    },

                    //删除平台操作
                    del:function(e){
                        var r = confirm("确定删除此平台吗？");
                        if(r){
                            var url = "{:U('Index/operator_del')}";
                            var data = {
                                operator: $(e.toElement).data('operator')
                            }
                            $.post(url,data,function(data){
                                if (data.status){
                                    window.location.reload();
                                }else{
                                    alert(data.error)
                                }
                            },'json');
                        }
                    },
                },
                mounted: function(){
                    var vm = this;
                    $(vm.$options.el).removeClass('hidden');
                }

            })
        }else{
            $('.table_full').text(data.error);
        }
    },'json');
</script>
</body>
</html>
