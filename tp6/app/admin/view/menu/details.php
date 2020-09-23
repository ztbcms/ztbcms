<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="100px">
                        <el-form-item label="上级" prop="parentid">
                            <el-select v-model="formData.parentid" placeholder="请选择上级" clearable :style="{width: '100%'}">

                                <el-option label="作为一级菜单" value="0"></el-option>
                                <el-option v-for="(item, index) in parentidOptions" :key="index" :label="item.name"
                                           :value="item.id" ></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="名称" prop="name">
                            <el-input v-model="formData.name" placeholder="请输入名称" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="模块" prop="app">
                            <el-select @change="getControllerList()" v-model="formData.app" placeholder="请选择模块" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in appOptions" :key="index" :label="item.name" :value="item.name"
                                           :disabled="item.disabled"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="控制器" prop="controller">
                            <el-select @change="getActionList()" v-model="formData.controller" placeholder="请选择控制器" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in controllerOptions" :key="index" :label="item"
                                           :value="item" ></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="方法" prop="action">
                            <el-select v-model="formData.action" placeholder="请输入方法" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in actionOptions" :key="index" :label="item"
                                           :value="item" ></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="参数" prop="parameter">
                            <el-input v-model="formData.parameter" placeholder="请输入参数" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="备注" prop="remark">
                            <el-input v-model="formData.remark" placeholder="请输入备注" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="状态" prop="status">
                            <el-select v-model="formData.status" placeholder="请选择状态" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in statusOptions" :key="index" :label="item.label"
                                           :value="item.value" :disabled="item.disabled"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="类型" prop=" type">
                            <el-select v-model="formData. type" placeholder="请选择类型" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in  typeOptions" :key="index" :label="item.label"
                                           :value="item.value" :disabled="item.disabled"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="图标" prop="icon">
                            <el-input v-model="formData.icon" placeholder="请输入图标" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>
                        <el-form-item size="large">
                            <el-button type="primary" @click="submitForm">提交</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </template>
        </el-col>
    </el-card>
</div>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            // 插入export default里面的内容
            components: {},
            props: [],
            data() {
                return {
                    formData: {
                        id : "{$id}",
                        parentid: "{$parentid}" * 1,
                        name: '',
                        app: '',
                        controller: '%',
                        action: '%',
                        parameter: '',
                        remark: '',
                        status: '1',
                        type: '1',
                        icon: '',
                        is_tp6: 0,
                    },
                    rules: {
                        parentid: [{
                            required: true,
                            message: '请选择上级',
                            trigger: 'change'
                        }],
                        name: [{
                            required: true,
                            message: '请输入名称',
                            trigger: 'blur'
                        }],
                        app: [{
                            required: true,
                            message: '请选择模块',
                            trigger: 'change'
                        }],
                        controller: [{
                            required: true,
                            message: '请选择控制器',
                            trigger: 'change'
                        }],
                        action: [{
                            required: true,
                            message: '请输入方法',
                            trigger: 'change'
                        }],
                        parameter: [],
                        remark: [],
                        status: [],
                        type: [{
                            required: true,
                            message: '请选择类型',
                            trigger: 'change'
                        }],
                    },
                    parentidOptions: [],
                    appOptions: [],
                    controllerOptions: [],
                    actionOptions: [],
                    statusOptions: [{
                        "label": "显示",
                        "value": '1'
                    }, {
                        "label": "不显示",
                        "value": '0'
                    }],
                    typeOptions: [{
                        "label": "权限认证+菜单",
                        "value": '1'
                    }, {
                        "label": "只作为菜单",
                        "value": '0'
                    }],
                }
            },
            computed: {},
            watch: {},
            created() {},
            mounted() {
                this.getMenuList();
                this.getModuleList();
                if(this.formData.id) this.getDetails();
            },
            methods: {
                submitForm() {
                    var that = this;

                    that.$refs['elForm'].validate(valid => {
                        if (!valid) return;
                        // TODO 提交表单
                        $.ajax({
                            url: "{:api_url('/Admin/Menu/addEditDetails')}",
                            data: that.formData,
                            type: "post",
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    //添加成功
                                    layer.msg(res.msg);
                                    setTimeout(function(){
                                        parent.window.layer.closeAll();
                                    }, 1000)
                                } else {
                                    layer.msg(res.msg)
                                }
                            }
                        })

                    })
                },
                //获取菜单列表
                getMenuList() {
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/Admin/Menu/getMenuList')}",
                        type: "get",
                        dataType: "json",
                        data: that.listQuery,
                        success: function (res) {
                            if (res.status) {
                                that.parentidOptions = res.data;
                            }
                        }
                    })
                },
                //获取模块列表
                getModuleList: function () {
                    var that = this;
                    $.post("{:api_url('/Admin/Menu/getModuleList')}", {}, function (res) {
                        that.appOptions = res.data;
                    }, 'json');
                },
                //获取控制器列表
                getControllerList: function () {
                    var that = this;

                    let appItem = that.appOptions.find(res => {
                        return res.name == that.formData.app;
                    });

                    if (appItem) {
                        that.formData.is_tp6 = appItem.is_tp6;
                    }

                    $.post("{:api_url('/Admin/Menu/getControllerList')}", {
                        module: that.formData.app,
                        is_tp6: that.formData.is_tp6
                    }, function (res) {
                        that.controllerOptions = res.data;
                    }, 'json');
                },
                //获取方法列表
                getActionList: function () {
                    var that = this;
                    $.post("{:api_url('Admin/Menu/getActionList')}", {
                        controller: that.formData.controller,
                        app : that.formData.app,
                        is_tp6: that.formData.is_tp6
                    }, function (res) {
                        that.actionOptions = res.data;
                    }, 'json');
                },
                //获取菜单详情
                getDetails: function (){
                    var that = this;
                    $.post("{:api_url('Admin/Menu/getDetails')}", {
                        id: that.formData.id,
                    }, function (res) {
                        that.formData = res.data;
                    }, 'json');
                }
            }
        });
    });
</script>

<style>

</style>
