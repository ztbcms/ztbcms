<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="16" :md="12">
                <!--                插入template 文件-->
                <template>
                    <div>
                        <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="100px"
                                 label-position="top">
                            <el-form-item label="行为标识" prop="name">
                                <el-input v-model="formData.name" placeholder="请输入单行文本行为标识" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">输入行为标识 英文字母</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="行为名称" prop="title">
                                <el-input v-model="formData.title" placeholder="请输入行为名称" clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">输入行为名称</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="下拉选择" prop="type">
                                <el-select v-model="formData.type" placeholder="请选择下拉选择" clearable
                                           :style="{width: '100%'}">
                                    <el-option v-for="(item, index) in typeOptions" :key="index" :label="item.label"
                                               :value="item.value"
                                               :disabled="item.disabled"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="行为描述" prop="remark">
                                <el-input v-model="formData.remark" type="textarea" placeholder="请输入行为描述"
                                          :autosize="{minRows: 4, maxRows: 4}" :style="{width: '100%'}"></el-input>
                            </el-form-item>

                            <div class="cross" style="width:100%;">
                                <ul id="J_ul_list_addItem" class="J_ul_list_public" style="margin-left:0px;">
                                    <li>
                                        <span style="width:40px;">排序</span>
                                        <span>规则</span>
                                    </li>
                                    <li>
                                              <span style="width:40px;">
                                                  <input type="text" name="listorder[0]" class="input" value=""
                                                         style="width:35px;">
                                              </span>

                                        <span style="width:500px;">
                                                  <input type="text" name="rule[0]" class="input" value=""
                                                         style="width:450px;">
                                              </span>
                                    </li>
                                </ul>
                            </div>
                            <a href="" class="link_add Js_ul_list_add" data-related="addItem">添加规则</a></td>

                            <el-form-item size="large">
                                <el-button type="primary" @click="submitForm">提交</el-button>
                            </el-form-item>
                        </el-form>
                    </div>
                </template>
            </el-col>
        </el-card>
    </div>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                // 插入export default里面的内容
                components: {},
                props: [],
                data() {
                    return {
                        formData: {
                            name: undefined,
                            title: undefined,
                            type: '1',
                            remark: undefined,
                            listorder : [],
                            rule : []
                        },
                        rules: {
                            name: [{
                                required: true,
                                message: '请输入单行文本行为标识',
                                trigger: 'blur'
                            }],
                            title: [{
                                required: true,
                                message: '请输入行为名称',
                                trigger: 'blur'
                            }],
                            type: [{
                                required: true,
                                message: '请选择下拉选择',
                                trigger: 'change'
                            }],
                        },
                        typeOptions: [{
                            "label": "控制器",
                            "value": '1'
                        }, {
                            "label": "视图",
                            "value": '2'
                        }],
                    }
                },
                computed: {},
                watch: {},
                created() {
                },
                mounted() {
                },
                methods: {
                    submitForm() {
                        var that = this;
                        this.$refs['elForm'].validate(valid => {
                            if (!valid) return;
                            // TODO 提交表单

                            //只取前十的参数
                            for(var x=0; x<9;x++){
                                var listorderval  = $("input[name='listorder["+x+"]']").val();
                                var ruleval  = $("input[name='rule["+x+"]']").val();
                                if(listorderval) {
                                    that.formData.listorder.push(listorderval);
                                }
                                if(ruleval) {
                                    that.formData.rule.push(ruleval);
                                }
                            }

                            $.ajax({
                                url: "{:U('Behavior/add')}",
                                method: 'post',
                                dataType: 'json',
                                data: this.formData,
                                success: function (res) {
                                    if (!res.status) {
                                        layer.msg(res.info)
                                    } else {
                                        layer.msg(res.info)
                                    }
                                }
                            });

                        })
                    }
                }
            });

            var Js_ul_list_add = $('a.Js_ul_list_add');
            var new_key = 0;
            if (Js_ul_list_add.length) {
                //添加
                Js_ul_list_add.click(function (e) {
                    e.preventDefault();
                    new_key++;
                    var $this = $(this);
                    //添加分类
                    var _li_html = '<li>\
								<span style="width:40px;"><input type="test" name="listorder[' + new_key + ']" class="input" value="" style="width:35px;"></span>\
								<span style="width:500px;"><input type="test" name="rule[' + new_key + ']" class="input" value="" style="width:450px;"></span>\
							</li>';
                    //"new_"字符加上唯一的key值，_li_html 由列具体页面定义
                    var $li_html = $(_li_html.replace(/new_/g, 'new_' + new_key));
                    $('#J_ul_list_' + $this.data('related')).append($li_html);
                    $li_html.find('input.input').first().focus();
                });

                //删除
                $('ul.J_ul_list_public').on('click', 'a.J_ul_list_remove', function (e) {
                    e.preventDefault();
                    $(this).parents('li').remove();
                });
            }
        });
    </script>

    <link href="/statics/css/admin_style.css" rel="stylesheet"/>
    <style>

    </style>
</block>
