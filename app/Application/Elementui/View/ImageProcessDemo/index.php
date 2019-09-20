<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>图片生成实例</h3>
            <pre>
* 图片生成建议：
* 1. 少用字符
* 2. 文字尽量简洁
* 3. 不支持emoji
* 4. 注意字体版权（内置免费思源黑体简体）
            </pre>
            <el-row>
                <el-col :span="16">
                    <div class="grid-content ">
                        <el-form ref="form" :model="form" label-width="80px">
                            <el-form-item label="呢称">
                                <el-input v-model="form.nick_name"></el-input>
                            </el-form-item>
                            <el-form-item label="描述">
                                <el-input v-model="form.description"></el-input>
                            </el-form-item>
                            <el-form-item label="预览">
                                <el-image v-if="url"
                                        style="width: 200px;"
                                        :src="url"
                                        :fit="fit"
                                          :preview-src-list="[url]"
                                ></el-image>
                            </el-form-item>
                            <el-form-item>
                                <el-button type="primary" @click="onSubmit">生成</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-col>
                <el-col :span="8">
                    <div class="grid-content "></div>
                </el-col>
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
                        nick_name: '',
                        description: '',
                    },
                    url: ''
                },
                watch: {},
                filters: {},
                methods: {
                    onSubmit: function () {
                        var that = this;
                        var data = this.form
                        $.ajax({
                            url: '{:U("Elementui/ImageProcessDemo/createSharePoster")}',
                            data: data,
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                console.log(res)
                                if (res.status) {
                                    that.url = res.data.path
                                } else {
                                    layer.msg(res.msg);
                                }
                            }
                        })
                    },
                    onCancel: function () {
                        this.$message.error('已取消');
                    },
                },
                mounted: function () {

                },

            })
        })
    </script>
</block>
