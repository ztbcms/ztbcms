<div id="app" style="padding: 8px;" v-cloak>

    <el-card>
        <el-col :sm="24" :md="18">
            <template>
                <div style="margin-bottom: 20px">

                    <h3>{{ name }} 权限设置</h3>

                    <el-tree
                            :data="data"
                            show-checkbox
                            default-expand-all
                            node-key="id"
                            ref="tree"
                            :default-checked-keys="defaultKeys"
                            highlight-current
                            :props="defaultProps">
                    </el-tree>

                </div>

                <el-button class="filter-item" style="margin-left: 10px;margin-bottom: 15px;" size="small" type="primary"
                           @click="submitForm()">
                    授权
                </el-button>
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
            data: function() {
                return {
                    id : "{$id}",
                    name : '',
                    role_id : '',
                    filterText: '',
                    defaultKeys : [],
                    data: [],
                    defaultProps: {
                        children: 'children',
                        label: 'label'
                    },
                }
            },
            computed: {},
            watch: {
                filterText: function(val) {
                    this.$refs.tree.filter(val);
                }
            },
            created: function() {
            },
            mounted: function() {
                this.getDetails();
            },
            methods: {
                submitForm: function() {
                    var that = this;
                    var checked_keys = that.$refs.tree.getCheckedKeys().toString();
                    var half_checked_keys = that.$refs.tree.getHalfCheckedKeys().toString();
                    // 选中+半选中也需要记录
                    var keys = checked_keys + ',' + half_checked_keys
                    $.ajax({
                        url: "{:api_url('/admin/Role/authorize')}",
                        data: {
                            menuid : keys,
                            roleid : that.role_id
                        },
                        type: "post",
                        dataType: 'json',
                        success: function (res) {
                            if (res.status) {
                                layer.msg(res.msg);
                                setTimeout(function () {
                                    parent.window.layer.closeAll();
                                }, 1000)
                            } else {
                                layer.msg(res.msg)
                            }
                        }
                    })
                },
                //获取菜单详情
                getDetails: function () {
                    var that = this;
                    $.get("{:api_url('/admin/Role/getAuthorizeList')}", {
                        id: that.id,
                    }, function (res) {
                        that.data = res.data.list;
                        that.defaultKeys = res.data.select_menu_id;
                        that.role_id = res.data.role_id;
                        that.name = res.data.name;
                    }, 'json');
                },
                filterNode: function(value, data) {
                    if (!value) return true;
                    return data.label.indexOf(value) !== -1;
                }
            }
        });
    });
</script>

