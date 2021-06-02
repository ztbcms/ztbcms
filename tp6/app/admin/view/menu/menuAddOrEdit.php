<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="9">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="100px">
                        <el-form-item label="上级" prop="parentid">
                            <el-select v-model="formData.parentid" placeholder="请选择上级" clearable :style="{width: '100%'}">
                                <el-option :label="topOption.label" :value="topOption.value"></el-option>
                                <el-option v-for="(item, index) in parentidOptions" :key="index"
                                           :value="item.id" :label="item.name">
                                    <template v-for="i in item.level * 3"><span>&nbsp;</span></template>
                                    <template v-if="item.level > 0"><span> ∟</span></template>
                                    <span>{{ item.name }}</span>
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="名称" prop="name">
                            <el-input v-model="formData.name" placeholder="请输入名称" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="应用" prop="app">
                            <el-input v-model="formData.app" placeholder="请输入应用,不限制请填写%" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="控制器" prop="controller">
                            <el-input v-model="formData.controller" placeholder="请输入控制器,不限制请填写%" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="方法" prop="action">
                            <el-input v-model="formData.action" placeholder="请输入方法,不限制请填写%" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="参数" prop="parameter">
                            <el-input v-model="formData.parameter" placeholder="请输入参数" clearable :style="{width: '100%'}">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="备注" prop="remark">
                            <el-input v-model="formData.remark" placeholder="请输入备注" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>

                        <el-form-item label="菜单显示" prop="status">
                            <el-select v-model="formData.status" placeholder="请选择状态" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in statusOptions" :key="index" :label="item.label" :value="item.value" :disabled="item.disabled"></el-option>
                            </el-select>
                            <el-alert style="margin-top: 4px;" type="success" :closable="false">
                                设置是否在后台左侧菜单栏展示。如果是在页面内的操作（如删除、审核等操作），可设置为不展示
                            </el-alert>
                        </el-form-item>

                        <el-form-item label="验证权限" prop="type">
                            <el-select v-model="formData.type" placeholder="请选择" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in  typeOptions" :key="index" :label="item.label"
                                           :value="item.value" :disabled="item.disabled"></el-option>
                            </el-select>
                            <el-alert style="margin-top: 4px;" type="success" :closable="false">
                                所有的菜单我们都建议需验证权限,99.9%情况下,你使用默认值『需验证』即可
                            </el-alert>
                        </el-form-item>

                        <el-form-item label="图标" prop="icon">
                            <el-input v-model="formData.icon" placeholder="请输入图标名称，如icon-check" clearable :style="{width: '100%'}">
                                <template slot="prepend">
                                    <i class="iconfont" :class="[formData.icon]"></i>
                                </template>
                                <template slot="append">
                                    <el-button type="primary" @click="toSelectIcon">点击选择</el-button>
                                </template>
                            </el-input>
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
            data: function() {
                return {
                    formData: {
                        id : "",
                        parentid: "",
                        name: '',
                        app: '',
                        controller: '',
                        action: '',
                        parameter: '',
                        remark: '',
                        status: '1',
                        type: '1',
                        icon: '',
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
                            message: '请填写模块',
                            trigger: 'change'
                        }],
                        controller: [{
                            required: true,
                            message: '请填写控制器',
                            trigger: 'change'
                        }],
                        action: [{
                            required: true,
                            message: '请填写方法',
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
                        "label": "需验证",
                        "value": '1'
                    }, {
                        "label": "不需验证",
                        "value": '0'
                    }],
                    topOption: {
                        label: '作为一级菜单',
                        value: 0
                    }
                }
            },
            computed: {},
            watch: {},
            created: function() {},
            mounted: function() {
                this.getMenuList();
                this.formData.id = this.getUrlQuery('id')
                if(this.formData.id) {
                    this.getDetail();
                }
                this.formData.parentid = this.getUrlQuery('parentid')
                if(this.formData.parentid){
                    this.formData.parentid = parseInt(this.formData.parentid)
                }
                // 注册回调
                window.addEventListener('ZTBCMS_SELECT_ICON', this.handleSelectIconCallback.bind(this))
            },
            methods: {
                submitForm: function() {
                    var that = this;

                    that.$refs['elForm'].validate(function(valid){
                        if (!valid) return;
                        var url = "{:api_url('/admin/Menu/menuAdd')}"
                        if (that.formData.id) {
                            url = "{:api_url('/admin/Menu/menuEdit')}"
                        }
                        that.httpPost(url, that.formData, function(res){
                            layer.msg(res.msg);
                            if (res.status) {
                                //添加成功
                                setTimeout(function(){
                                    if(window !== parent.window) parent.window.layer.closeAll();
                                }, 1000)
                            }
                        })
                    })
                },
                //获取菜单列表
                getMenuList: function() {
                    var that = this;
                    var url = "{:api_url('/admin/Menu/menuAdd')}"
                    if (that.formData.id) {
                        url = "{:api_url('/admin/Menu/menuEdit')}"
                    }
                    var data = {
                        '_action': 'getMenuList'
                    }
                    this.httpGet(url, data, function (res) {
                        if (res.status) {
                            that.parentidOptions = res.data;
                        }
                    })
                },
                //获取菜单详情
                getDetail: function (){
                    var that = this;
                    var url = "{:api_url('/admin/Menu/menuEdit')}"
                    if (!that.formData.id) {
                        return
                    }
                    var data = {
                        id: that.formData.id,
                        '_action': 'getDetail'
                    }
                    this.httpGet(url, data, function (res) {
                        that.formData = res.data;
                        that.formData.parentid = parseInt(that.formData.parentid) || 0
                    })
                },
                // 去选择icon
                toSelectIcon: function(){
                    layer.open({
                        type: 2,
                        title: '选择',
                        content: "{:api_url('/admin/Iconfont/index')}",
                        area: ['80%', '90%'],
                    })
                },
                // 选择icon回调
                handleSelectIconCallback: function(res){
                    this.formData.icon = res.detail.icon
                }
            }
        });
    });
</script>

<style>

</style>
