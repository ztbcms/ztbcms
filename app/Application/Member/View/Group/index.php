<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card>
            <h4>会员组列表</h4>
            <el-table
                    :key=""
                    :data="list"
                    border
                    fit
                    highlight-current-row
                    style="width: 100%;"
                    @selection-change="changebox"
            >
                <el-table-column type="selection"
                                 width="55"
                >
                    <template slot-scope="scope">
                        <span v-if="scope.row.issystem != 1">
                            <el-checkbox  @change="changebox(scope.row.groupid,$event)" ></el-checkbox>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="排序" align="center" width="80">
                    <template slot-scope="scope">
                        <span v-if="scope.row.issystem != 1">
                            <el-input v-model="scope.row.sort" @change="changSort(scope.row.groupid,$event)"></el-input>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="用户组名" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.name }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="ID" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.groupid }}</span>
                    </template>
                </el-table-column>

                <el-table-column label="用户组名" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.name }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="系统组" align="center">
                    <template slot-scope="scope">
                        <i class="el-icon-check"  style="font-size: 1.5rem; color:red"></i>
                    </template>
                </el-table-column>
                <el-table-column label="会员数" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row._count }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="星星数" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.starnum }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="积分小于" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.point }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="允许上传附件" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.allowattachment == 0"><i class="el-icon-check"  style="font-size: 1.5rem; color:red"></i></span>
                        <span v-else><i class="el-icon-close"  style="font-size: 1.5rem; color:blue"></i></span>
                    </template>
                </el-table-column>
                <el-table-column label="投稿权限" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.allowpost == 0"><i class="el-icon-check"  style="font-size: 1.5rem; color:red"></i></span>
                        <span v-else><i class="el-icon-close"  style="font-size: 1.5rem; color:blue"></i></span>
                    </template>
                </el-table-column>
                <el-table-column label="投稿不需审核" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.allowpostverify == 0"><i class="el-icon-check"  style="font-size: 1.5rem; color:red"></i></span>
                        <span v-else><i class="el-icon-close"  style="font-size: 1.5rem; color:blue"></i></span>
                    </template>
                </el-table-column>
                <el-table-column label="搜索权限" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.allowsearch == 0"><i class="el-icon-check"  style="font-size: 1.5rem; color:red"></i></span>
                        <span v-else><i class="el-icon-close"  style="font-size: 1.5rem; color:blue"></i></span>
                    </template>
                </el-table-column>
                <el-table-column label="自动升级" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.allowupgrade == 0"><i class="el-icon-check"  style="font-size: 1.5rem; color:red"></i></span>
                        <span v-else><i class="el-icon-close"  style="font-size: 1.5rem; color:blue"></i></span>
                    </template>
                </el-table-column>
                <el-table-column label="发送短信" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.allowsendmessage == 0"><i class="el-icon-check"  style="font-size: 1.5rem; color:red"></i></span>
                        <span v-else><i class="el-icon-close"  style="font-size: 1.5rem; color:blue"></i></span>
                    </template>
                </el-table-column>

                <el-table-column label="操作" align="center" width="80" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <el-button type="primary" size="mini" @click="openEditNewFrame(scope.row.groupid)">
                            修改
                        </el-button>

                    </template>
                </el-table-column>
            </el-table>

            <div style="margin-top: 20px;">
                    <el-button type="primary" size="" @click="sortBtn">
                        排序
                    </el-button>
                    <el-button size="" type="danger"
                               @click="handleDelete()">
                        删除
                    </el-button>
            </div>

        </el-card>
    </div>

    <style>
        .filter-container {
            padding-bottom: 10px;
        }

        .pagination-container {
            padding: 32px 16px;
        }
    </style>

    <script>
        $(document).ready(function () {
            new Vue({
                el: '#app',
                data: {
                    groupid:[],
                    list: [],
                    sortlist:[],
                },
                watch: {},
                filters: {},
                methods: {
                    getList: function () {
                        var that = this;
                        $.ajax({
                            url:"{:U('getInfoApi')}",
                            dataType:"json",
                            type:"get",
                            success(res){
                                that.list = res.data;
                            }
                        })
                    },
                    //排序数组
                    changSort(groupid,e){
                        var that = this
                        that.sortlist.forEach(function(value,index,array){
                            if(value.groupid == groupid){
                                that.sortlist.splice(index,1)
                            }
                        });
                        var obj = {
                            groupid: groupid,
                            value: e
                        };
                        that.sortlist.push(obj)
                    },
                    //点击排序
                    sortBtn:function(){
                        var that = this;
                        $.ajax({
                            url:"{:U('sortApi')}",
                            dataType:"json",
                            type:"post",
                            data:{
                                sortlist:that.sortlist,
                                grouplist:that.groupid
                            },
                            success(res){
                                if(res.status){
                                    layer.alert(res.info, { icon: 1, closeBtn: 0 }, function (index) {
                                        window.location.reload();
                                    });
                                }else{
                                    that.$message.error(res.info)
                                }
                            }
                        })
                    },
                    //选中的列表
                    changebox(val,e){
                        var that = this
                        that.groupid.forEach(function(value,index,array){
                            if(value.id == val){
                                that.groupid.splice(index,1)
                            }
                        });
                        if(e == true){
                            that.groupid.push(val)
                        }
                    },
                    openEditNewFrame: function (id) {
                        var url = "index.php?g=Member&m=Group&a=edit&groupid=";
                        if (id !== 0) {
                            url += id
                        }
                        Ztbcms.openNewIframeByUrl('修改会员组',url)
                    },
                    handleDelete: function () {
                        var that = this;
                        layer.confirm('是否确定删除该此项内容吗？', {
                            btn: ['确认', '取消'] //按钮
                        }, function () {
                            that.doDelete()
                            layer.closeAll();
                        }, function () {
                            layer.closeAll();
                        });
                    },
                    doDelete(){
                      var that = this;
                      $.ajax({
                          url:"{:U('delete')}",
                          dataType:"json",
                          type:"post",
                          data:{
                              groupid: that.groupid,
                          },
                          success(res){
                              if(res.status){
                                  layer.alert(res.info, { icon: 1, closeBtn: 0 }, function (index) {
                                      window.location.reload();
                                  });
                              }else{
                                  that.$message.error(res.info)
                              }
                          }
                      })
                    },
                    orderChange: function (index) {
                        var that = this;
                        var url = '{:U("Manage/CourseCategory/doAddEditCourseCategory")}';
                        var data = that.list[index];
                        this.$prompt('请输入排序值', '编辑排序', {
                            confirmButtonText: '确定',
                            cancelButtonText: '取消',
                        }).then(({value}) => {
                            data['order'] = value;
                            that.httpPost(url, data, function (res) {
                                if (res.status) {
                                    layer.msg(res.msg, {time: 1000}, function () {
                                    });
                                    that.dialogFormVisible = false;
                                    that.getList();
                                } else {
                                    layer.msg(res.msg, {time: 1000});
                                }
                            });
                        }).catch(() => {
                        });
                        this.form = this.tmp_form;
                    }
                },
                mounted: function () {
                    this.getList();
                },

            })
        })
    </script>
</block>

