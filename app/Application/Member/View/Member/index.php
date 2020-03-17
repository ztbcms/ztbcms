<extend name="../../Admin/View/Common/element_layout"/>

<block name="content">
    <div id="app" style="padding: 8px;" v-cloak>
        <el-card v-if="listQuery.tab == 1">
            <div class="filter-container">
                <template>
                    <el-tabs v-model="listQuery.tab" >
                        <el-tab-pane v-for="(item,index) in tab" :key="index" :label="item.name"
                                     :name="item.id" ></el-tab-pane>
                    </el-tabs>
                </template>
            </div>

            <div class="filter-container">
                注册时间：
                <el-date-picker
                        v-model="listQuery.input_date"
                        type="daterange"
                        size=""
                        value-format="yyyy-MM-dd HH:mm:ss"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        :default-time="['00:00:00', '23:59:59']">
                </el-date-picker>
                状态：
                <el-select size="" v-model="listQuery.islock" placeholder="请选择">
                    <el-option
                            v-for="item in zt"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                    </el-option>
                </el-select>
                <br>
                <div style="margin-top: 10px">
                审核：
                <el-select v-model="listQuery.checked" placeholder="请选择">
                    <el-option
                            v-for="item in zt1"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                    </el-option>
                </el-select>
                <el-select v-model="listQuery.type1" placeholder="请选择">
                    <el-option
                            v-for="item in filter1"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                    </el-option>
                </el-select>
                <el-select v-model="listQuery.type2" placeholder="请选择">
                    <el-option
                            v-for="item in filter2"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                    </el-option>
                </el-select>
                <el-input v-model="listQuery.title" placeholder="用户名" style="width: 200px;"
                          class="filter-item"></el-input>
                <el-button class="filter-item" type="primary" style="margin-left: 10px;"
                           @click="search">
                    搜索
                </el-button>
                </div>

            </div>

            <el-table
                ref="multipleTable"
                :key="tableKey"
                :data="list"
                border
                fit
                highlight-current-row
                @selection-change="changebox"
                style="width: 100%;"
            >
                <el-table-column
                        type="selection"
                        width="55">
                </el-table-column>
                <el-table-column
                        width="55">
                    <template  slot-scope="scope">
                        <span v-if="scope.row.islock == 1" title="锁定">
                            <i class="el-icon-lock" style="color: red;"></i>
                        </span>
                        <span v-if="scope.row.checked == 0" title="待审核">
                            <i class="el-icon-info" style="color: blue;"></i>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="用户ID" align="center" width="50">
                    <template slot-scope="scope">
                        <span>{{ scope.row.userid }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="用户名" align="center">
                    <template slot-scope="scope">
                        <span v-if="scope.row.userpic == '' ">
                            <el-avatar shape="square" :size="20" src="{$config_siteurl}statics/images/member/nophoto.gif" ></el-avatar>
                        </span>
                        <span v-else>
                            <el-avatar shape="square" :size="20" :src="scope.row.userpic"></el-avatar>
                        </span>
                        <span @click="openDetail(scope.row.userid)">{{ scope.row.username }}
                            <i class="el-icon-search" ></i>
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="昵称" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.nickname }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="邮箱" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.email }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="模型昵称" align="center">
                    <template slot-scope="scope" v-if="groupsModel != null">
                        <span>{{ groupsModel[scope.row.modelid] }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="注册ip" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.regip }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="最后登录" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.lastdate }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="金钱总数" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.amount }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="积分点数" align="center">
                    <template slot-scope="scope">
                        <span>{{ scope.row.point }}</span>
                    </template>
                </el-table-column>

                <el-table-column label="操作" align="center" width="200" class-name="small-padding fixed-width">
                    <template slot-scope="scope">
                        <el-button  size="mini" @click="openDetail(scope.row.userid)">
                            查看详情
                        </el-button>
                        <el-button  size="mini" type="primary"
                                   @click="editOpen(scope.row.userid)">
                            修改
                        </el-button>
                    </template>
                </el-table-column>

            </el-table>
            <div style="margin-top: 30px;">
                <template >
                    <el-button  size="" @click="getIdSh()">
                        审核
                    </el-button>
                    <el-button  size="" @click="getIdNoSh()">
                        取消审核
                    </el-button>
                    <el-button  size="" @click="getIdSd()">
                        锁定
                    </el-button>
                    <el-button  size="" @click="getIdJs()">
                        解锁
                    </el-button>
                    <el-button  size="" @click="getIdDel()">
                        删除
                    </el-button>
                </template>
            </div>

            <div class="pagination-container">
                <el-pagination
                    background
                    layout="prev, pager, next, jumper"
                    :total="total"
                    v-show="total>0"
                    :current-page.sync="listQuery.page"
                    :page-size.sync="listQuery.limit"
                    @current-change="getList"
                >
                </el-pagination>
            </div>

        </el-card>

        <el-card v-if="listQuery.tab == 2">
            <div class="filter-container">
                <template>
                    <el-tabs v-model="listQuery.tab" >
                        <el-tab-pane v-for="(item,index) in tab" :key="index" :label="item.name"
                                     :name="item.id" ></el-tab-pane>
                    </el-tabs>
                </template>
            </div>
        <el-row >
            <el-col :span="9">
                <div class="grid-content ">
                    <el-form ref="form" :model="form" label-width="80px">
                        <el-form-item label="用户名">
                            <el-input v-model="form.username"></el-input>
                        </el-form-item>
                        <el-form-item label="是否审核">
                            <el-radio-group v-model="form.checked">
                                <el-radio label="1">审核通过</el-radio>
                                <el-radio label="0">待审核</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="密码">
                            <el-input type="password" v-model="form.password"></el-input>
                        </el-form-item>
                        <el-form-item label="确认密码">
                            <el-input type="password" v-model="form.pwdconfirm"></el-input>
                        </el-form-item>
                        <el-form-item label="昵称">
                            <el-input v-model="form.nickname"></el-input>
                        </el-form-item>
                        <el-form-item label="邮箱">
                            <el-input v-model="form.email"></el-input>
                        </el-form-item>

                        <el-form-item label="会员组">
                            <template>
                                <el-select v-model="form.groupid" clearable placeholder="请选择">
                                    <el-option
                                            v-for="item in groupCache1"
                                            :key="item.id"
                                            :label="item.label"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                        </el-form-item>
                        <el-form-item label="积分点数">
                            <el-input v-model="form.point" style="width:70px"></el-input>
                            &nbsp;请输入积分点数，积分点数将影响会员用户组
                        </el-form-item>
                        <template v-if="groupsModel1 != '' ">
                        <el-form-item label="会员模型">
                                <el-select v-model="form.modelid" clearable placeholder="请选择">
                                    <el-option
                                            v-for="item in groupsModel1"
                                            :key="item.id"
                                            :label="item.label"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                        </el-form-item>
                        </template>
                        <el-form-item>
                            <el-button type="primary" @click="onSubmit">保存</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </el-col>
            <el-col :span="16"><div class="grid-content "></div></el-col>
        </el-row>
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
                    tableKey: 0,
                    list: [],
                    total: 0,
                    tab: [
                        {
                            id: "1",
                            name: "会员列表"
                        },
                        {
                            id: "2",
                            name: "添加会员"
                        },
                    ],
                    zt:[
                        {value: '', label: '全部'},
                        {value: '1', label: '锁定'},
                        {value: '2', label: '正常'},
                    ],
                    zt1:[
                        {value: '', label: '全部'},
                        {value: '1', label: '审核通过'},
                        {value: '0', label: '待审核'},
                    ],
                    filter1:[
                        {value: 'username', label: '用户名'},
                        {value: 'userid', label: '用户ID'},
                        {value: 'nickname', label: '昵称'},
                    ],
                    filter2:[
                        {value: 'EQ', label: '等于'},
                        {value: 'NEQ', label: '不等于'},
                        {value: 'GT', label: '大于'},
                        {value: 'EGT', label: '大于等于'},
                        {value: 'LT', label: '小于'},
                        {value: 'ELT', label: '小于等于'},
                        {value: 'LIKE', label: '模糊查询'},
                    ],
                    input_date: ['', ''],
                    listQuery: {
                        search:1,
                        page: 1,
                        tab: '1',
                        limit: 20,
                        start_time: '',
                        end_time: '',
                        user_name: '{:I("get.user_name")}',
                        islock:'',
                        checked:'{:I("get.checked")}',
                        type1:'username',
                        type2:'EQ',
                        title:  '{:I("get.user_name")}',
                        input_date: ['', ''],
                    },
                    form: {
                        id:'{$_GET["id"]}',
                        username: '',
                        password: '',
                        pwdconfirm: '',
                        checked: '1',
                        email: '',
                        nickname: '',
                        groupid: '',
                        point: '0',
                    },
                    checkList:[],
                    groupsModel1:[],
                    groupsModel:{$groupsModel},
                    groupCache1:{$groupCache1}
                },
                watch: {},
                filters: {},
                methods: {
                    search: function () {
                        this.getList();
                    },
                    //修改页面
                    editOpen:function(id){
                        var url = "{:U('edit')}";
                        if (id !== 0) {
                            url += '&userid=' + id
                            Ztbcms.openNewIframeByUrl('修改会员信息', url)
                        }
                    },
                    //个人详情
                    openDetail: function(id){
                        var url = "{:U('memberinfo')}";
                        if (id !== 0) {
                            url += '&userid=' + id
                        }
                        layer.open({
                            type: 2,
                            title: '会员详情',
                            content: url,
                            area: ['4   0%', '80%'],
                        })
                    },
                    //选中的列表人
                    changebox(val){
                        var that = this;
                        that.checkList = []
                        val.forEach(function(value,index,array){
                            that.checkList.push(value.userid)
                        });
                    },
                    //审核
                    getIdSh(){
                        var that = this;
                        var checkList = this.checkList
                        $.ajax({
                            url:"{:U('userverify')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": checkList,
                            },
                            success(res){
                                if(res.state){
                                    that.getList()
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.success(res.info);
                                }
                            }
                        })
                    },
                    //取消审核
                    getIdNoSh(){
                        var that = this;
                        var checkList = this.checkList
                        $.ajax({
                            url:"{:U('userunverify')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": checkList,
                            },
                            success(res){
                                if(res.state){
                                    that.getList()
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.success(res.info);
                                }
                            }
                        })
                    },
                    //锁定
                    getIdSd(){
                        var that = this;
                        var checkList = this.checkList
                        $.ajax({
                            url:"{:U('lock')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": checkList,
                            },
                            success(res){
                                if(res.state){
                                    that.getList()
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.success(res.info);
                                }
                            }
                        })
                    },
                    //解锁
                    getIdJs(){
                        var that = this;
                        var checkList = this.checkList
                        $.ajax({
                            url:"{:U('unlock')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": checkList,
                            },
                            success(res){
                                if(res.state){
                                    that.getList()
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.success(res.info);
                                }
                            }
                        })
                    },
                    //删除
                    getIdDel(){
                        var that = this;
                        var checkList = this.checkList
                        $.ajax({
                            url:"{:U('delete')}",
                            dataType:"json",
                            type:"post",
                            data: {
                                "userid": checkList,
                            },
                            success(res){
                                if(res.state){
                                    that.getList()
                                    that.$message.success(res.info);
                                }else{
                                    that.$message.success(res.info);
                                }
                            }
                        })
                    },
                    //获取总列表
                    getList: function () {
                        var that = this;
                        $.ajax({
                            url:"{:U('getMemberUser')}",
                            dataType:"json",
                            type:"get",
                            data: that.listQuery ,
                            success(res){
                                that.list = res.data.items;
                                // that.groupsModel = res.data.groupsModel;
                                that.total = res.data.total_items;
                                that.listQuery.page = res.data.page;
                            }
                        })
                        this.getGroupsModel()
                    },
                    //添加会员
                    onSubmit: function(){
                        var that = this
                        $.ajax({
                            url:"{:U('add')}",
                            dataType:"json",
                            type:"post",
                            data:  that.form,
                            success(res){
                                if(res.status){
                                    layer.alert(res.info, { icon: 1, closeBtn: 0 }, function (index) {
                                        window.location.reload()
                                    });
                                }else{
                                    that.$message.error(res.info);
                                }
                            }
                        })
                    },

                    //获取会员模型
                    getGroupsModel:function(tab, event) {
                        var that = this;
                        $.ajax({
                            url:"{:U('getGroupsModel')}",
                            dataType:"json",
                            type:"get",
                            success(res){
                                if(res.status){
                                    that.groupsModel1 = res.data
                                }
                            }
                        })
                    },
                },
                mounted: function () {
                    this.getList();
                },
            })
        })
    </script>
</block>