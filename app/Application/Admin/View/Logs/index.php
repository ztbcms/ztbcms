<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-row :gutter="10">
                <el-col :span="2">
                    <el-input v-model="form.uid"  placeholder="用户ID"/>
                </el-col>
                <el-col :span="2">
                    <el-input v-model="form.ip"  placeholder="IP"/>
                </el-col>
                <el-col :span="2">
                    <el-select v-model="form.status" placeholder="状态">
                        <el-option
                                label="不限"
                                value="">
                        </el-option>
                        <el-option
                                label="成功"
                                value="1">
                        </el-option>
                        <el-option
                                label="失败"
                                value="0">
                        </el-option>
                    </el-select>
                </el-col>

                <el-col :span="4">
                    <el-date-picker
                            v-model="form.search_date"
                            value-format="yyyy-MM-dd HH:mm:ss"
                            type="datetimerange"
                            range-separator="至"
                            start-placeholder="开始日期"
                            end-placeholder="结束日期">
                    </el-date-picker>
                </el-col>


            </el-row>

            <el-row :gutter="10" style="margin-top: 10px;">

                <el-col :span="10">
                    <el-button  type="primary" @click="getList">
                        筛选
                    </el-button>
                </el-col>
            </el-row>

            <el-table
                    :data="tableData"
                    border
                    style="width: 100%;margin-top: 10px;">
                <el-table-column
                        prop="id"
                        label="ID"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="uid"
                        label="用户ID"
                        width="80">
                </el-table-column>
                <el-table-column
                        prop="status"
                        label="状态"
                        width="80">
                    <template slot-scope="scope">
                        <span v-if="scope.row.status == 1" style="color: green">成功</span>
                        <span v-else style="color: red">失败</span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="info"
                        label="说明">
                    <template slot-scope="scope">
                        <p v-html="scope.row.info"></p>
                    </template>
                </el-table-column>

                <el-table-column
                        prop="get"
                        label="GET">
                </el-table-column>
                <el-table-column
                        prop="time"
                        label="时间">
                    <template slot-scope="scope">
                        <span >{{ scope.row.time | formatTime }}</span>
                    </template>
                </el-table-column>
                <el-table-column
                        prop="ip"
                        label="IP"
                        width="100">
                </el-table-column>
            </el-table>
            <div class="pager_container">
                <el-pagination
                        background
                        layout="prev, pager, next"
                        :page-size="pagination.limit"
                        :current-page.sync="pagination.page"
                        :total="pagination.total_items"
                        @current-change="getList">
                </el-pagination>
            </div>

        </el-card>


    </div>

    <style>
        .pager_container{
            margin-top: 20px;
        }
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    tableData: [],
                    pagination: {
                        page:1 ,
                        limit: 20,
                        total_pages: 0,
                        total_items: 0,
                    },

                    form: {
                        search_date: [],
                        uid: '',
                        ip: '',
                        start_time: '',
                        end_time: '',
                        status: ''
                    }
                },
                watch: {
                    'form.search_date': function(newValue){
                        if(newValue && newValue.length == 2){
                            this.form.start_time = newValue[0]
                            this.form.end_time = newValue[1]
                        }
                    }
                },
                filters: {
                  formatTime(timestamp){
                      var date = new Date();
                      date.setTime(parseInt(timestamp) * 1000);
                      return moment(date).format('YYYY-MM-DD HH:mm:ss')
                  }
                },
                methods: {
                    getList: function () {
                        var that = this;
                        var where = {
                            page: this.pagination.page,
                            limit: this.pagination.limit,
                            uid: this.form.uid,
                            ip: this.form.ip,
                            status: this.form.status,
                            start_time: this.form.start_time,
                            end_time: this.form.end_time,
                        };
                        $.ajax({
                            url: "{:U('Admin/LogsApi/getOperateLogList')}",
                            data: where,
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                console.log(res)
                                var data = res.data;
                                that.pagination.page = data.page;
                                that.pagination.limit = data.limit;
                                that.pagination.total_pages = data.total_pages;
                                that.pagination.total_items = data.total_items;
                                that.tableData = data.items
                            }
                        })
                    },
                },
                mounted: function () {
                    this.getList();
                }
            })
        })
    </script>
</block>
