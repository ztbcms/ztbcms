<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h3>编辑</h3>
            <div class="filter-container">
                <el-form :model="form" label-width="120px">
                    <el-form-item label="输入框" required>
                        <el-input v-model="form.name" placeholder="请输入名称" style="width: 400px"></el-input>
                    </el-form-item>
                    <el-form-item label="排序">
                        <el-input v-model="form.order" style="width: 400px"></el-input>
                    </el-form-item>
                    <el-form-item label="排序">
                        <el-input-number v-model="form.order" :min="0"></el-input-number>
                    </el-form-item>
                    <el-form-item label="展示状态">
                        <el-switch
                                v-model="form.show_status"
                                active-color="#13ce66"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                    </el-form-item>
                    <el-form-item required>
                        <el-button type="primary" @click="doEdit">保存</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </el-card>
    </div>

    <style>
        .filter-container {
            padding-bottom: 10px;
        }

    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    form: {
                        id: '{:I("get.id")}',
                        picture: [],
                        order: 1,
                        show_status: "1",
                    },
                    tableKey: 0,
                    pictureUploadStatus: 1
                },
                watch: {},
                filters: {},
                methods: {
                    doEdit: function () {
                        layer.msg("保存成功", {time: 1000}, function () {

                        });
                        if (window !== window.parent) {
                            setTimeout(function () {
                                window.parent.layer.closeAll();
                            }, 1000);
                        }
                    }
                },
                mounted: function () {
                },
            })
        })
    </script>
</block>
