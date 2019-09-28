<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <div class="filter-container">

            </div>
            <div class="filter-container">
                <div class="label">提问人名称</div>
                <div>{{questions.user_name}}</div>
            </div>
            <div class="filter-container">
                <div class="label">提问详情</div>
                <div>
                    <div>{{questions.title}}</div>
                    <div style="margin: 10px">
                        <template v-for="item in questions.images">
                            <el-image :src="item" lazy style="width: 128px;height: 128px;margin: 10px"
                                      :preview-src-list="questions.images">
                        </template>
                    </div>
                    <el-button type="danger" style="margin: 10px">删除提问</el-button>
                </div>
            </div>
        </el-card>

        <el-card style="margin-top: 20px;padding: 20px">
            <div style="display: flex">
                <div class="reply-label" style="margin-right: 10px;"><span style="color: red"> * </span>提问回复</div>
                <div class="reply">
                    <div v-if="questions.reply_status==1">
                        {{questions.reply_content}}
                    </div>
                    <div v-else>
                        <el-input v-model="reply_content" type="textarea" rows="6" placeholder="请输入您要回复的内容"
                                  style="width: 80%;"></el-input>
                        <div>
                            <el-button type="primary" style="margin-top: 10px">确认回复</el-button>
                        </div>
                    </div>
                </div>
            </div>
        </el-card>
    </div>

    <style>
        .filter-container {
            padding-bottom: 10px;
            padding-top: 10px;
            width: 100%;
            display: flex;
        }

        .label {
            font-weight: bolder;
            margin-right: 40px;
            min-width: 100px;
        }

        .reply-label {
            min-width: 100px;
        }

        .reply {
            width: 100%;
        }
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    id: '{$id}',
                    tableKey: 0,
                    questions: {},
                    reply_content: ''

                },
                watch: {},
                filters: {
                    parseTime: function (time, format) {
                        return Ztbcms.formatTime(time, format)
                    },
                },
                methods: {
                    getDetail: function () {
                        var that = this;
                        $.ajax({
                            url: '{:U("Elementui/TableDemo/getDetail")}',
                            data: {id: that.id},
                            type: 'post',
                            dataType: 'json',
                            success: function (res) {
                                if (res.status) {
                                    that.questions = res.data;
                                }
                            }
                        });
                    },
                },
                mounted: function () {
                    this.getDetail();
                },
            })
        })
    </script>
</block>

