<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="16" :md="12" >
                <!--                插入template 文件-->
                <div class="common-form" id="app">
                    <form method="post" class="J_ajaxForm" id="addEditForm">
                        <div class="h_a">菜单信息</div>
                        <div class="table_list">
                            <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
                                <input type="hidden" name="id" value="{$id}"/>
                                <tbody>
                                <tr>
                                    <td width="140">上级:</td>
                                    <td>
                                        <select name="parentid">
                                            <option value="0">作为一级菜单</option>
                                            {$select_categorys}
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>名称:</td>
                                    <td><input type="text" class="input" name="name" value="{$data.name}"></td>
                                </tr>
                                <tr>
                                    <td>模块:</td>
                                    <td>
                                        <select name="app" id="app" @change="getControllerList()" v-model="module">
                                            <option value="">请选择模块</option>
                                            <option v-for="item in moduleList" :value="item">{{item}}</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>控制器:</td>
                                    <td>
                                        <select name="controller" id="controller" @change="getActionList()" v-model="controller">
                                            <option value="%">%</option>
                                            <option v-for="item in controllerList" :value="item">{{item}}</option>
                                        </select>
                                        如果填 % 作为权限分配的时候就匹配模块下所有控制器
                                    </td>
                                </tr>
                                <tr>
                                    <td>方法:</td>
                                    <td>
                                        <select name="action" id="action" v-model="action">
                                            <option value="%">%</option>
                                            <option v-for="item in actionList" :value="item">{{item}}</option>
                                        </select>
                                        如果填 % 作为权限分配的时候就匹配控制器下所有的方法
                                    </td>
                                </tr>
                                <tr>
                                    <td>参数:</td>
                                    <td>
                                        <input type="text" class="input length_5" name="parameter" value="{$data.parameter}">
                                        例:groupid=1&amp;type=2
                                    </td>
                                </tr>
                                <tr>
                                    <td>备注:</td>
                                    <td><textarea name="remark" rows="5" cols="57">{$data.remark}</textarea></td>
                                </tr>
                                <tr>
                                    <td>状态:</td>
                                    <td>
                                        <select name="status">
                                            <option value="1"
                                            <eq name="data.status" value="1">selected</eq>
                                            >显示</option>
                                            <option value="0"
                                            <eq name="data.status" value="0">selected</eq>
                                            >不显示</option>
                                        </select>需要明显不确定的操作时建议设置为不显示，例如：删除，修改等
                                    </td>
                                </tr>
                                <tr>
                                    <td>类型:</td>
                                    <td>
                                        <select name="type">
                                            <option value="1"
                                            <eq name="data.type" value="1">selected</eq>
                                            >权限认证+菜单</option>
                                            <option value="0"
                                            <eq name="data.type" value="0">selected</eq>
                                            >只作为菜单</option>
                                        </select>
                                        注意：“权限认证+菜单”表示加入后台权限管理，纯粹是菜单项请不要选择此项。
                                    </td>
                                </tr>
                                <tr>
                                    <td>图标:</td>
                                    <td>
                                        <input type="text" class="input length_4" name="icon" value="{$data.icon}">
                                        仅支持二级菜单，例：icon-icon
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="">
                            <div class="btn_wrap_pd">
<!--                                <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">修改</button>-->
                                <input type="hidden" name="id" value="{$data.id}"/>

                                <button class="btn btn_submit mr10" @click="saveBtn" title="" data-toggle="tooltip"
                                        type="button"
                                        data-original-title="保存">保存
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </el-col>
        </el-card>
    </div>

    <script>
        $(document).ready(function () {
            var vm = new Vue({
                el: '#app',
                data: {
                    module: '{$data["app"]}',
                    moduleList: [],
                    controller: '{$data["controller"]}',
                    controllerList: [],
                    action: '{$data["action"]}',
                    actionList: [],
                },
                methods: {
                    getModuleList: function () {
                        $.post('Admin/Menu/public_getModule', {}, function (res) {
                            vm.moduleList = res.data;
                            vm.getControllerList();
                        }, 'json');
                    },
                    getControllerList: function () {
                        $.post('Admin/Menu/public_getController', {module: vm.module}, function (res) {
                            vm.controllerList = res.data;
                            vm.getActionList();
                        }, 'json');
                    },
                    getActionList: function () {
                        $.post('Admin/Menu/public_getAction', {controller: vm.module + '/' + vm.controller}, function (res) {
                            vm.actionList = res.data;
                        }, 'json');
                    },
                    saveBtn: function () {
                        var that = this;
                        var data = $('#addEditForm').serialize();
                        $.ajax({
                            url: "{:U('Menu/edit')}",
                            data: data,
                            type: "post",
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    //添加成功
                                    layer.msg(res.info);
                                    window.location.reload();
                                } else {
                                    layer.msg(res.info)
                                }
                            }
                        })
                    },
                },
                mounted: function () {
                    this.getModuleList();
                }
            });
        });
    </script>

    <link href="/statics/css/admin_style.css" rel="stylesheet" />

