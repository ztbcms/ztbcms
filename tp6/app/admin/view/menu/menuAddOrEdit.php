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
                                    <template v-for="i in item.level * 2"><span>&nbsp;</span></template>
                                    <template v-if="item.level > 0"><span> ∟</span></template>
                                    <span>{{ item.name }}</span>
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="名称" prop="name">
                            <el-input v-model="formData.name" placeholder="请输入名称" clearable :style="{width: '100%'}"></el-input>
                        </el-form-item>
                        <el-form-item label="模块" prop="app">
                            <el-input v-model="formData.app" placeholder="请输入模块,不限制请填写%" clearable :style="{width: '100%'}"></el-input>
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

                        <el-form-item label="类型" prop="type">
                            <el-select v-model="formData.type" placeholder="请选择类型" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in  typeOptions" :key="index" :label="item.label"
                                           :value="item.value" :disabled="item.disabled"></el-option>
                            </el-select>
                            <el-alert title="菜单组：菜单的集合；权限菜单：需要进行权限校验的菜单"
                                    type="info"
                                    :closable="false">
                            </el-alert>
                        </el-form-item>
                        <el-form-item label="状态" prop="status">
                            <el-select v-model="formData.status" placeholder="请选择状态" clearable :style="{width: '100%'}">
                                <el-option v-for="(item, index) in statusOptions" :key="index" :label="item.label"
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
                        is_tp6: '1',
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
                        "label": "权限菜单",
                        "value": '1'
                    }, {
                        "label": "菜单组",
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
                }
            }
        });
    });
</script>

<style>

</style>
