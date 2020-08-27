<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="16" :md="12">
                <!--                插入template 文件-->
                <div class="common-form" id="app">
                    <form method="post" class="J_ajaxForm" id="addEditForm">
                        <div class="h_a">菜单信息</div>
                        <div class="table_list">
                            <table cellpadding="0" cellspacing="0" class="table_form" width="100%">
                                <tbody>
                                <tr>
                                    <td width="140">上级:</td>
                                    <td><select name="parentid">
                                            <option value="0">作为一级菜单</option>
                                            {$select_categorys}
                                        </select></td>
                                </tr>
                                <tr>
                                    <td>名称:</td>
                                    <td><input type="text" class="input" name="name" value=""></td>
                                </tr>
                                <tr>
                                    <td>模块:</td>
                                    <td>
                                        <select name="app" id="app" @change="getControllerList()" v-model="module">
                                            <option value="">请选择模块</option>
                                            <option v-for="item in moduleList" :value="item.name">{{item.name}}</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>控制器:</td>
                                    <td>
                                        <select name="controller" id="controller" @change="getActionList()"
                                                v-model="controller">
                                            <option value="%">%</option>
                                            <option v-for="item in controllerList" :value="item">{{item}}</option>
                                        </select>
                                        如果填 % 作为权限分配的时候就匹配模块下所有控制器
                                    </td>
                                </tr>
                                <tr>
                                    <td>方法:</td>
                                    <td>
                                        <template v-if="actionList.length>0">
                                            <select name="action" id="action" v-model="action">
                                                <option value="%">%</option>
                                                <option v-for="item in actionList" :value="item">{{item}}</option>
                                            </select>
                                        </template>
                                        <template v-else>
                                            <input class="input input-action" name="action" v-model="action" type="text"
                                                   placeholder="">
                                        </template>
                                        如果填 % 作为权限分配的时候就匹配控制器下所有的方法
                                    </td>
                                </tr>
                                <tr>
                                    <td>参数:</td>
                                    <td><input type="text" class="input length_5 input-params" name="parameter"
                                               value="">
                                        例:groupid=1&amp;type=2
                                    </td>
                                </tr>
                                <tr>
                                    <td>备注:</td>
                                    <td><textarea name="remark" rows="5" cols="57">{$remark}</textarea></td>
                                </tr>
                                <tr>
                                    <td>状态:</td>
                                    <td><select name="status">
                                            <option value="1"
                                            <eq name="status" value="1">selected</eq>
                                            >显示</option>
                                            <option value="0"
                                            <eq name="status" value="0">selected</eq>
                                            >不显示</option>
                                        </select>需要明显不确定的操作时建议设置为不显示，例如：删除，修改等
                                    </td>
                                </tr>
                                <tr>
                                    <td>类型:</td>
                                    <td><select name="type">
                                            <option value="1" selected>权限认证+菜单</option>
                                            <option value="0">只作为菜单</option>
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
        var vm = new Vue({
            el: '#app',
            data: {
                isTp6: 0,
                module: '',
                moduleList: {},
                controller: '%',
                controllerList: {},
                action: '%',
                actionList: {},
            },
            methods: {
                getModuleList: function () {
                    $.post('Admin/Menu/public_getModule', {}, function (res) {
                        vm.moduleList = res.data;
                    }, 'json');
                },
                getControllerList: function () {
                    console.log('e', vm.module);
                    let moduleItem = this.moduleList.find(res => {
                        return res.name == vm.module;
                    });
                    if (moduleItem) {
                        console.log('moduleItem', moduleItem);
                        vm.isTp6 = moduleItem.is_tp6;
                    }
                    $.post('Admin/Menu/public_getController', {module: vm.module, is_tp6: vm.isTp6}, function (res) {
                        vm.controllerList = res.data;
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
                    if (parseInt(that.isTp6) === 1) {
                        data += '&is_tp6=1';
                    } else {
                        data += '&is_tp6=0';
                    }
                    $.ajax({
                        url: "{:U('Menu/add')}",
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
    </script>

    <link href="/statics/css/admin_style.css" rel="stylesheet"/>
    <style>
        .input-action {
            height: 20px;
            width: 100px;
        }

        .input-params {
            height: 20px;
            width: 250px;
        }
    </style>
</block>