</block>

<Admintemplate file="Common/Head"/>
<body class="J_scroll_fixed">
<div class="wrap jj">
    <Admintemplate file="Common/Nav"/>
    <div class="common-form" id="app">
        <form method="post" class="J_ajaxForm" action="{:U('Menu/edit')}">
            <div class="h_a">菜单信息</div>
            <div class="table_list">
                <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
                    <input type="hidden" name="id" value="{$id}"/>
                    <tbody>
                    <tr>
                        <td width="140">上级:</td>
                        <td>
                            <select name="parentid">
                                <option value="0">作为一级菜单</option>
                                {$select_categorys}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>名称:</td>
                        <td><input type="text" class="input" name="name" value="{$data.name}"></td>
                    </tr>
                    <tr>
                        <td>模块:</td>
                        <td>
                            <select name="app" id="app" @change="getControllerList()" v-model="module">
                                <option value="">请选择模块</option>
                                <option v-for="item in moduleList" :value="item">{{item}}</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>控制器:</td>
                        <td>
                            <select name="controller" id="controller" @change="getActionList()" v-model="controller">
                                <option value="%">%</option>
                                <option v-for="item in controllerList" :value="item">{{item}}</option>
                            </select>
                            如果填 % 作为权限分配的时候就匹配模块下所有控制器
                        </td>
                    </tr>
                    <tr>
                        <td>方法:</td>
                        <td>
                            <select name="action" id="action" v-model="action">
                                <option value="%">%</option>
                                <option v-for="item in actionList" :value="item">{{item}}</option>
                            </select>
                            如果填 % 作为权限分配的时候就匹配控制器下所有的方法
                        </td>
                    </tr>
                    <tr>
                        <td>参数:</td>
                        <td>
                            <input type="text" class="input length_5" name="parameter" value="{$data.parameter}">
                            例:groupid=1&amp;type=2
                        </td>
                    </tr>
                    <tr>
                        <td>备注:</td>
                        <td><textarea name="remark" rows="5" cols="57">{$data.remark}</textarea></td>
                    </tr>
                    <tr>
                        <td>状态:</td>
                        <td>
                            <select name="status">
                                <option value="1"
                                <eq name="data.status" value="1">selected</eq>
                                >显示</option>
                                <option value="0"
                                <eq name="data.status" value="0">selected</eq>
                                >不显示</option>
                            </select>需要明显不确定的操作时建议设置为不显示，例如：删除，修改等
                        </td>
                    </tr>
                    <tr>
                        <td>类型:</td>
                        <td>
                            <select name="type">
                                <option value="1"
                                <eq name="data.type" value="1">selected</eq>
                                >权限认证+菜单</option>
                                <option value="0"
                                <eq name="data.type" value="0">selected</eq>
                                >只作为菜单</option>
                            </select>
                            注意：“权限认证+菜单”表示加入后台权限管理，纯粹是菜单项请不要选择此项。
                        </td>
                    </tr>
                    <tr>
                        <td>图标:</td>
                        <td>
                            <input type="text" class="input length_4" name="icon" value="{$data.icon}">
                            仅支持二级菜单，例：icon-icon
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="">
                <div class="btn_wrap_pd">
                    <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">修改</button>
                    <input type="hidden" name="id" value="{$data.id}"/>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="{$config_siteurl}statics/js/common.js"></script>
<script src="{$config_siteurl}statics/js/vue/vue.js"></script>
<script>
    var vm = new Vue({
        el: '#app',
        data: {
            module: '{$data["app"]}',
            moduleList: [],
            controller: '{$data["controller"]}',
            controllerList: [],
            action: '{$data["action"]}',
            actionList: [],
        },
        methods: {
            getModuleList: function () {
                $.post('Admin/Menu/public_getModule', {}, function (res) {
                    vm.moduleList = res.data;
                    vm.getControllerList();
                }, 'json');
            },
            getControllerList: function () {
                $.post('Admin/Menu/public_getController', {module: vm.module}, function (res) {
                    vm.controllerList = res.data;
                    vm.getActionList();
                }, 'json');
            },
            getActionList: function () {
                $.post('Admin/Menu/public_getAction', {controller: vm.module + '/' + vm.controller}, function (res) {
                    vm.actionList = res.data;
                }, 'json');
            }
        },
        mounted: function () {
            this.getModuleList();
        }
    });
</script>
</body>
</html>
