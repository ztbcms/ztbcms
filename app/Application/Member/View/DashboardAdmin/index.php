<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-row :gutter="20">
            <!--  左侧  -->
            <el-col :span="19">
                <!-- 后台统计  -->
                <el-row :gutter="20" style="margin-top: 20px;">
                    <el-col :span="24">
                        <el-card body-style="height:130px; " class="card-summary">
                            <div class="card-summary-label">统计</div>
                            <div class="card-summary-data">
                                <el-row>
                                    <el-col :span="8" class="col-data">
                                        <div class="data-value">
                                            <template v-if="adminStatisticsInfo.total_member">
                                                {{adminStatisticsInfo.total_member}}
                                            </template>
                                            <template v-else>
                                                -
                                            </template>
                                        </div>
                                        <div class="data-label">用户数总数</div>
                                    </el-col>
                                    <el-col :span="8" class="col-data">
                                        <div class="data-value">
                                            <template v-if="adminStatisticsInfo.today_new_member">
                                                {{adminStatisticsInfo.today_new_member}}
                                            </template>
                                            <template v-else>
                                                -
                                            </template>
                                        </div>
                                        <div class="data-label">今日新增</div>
                                    </el-col>
                                    <el-col :span="8" class="col-data">
                                        <div class="data-value">
                                            <template v-if="adminStatisticsInfo.last_sevent_day_new_member">
                                                {{adminStatisticsInfo.last_sevent_day_new_member}}
                                            </template>
                                            <template v-else>
                                                -
                                            </template>
                                        </div>
                                        <div class="data-label">最近7日新增</div>
                                    </el-col>
                                </el-row>
                            </div>

                        </el-card>
                    </el-col>
                </el-row>

                <!--常用功能-->
                <el-row :gutter="20" style="margin-top: 20px;">
                    <el-col :span="24">
                        <el-card body-style="height:130px;" class="card-changyong">
                            <div class="card-changyong-label">常用功能</div>
                            <div class="card-changyong-data">
                                <el-row>
                                    <el-col :span="4">
                                        <div class="col-data" @click="gotoPage1">
                                            <div class="item-icon">
                                                <i class="iconfont icon-shuju"></i>
                                            </div>
                                            <div class="item-label">用户前台</div>
                                        </div>
                                    </el-col>

                                    <el-col :span="4">
                                        <div class="col-data" @click="gotoPage2">
                                            <div class="item-icon">
                                                <i class="iconfont icon-empty"></i>
                                            </div>
                                            <div class="item-label">模型管理</div>
                                        </div>
                                    </el-col>

                                    <el-col :span="4">
                                        <div class="col-data" @click="gotoPage3">
                                            <div class="item-icon">
                                                <i class="el-icon-setting"></i>
                                            </div>
                                            <div class="item-label">站点配置</div>
                                        </div>
                                    </el-col>

                                    <el-col :span="4">
                                        <div class="col-data" @click="gotoPage4">
                                            <div class="item-icon">
                                                <i class="iconfont icon-question"></i>
                                            </div>
                                            <div class="item-label">管理员管理</div>
                                        </div>
                                    </el-col>

                                    <el-col :span="4">
                                        <div class="col-data" @click="gotoPage5">
                                            <div class="item-icon">
                                                <i class="el-icon-tickets"></i>
                                            </div>
                                            <div class="item-label">开发文档</div>
                                        </div>
                                    </el-col>

                                </el-row>
                            </div>

                        </el-card>
                    </el-col>
                </el-row>
            </el-col>
            <!--  右侧  -->
            <el-col :span="5">

            </el-col>

        </el-row>
    </div>

    <style>

        /* 后台统计  */
        .card-summary .card-summary-label {
            font-size: 14px;
            color: #333333;
            font-weight: bold;
        }

        .card-summary .card-summary-data {
            margin: 35px 0px 20px;
        }

        .card-summary .col-data {
            text-align: center;
        }

        .card-summary .data-value {
            font-size: 24px;
            color: #409eff;
            font-weight: bold;
        }

        .card-summary .data-label {
            margin-top: 10px;
            font-size: 14px;
            color: #333333;
            font-weight: bold;
        }

        /* 后台统计 */

        /*常用功能*/
        .card-changyong .card-changyong-label {
            font-size: 14px;
            color: #333333;
            font-weight: bold;
        }

        .card-changyong .card-changyong-data {
            margin: 35px 0px 20px;
            text-align: center;
        }

        .card-changyong .col-data {
            cursor: pointer;
        }

        .card-changyong .item-icon {
            display: inline-block;
            border-radius: 4px;
            background: #409eff;
            width: 54px;
            height: 54px;
            text-align: center;
            line-height: 54px;
            color: white;
            font-size: 28px;
        }

        .card-changyong .item-icon i{
            font-size: 28px;
        }

        .card-changyong .item-label {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }

        /*常用功能*/

        .system_info .el-form-item__label {
            font-weight: bold;
        }
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    systemInfo: [],
                    adminStatisticsInfo: {},
                    alert_message: [],
                },
                watch: {},
                filters: {},
                methods: {
                    getInfo: function () {
                        var that = this;
                        $.ajax({
                            url: "{:U('Member/DashboardAdmin/getDashboardIndexInfo')}",
                            data: {},
                            dataType: 'json',
                            type: 'post',
                            success: function (res) {
                                var data = res.data;
                                that.adminStatisticsInfo = data.admin_statistics_info
                            }
                        })
                    },
                    gotoPage1: function () {
                        window.open("{:U('Member/Index/index')}")
                    },

                },
                mounted: function () {
                    this.getInfo();
                }
            })
        })
    </script>
</block>
