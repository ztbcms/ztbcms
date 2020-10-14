<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <el-col :sm="24" :md="18">
            <template>
                <div>
                    <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="100px">

                        <h3> 权限组 </h3>

                        <el-table
                                :data="getAccessList"
                                highlight-current-row
                                style="width: 100%;"
                                @selection-change="handleSelectionChange"
                        >

                            <el-table-column type="selection" width="55" align="center">

                            </el-table-column>

                            <el-table-column label="ID" align="center">
                                <template slot-scope="scope">
                                    <span>{{ scope.row.id }}</span>
                                </template>
                            </el-table-column>

                            <el-table-column label="名称" align="">
                                <template slot-scope="scope">
                                    <template v-for="i in scope.row.level * 4"><span>&nbsp;</span></template>
                                    <span>|— {{ scope.row.name }}  ({{ scope.row.app }}/{{ scope.row.controller }}/{{ scope.row.action }})</span>
                                </template>
                            </el-table-column>

                        </el-table>

                        <el-form-item size="large" style="margin-top: 15px;">
                            <el-button type="primary" @click="submitForm">确认选择</el-button>
                        </el-form-item>

                    </el-form>
                </div>
            </template>
        </el-col>
    </el-card>
</div>

<script>
    $(document).ready(function () {
        window.App = new Vue({
            el: '#app',
            // 插入export default里面的内容
            components: {},
            props: [],
            data() {
                return {
                    formData: {},
                    rules: {},
                    getAccessList: [],
                    initSelectIds: [],
                    multipleSelectionFlag:false,
                    multiDeleteVisible:false,
                    multipleSelection:''
                }
            },
            computed: {

            },
            watch: {},
            created: function() {
            },
            mounted: function() {
                this.fetchData();
            },
            methods: {
                handleSelectionChange: function(val) {
                    this.multipleSelection = val;
                    this.multipleSelectionFlag = true;
                    if(this.multipleSelection.length == 0) {
                        this.multipleSelectionFlag = false;
                    }
                },
                fetchData: function(){
                    var that = this;
                    $.ajax({
                        url: "{:api_url('/admin/AccessGroup/getAccessList')}",
                        type: "get",
                        dataType: "json",
                        success: function(res){
                            if(res.status){
                                res.data.forEach(function(item,index){
                                    res.data[index]['checked'] = that.initSelectIds.indexOf('' + item['id']) !== -1;
                                });
                                that.getAccessList = res.data;
                                that.initSelectIds = [];
                            }else{
                                layer.msg('操作繁忙，请稍后再试')
                            }
                        }
                    })
                },
                submitForm : function () {

                    if(parent.window.selectAccessListCallback){
                        parent.window.selectAccessListCallback(this.multipleSelection)
                    }
                    parent.window.layer.closeAll();
                }
            }
        });
    });
</script>

<style>

</style>
