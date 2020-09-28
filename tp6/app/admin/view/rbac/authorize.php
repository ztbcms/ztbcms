<div id="app" style="padding: 8px;" v-cloak>

    <el-card>
        <el-col :sm="24" :md="18">
            <template>
                <div style="margin-bottom: 20px">

                    <h3>{{ name }}</h3>

                    <el-tree
                            :data="data"
                            show-checkbox
                            default-expand-all
                            node-key="id"
                            ref="tree"
                            :default-expanded-keys="defaultKeys"
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
                    roleid : '',
                    filterText: '',
                    defaultKeys : [],
                    data: [],
                    defaultProps: {
                        children: 'children',
                        label: 'label'
                    }
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
                    $.ajax({
                        url: "{:api_url('/Admin/Rbac/addEditAuthorize')}",
                        data: {
                            menuid : checked_keys,
                            roleid : that.roleid
                        },
                        type: "post",
                        dataType: 'json',
                        success: function (res) {
                            if (res.status) {
                                //添加成功
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
                    $.post("{:api_url('Admin/Rbac/getAuthorizeList')}", {
                        id: that.id,
                    }, function (res) {
                        that.data = res.data.list;
                        that.defaultKeys = res.data.select_menu_id;
                        that.roleid = res.data.roleid;
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

</body>
</html>
