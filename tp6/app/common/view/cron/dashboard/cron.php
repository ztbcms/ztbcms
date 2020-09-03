<div id="app" v-cloak>
    <el-card>
        <div style="margin-bottom: 20px;">
            <el-button @click="createCron" type="primary">
                新增任务
            </el-button>
        </div>
        <el-table
                :data="tableData"
                border
                style="width: 100%">
            <el-table-column
                    prop="date"
                    label="日期"
                    width="180">
            </el-table-column>
            <el-table-column
                    prop="name"
                    label="姓名"
                    width="180">
            </el-table-column>
            <el-table-column
                    prop="address"
                    label="地址">
            </el-table-column>
        </el-table>
    </el-card>
</div>
<script>
    $(function () {
        new Vue({
            el: "#app",
            data: {
                tableData: []
            },
            mounted: function () {
                this.getList();
            },
            methods: {
                createCron: function () {
                    Ztbcms.openNewIframeByUrl('新增任务', "{:urlx('common/cron.dashboard/createCron')}");
                },
                getList: function () {
                    var data = {};
                    $.ajax({
                        url: "{:urlx('common/cron.dashboard/getCronList')}",
                        data: data,
                        dataType: 'json',
                        type: 'get',
                        success: function (res) {
                            var data = res.data;
                        }
                    })
                },
            }
        });
    })
</script>
