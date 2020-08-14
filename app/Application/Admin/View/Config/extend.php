<extend name="../../Admin/View/Common/element_layout"/>
<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <el-col :sm="18" :md="18">
                <!--                插入template 文件-->
                <template>
                    <div>
                        <el-form ref="elForm" :model="formData" :rules="rules" size="medium" label-width="200px"
                                 label-position="left">
                            <el-form-item label="键名 " prop="fieldname">
                                <el-input v-model="formData.fieldname" placeholder="请输入键名 " clearable
                                          :style="{width: '100%'}">
                                    <template slot="append">注意：只允许英文、数组、下划线</template>
                                </el-input>
                            </el-form-item>
                            <el-form-item label="名称" prop="setting['title']">
                                <el-input v-model="formData.setting['title']" placeholder="请输入名称" clearable
                                          :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="类型" prop="type">
                                <el-select v-model="formData.type" placeholder="请选择类型" clearable
                                           :style="{width: '100%'}">
                                    <el-option v-for="(item, index) in typeOptions" :key="index" :label="item.label"
                                               :value="item.value"
                                               :disabled="item.disabled"></el-option>
                                </el-select>
                            </el-form-item>
                            <el-form-item label="提示" prop="setting.tips">
                                <el-input v-model="formData.setting.tips" placeholder="请输入提示" clearable
                                          :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item label="样式" prop="setting.style">
                                <el-input v-model="formData.setting.style" placeholder="请输入样式" clearable
                                          :style="{width: '100%'}">
                                </el-input>
                            </el-form-item>
                            <el-form-item v-if="formData.type == 'radio' || formData.type == 'select'" label="选项" prop="setting[option]">
                                <el-input v-model="formData.setting.option" type="textarea" placeholder="请输入选项"
                                          :autosize="{minRows: 4, maxRows: 4}" :style="{width: '100%'}"></el-input>
                            </el-form-item>

                            <el-form-item size="large">
                                <el-button type="primary" @click="submitForm">提交</el-button>
                            </el-form-item>

                            <div class="h_a">扩展配置 ，用法：模板调用标签：
                                <literal>{:cache('Config</literal>
                                .键名')}，PHP代码中调用：
                                <literal>cache('Config</literal>
                                .键名');
                            </div>

                            <div class="table_full">
                                <form method='post' id="myform" class="J_ajaxForm" action="{:U('Config/extend')}">
                                    <table width="100%" class="table_form">
                                        <volist name="extendList" id="vo">
                                            <php>$setting = unserialize($vo['setting']);</php>
                                            <tr>
                                                <th width="200">{$setting.title}

                                                    <span @click="extendDel({$vo['fid']})"
                                                            class="J_ajax_del" title="删除该项配置" style="color:#F00">X</span>

                                                    <span class="gray"><br/>键名：{$vo.fieldname}</span>
                                                </th>

                                                <th class="y-bg">
                                                    <switch name="vo.type">
                                                        <case value="input">
                                                            <input type="text" class="input" style="{$setting.style}" name="{$vo.fieldname}"
                                                                   value="{$Site[$vo['fieldname']]}">
                                                        </case>
                                                        <case value="select">
                                                            <select name="{$vo.fieldname}">
                                                                <volist name="setting['option']" id="rs">
                                                                    <option value="{$rs.value}"
                                                                    <if condition=" $Site[$vo['fieldname']] == $rs['value'] ">selected</if>
                                                                    >{$rs.title}</option>
                                                                </volist>
                                                            </select>
                                                        </case>
                                                        <case value="textarea">
                                    <textarea name="{$vo.fieldname}"
                                              style="{$setting.style}">{$Site[$vo['fieldname']]}</textarea>
                                                        </case>
                                                        <case value="radio">
                                                            <volist name="setting['option']" id="rs">
                                                                <input name="{$vo.fieldname}" value="{$rs.value}" type="radio"
                                                                <if condition=" $Site[$vo['fieldname']] == $rs['value'] ">checked</if>
                                                                > {$rs.title}
                                                            </volist>
                                                        </case>
                                                        <case value="password">
                                                            <input type="password" class="input" style="{$setting.style}" name="{$vo.fieldname}"
                                                                   value="{$Site[$vo['fieldname']]}">
                                                        </case>
                                                    </switch>
                                                    <span class="gray"> {$setting.tips}</span>
                                                </th>
                                            </tr>
                                        </volist>
                                    </table>

                                    <div class="btn_wrap">
                                        <div class="btn_wrap_pd">
                                            <button class="btn btn_submit mr10 J_ajax_submit_btn" type="submit">编辑</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

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
                            action : 'add',
                            fieldname: '',
                            type : 'input',
                            setting: {
                                'title': '',
                                'tips': '',
                                'style': '',
                                'option': '选项名称1|选项值1'
                            }
                        },
                        rules: {
                            fieldname: [{
                                required: true,
                                message: '请输入键名 ',
                                trigger: 'blur'
                            }],


                        },
                        typeOptions: [{
                            "label": "单行文本框",
                            "value": "input"
                        }, {
                            "label": "下拉框",
                            "value": "select"
                        }, {
                            "label": "多行文本框",
                            "value": "textarea"
                        }, {
                            "label": "单选框",
                            "value": "radio"
                        }, {
                            "label": "密码输入框",
                            "value": "password"
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
                        this.$refs['elForm'].validate(valid => {
                            if (!valid) return;
                            // TODO 提交表单
                            $.ajax({
                                url: "{:U('Config/extend')}",
                                method: 'post',
                                dataType: 'json',
                                data: this.formData,
                                success: function (res) {
                                    if (!res.status) {
                                        layer.msg(res.info)
                                    } else {
                                        window.location.reload();
                                        layer.msg(res.info)
                                    }
                                }
                            });
                        })
                    },
                    extendDel(fid) {
                        $.ajax({
                            url: "{:U('Config/extendDel')}",
                            method: 'post',
                            dataType: 'json',
                            data: {
                                fid : fid,
                                action : 'delete'
                            },
                            success: function (res) {
                                if (!res.status) {
                                    layer.msg(res.info)
                                } else {
                                    window.location.reload();
                                    layer.msg(res.info)
                                }
                            }
                        });
                    }
                }
            });
        });
    </script>

    <link href="/statics/css/admin_style.css" rel="stylesheet" />
</block>
