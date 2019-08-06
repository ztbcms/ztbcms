<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>编辑日志</h3>
            <el-row>
                <el-col :span="8">
                    <div class="grid-content ">
                        <el-form ref="form" :model="form" label-width="80px">
                            <el-form-item label="类别">
                                <el-input v-model="form.category"></el-input>
                            </el-form-item>
                            <el-form-item label="内容">
                                <el-input v-model="form.message"></el-input>
                            </el-form-item>

                            <el-form-item>
                                <el-button type="primary" @click="onSubmit">确认</el-button>
                                <el-button @click="onCancel">取消</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-col>
                <el-col :span="16"><div class="grid-content "></div></el-col>
            </el-row>


        </el-card>
    </div>

    <style>

    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    form: {
                        category: '',
                        message: '',
                    }
                },
                watch: {},
                filters: {},
                methods: {
                    onSubmit: function(){
                        var that = this;
                        $.ajax({
                            url: '{:U("Log/Index/doAddLog")}',
                            data: that.form,
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    that.$message.success(res.msg);
                                    setTimeout(function(){
                                        parent.window.layer.closeAll();
                                    }, 1000)
                                } else {
                                    that.$message.error(res.msg);
                                }
                            }
                        });
                    },
                    onCancel: function(){
                        parent.window.layer.closeAll();
                    },
                },
                mounted: function () {

                },

            })
        })
    </script>
</block>
