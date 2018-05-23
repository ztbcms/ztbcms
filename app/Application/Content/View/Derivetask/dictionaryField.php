<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap J_check_wrap">
  <Admintemplate file="Common/Nav"/>
  <div class="h_a">数据字典 <a style="margin-left: 20px;" href="{:U('Models/index')}" class="btn btn-success">返回列表</a></div>

    <div class="table_full">
      <table width="100%"  class="table_form">
          <!--  栏目模板选择 -->
        <tr>
            <style type="text/css">
                .y-bg p{
                    margin: 0 0 0 0px;
                }
            </style>
          <th  class="y-bg">
            <volist name="data" id="vo">
              <div style=" float:left;width: 600px;">
                  <span style="color: #1ab7ea; float: left;">## {$vo.0.tablename.tablename}&nbsp&nbsp</span>
                  <span style="color: tomato; float: left; ">{$vo.0.tablename.name}</span>
              </div>
              <div style="width: 600px;">

                  <div style="float: left;">
                      <div  style="float: left; width: 100px;">
                          <span>|&nbsp字段名&nbsp</span>
                      </div>
                      <div style="float: left; width: 100px;">
                          <span>|&nbsp字段别名&nbsp</span>
                      </div>
                      <div style="float: left; width: 100px;">
                          <span>|&nbsp类型&nbsp</span>
                      </div>
                      <div style="float: left; width: 100px;">
                          <span>|&nbsp说明&nbsp</span>
                      </div>
                  </p>
                  </div>

                  <div style="float: left;">
                      <p>
                      <div  style="float: left; width: 100px;">
                          <span>|:--- </span>
                      </div>
                      <div style="float: left; width: 100px;">
                          <span> |:---</span>
                      </div>
                      <div style="float: left; width: 100px;">
                          <span> |:---</span>
                      </div>
                      <div style="float: left; width: 100px;">
                          <span> |:---  |</span>
                      </div>
                      </p>
                  </div>
                      <volist name="vo" id="foo" >
                          <p>
                      <div style="float: left; margin-right: 50px;">
                          <div  style="float: left; width: 100px;">
                              <span>|&nbsp{$foo.field}</span>
                          </div>
                          <div style="float: left; width: 100px;">
                              <span>|&nbsp{$foo.name}</span>
                          </div>
                          <div style="float: left; width: 100px;">
                              <span>|&nbsp{$foo.formtype}</span>
                          </div>
                          <div style="float: left; width: 100px;">
                              <span>|&nbsp{$foo.tips}&nbsp|</span>
                          </div>
                      </div>
                          </p>
                      </volist>
                  </volist>
              </div>

          </th>
        </tr>

    </table>
    </div>

</div>
<script src="{$config_siteurl}statics/js/common.js?v"></script>

<script>
    $(document).ready(function () {
        new Vue({
            el: '.y-bg',
            data: {
            },
            methods: {
                getList: function () {
                    var that = this;
                    $.ajax({
                        url: '{:U("Controller/derivetask/ajaxdictionaryField")}',
                        type: 'get',
                        dataType: 'json',
                        success: function (res) {
                            console.log(res);
                        }
                    });
                },
            }
        })
    });
</script>
</body>
</html>
