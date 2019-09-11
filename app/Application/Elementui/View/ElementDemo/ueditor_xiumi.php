<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>UEditor+秀米编辑器</h3>
            <p>本示例只包含了一个uediotor实例，若需要一个页面有多 editor 实例，请查看 ztbcms-Translate 多语言模块的Demo</p>
            <el-row>
                <el-col :span="24">
                    <div class="grid-content ">
                        <el-form ref="form" :model="form" label-width="80px">
                            <el-form-item label="活动名称">
                                <el-input v-model="form.name"></el-input>
                            </el-form-item>

                            <div class="el-form-item">
                                <label class="el-form-item__label" style="width: 80px;">内容</label>
                                <div class="el-form-item__content" style="margin-left: 80px;line-height: 0;">
                                    <textarea id="editor_content" style="height: 400px;"></textarea>
                                </div>
                            </div>

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
</block>

<block name="footer">
    <!-- 引入UEditor   -->
    <include file="../../Admin/View/Common/ueditor"/>
    <script>

        $(document).ready(function () {
            var ueditorInstance = UE.getEditor('editor_content');

            new Vue({
                el: '#app',
                data: {
                    form: {
                        name: '周末爬山',
                        content: ' <p>本示例只包含了一个uediotor实例，若需要一个页面有多 editor 实例，请查看 ztbcms-Translate 多语言模块的Demo</p>',

                    }
                },
                watch: {},
                filters: {},
                methods: {
                    onSubmit: function(){
                        this.form.content = ueditorInstance.getContent()
                        console.log(this.form)
                        this.$message.success('提交成功，请查看控制台输出内容');
                    },
                    onCancel: function(){
                        this.$message.error('已取消');
                    },
                },
                mounted: function () {
                    var that = this
                    ueditorInstance.ready(function (editor) {
                        //ueditor 初始化
                        editor.setContent(that.form.content);
                    }.bind(this, ueditorInstance))
                },

            })
        })
    </script>
</block>
