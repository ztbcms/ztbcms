<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <div>
                <template v-for="(item,index) in options">
                    <div>
                        <span>
                            <el-input class="label-input" v-model="item.label">
                        </span>
                        <span class="close-icon">
                            <i @click="deleteEvent(index)" class="el-icon-close"></i>
                        </span>
                    </div>
                </template>
                <div class="label-input">
                    <el-input @blur="blurEvent" v-model="newLabel" placeholder="输入新增记录">
                </div>
                <div id="dosubmit" @click="dosubmitEvent"></div>
            </div>
        </el-card>
    </div>
    <style lang="css">
        .label-input {
            display: inline-block;
            width: 200px;
            margin-bottom: 10px;
        }

        .close-icon {
            width: 100px;
            cursor: pointer;
            color: #f56c6c;
        }
    </style>
    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    options: [],
                    newLabel: ''
                },
                watch: {},
                filters: {},
                methods: {
                    deleteEvent(index) {
                        console.log('deleteEvent', index)
                        this.$confirm('是否确认删除该选项，删除前请注意关联数据。', '提示', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                            type: 'warning'
                        }).then(() => {
                            if (this.options.length > 1) {
                                this.options.splice(index, 1)
                            } else {
                                this.$message.error('数组不能为空');
                            }
                        }).catch(() => {
                        });
                    },
                    dosubmitEvent() {
                        var that = this;
                        $.ajax({
                            type: "post",
                            url: "{:U('Content/BoxField/save')}",
                            data: {data: this.options},
                            dataType: 'json',
                            success: function (res) {
                                console.log(res);
                                if (res.status) {
                                    that.$message({
                                        type: 'success',
                                        message: '更新成功'
                                    });
                                    setTimeout(function () {
                                        window.parent.location.reload()
                                        window.parent.art.dialog.list[that.options[0].fieldid].close()
                                    }, 1000)
                                } else {
                                    that.$message.error(res.msg);
                                }
                            }
                        })
                    },
                    blurEvent() {
                        if (!this.newLabel) {
                            return;
                        }
                        console.log('blurEvent', this.newLabel);
                        var item = {
                            modelid: this.options[0].modelid,
                            fieldid: this.options[0].fieldid,
                            label: this.options[0].label,
                            value: this.options[0].value
                        };
                        var maxValue = 0;
                        if (this.options[0]) {
                            maxValue = this.options[0]['value'];
                        }
                        for (var i in this.options) {
                            if (this.options[i].value > maxValue) {
                                maxValue = this.options[i].value;
                            }
                        }
                        item['label'] = this.newLabel;
                        item['value'] = parseInt(maxValue) + 1;
                        this.options.push(item)
                        this.newLabel = '';
                    }
                },
                mounted: function () {
                    this.options = JSON.parse('<?php echo json_encode($options) ?>')
                },
            })
        })
    </script>
</block>
