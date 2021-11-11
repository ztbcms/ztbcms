<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <h3>列表页示例</h3>

        <div class="filter-container">
            <el-input v-model="listQuery.title" placeholder="标题" style="width: 200px;"
                      class="filter-item"></el-input>

            <el-select v-model="listQuery.importance" placeholder="重要性" clearable style="width: 90px"
                       class="filter-item">
                <el-option v-for="item in importanceOptions" :key="item" :label="item" :value="item"></el-option>
            </el-select>
            <el-select v-model="listQuery.type" placeholder="类型" clearable class="filter-item"
                       style="width: 130px">
                <el-option v-for="item in calendarTypeOptions" :key="item.key"
                           :label="item.display_name+'('+item.key+')'" :value="item.key"></el-option>
            </el-select>
            <el-select v-model="listQuery.sort" style="width: 140px" class="filter-item">
                <el-option v-for="item in sortOptions" :key="item.key" :label="item.label"
                           :value="item.key"></el-option>
            </el-select>
            <el-button class="filter-item" type="primary" icon="el-icon-search">
                搜索
            </el-button>
            <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-edit"
                       @click="openArticleLink('http://www.baidu.com')">
                添加
            </el-button>
        </div>
        <el-table
                :key="tableKey"
                :data="data_list"
                border
                fit
                highlight-current-row
                style="width: 100%;"
                @sort-change="sortChange"
        >
            <el-table-column fixed="left" label="ID" prop="id" sortable="custom" align="center" width="80"
                             :class-name="getSortClass('id')">
                <template slot-scope="scope">
                    <span>{{ scope.row.id }}</span>
                </template>
            </el-table-column>
            <el-table-column label="日期" width="150px" align="center">
                <template slot-scope="scope">
                    <span>{{ scope.row.timestamp | parseTime('{y}-{m}-{d} {h}:{i}') }}</span>
                </template>
            </el-table-column>
            <el-table-column label="标题" min-width="150px">
                <template slot-scope="{row}">
                    <span>{{ row.title }}</span>
                    <!--                    <span style="color: #337ab7;cursor: pointer;"-->
                    <!--                          @click="openArticleLink(row.link)">{{ row.title }}</span>-->
                    <!--<el-tag>{{ row.type }}</el-tag>-->
                </template>
            </el-table-column>
            <el-table-column label="作者" width="110px" align="center">
                <template slot-scope="scope">
                    <span>{{ scope.row.author }}</span>
                </template>
            </el-table-column>
            <el-table-column label="审核人" width="110px" align="center">
                <template slot-scope="scope">
                    <span style="color:red;">{{ scope.row.reviewer }}</span>
                </template>
            </el-table-column>
            <el-table-column label="重要性" width="80px">
                <template slot-scope="scope">
                    <span v-for="n in +scope.row.importance" :key="n" class="iconfont icon-yuandian"></span>

                </template>
            </el-table-column>
            <el-table-column label="访问量" align="center" width="95">
                <template slot-scope="{row}">
                    <span v-if="row.pageviews" @click="linkToList(row.id)">
                        <el-link>{{ row.pageviews }}</el-link>
                    </span>
                    <span v-else>0</span>
                </template>
            </el-table-column>
            <el-table-column label="状态" class-name="status-col" width="100">
                <template slot-scope="{row}">
                    <el-tag :type="row.status | statusFilter">
                        {{ row.status }}
                    </el-tag>
                </template>
            </el-table-column>
            <el-table-column fixed="right" label="Actions" align="center" width="230" class-name="small-padding fixed-width">
                <template slot-scope="{row}">
                    <el-button type="primary" size="mini" @click="openArticleLink(row.link)">
                        编辑
                    </el-button>
                    <el-button v-if="row.status!='published'" size="mini" type="success"
                               @click="handleModifyStatus(row,'published')">
                        发布
                    </el-button>
                    <el-button v-if="row.status!='draft'" size="mini" @click="handleModifyStatus(row,'draft')">
                        起草
                    </el-button>
                    <el-button v-if="row.status!='deleted'" size="mini" type="danger"
                               @click="handleModifyStatus(row,'deleted')">
                        删除
                    </el-button>
                </template>
            </el-table-column>

        </el-table>

        <div class="pagination-container">
            <el-pagination
                    background
                    layout="prev, pager, next, jumper"
                    :total="total_num"
                    :current-page="current_page"
                    :page-size="per_page"
                    @current-change="currentChangeEvent"
            >
            </el-pagination>
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
                is_init_list: true,
                form: {},
                tableKey: 0,
                listQuery: {
                    importance: '',
                    title: '',
                    type: '',
                    sort: '+id'
                },
                importanceOptions: [1, 2, 3],
                calendarTypeOptions: [
                    {key: 'CN', display_name: 'China'},
                    {key: 'US', display_name: 'USA'},
                    {key: 'JP', display_name: 'Japan'},
                    {key: 'EU', display_name: 'Eurozone'}
                ],
                sortOptions: [{label: 'ID 升序', key: '+id'}, {label: 'ID 降序', key: '-id'}],
                statusOptions: ['published', 'draft', 'deleted'],
            },
            watch: {},
            filters: {
                parseTime: function (time, format) {
                    return Ztbcms.formatTime(time, format)
                },
                statusFilter: function (status) {
                    var statusMap = {
                        published: 'success',
                        draft: 'info',
                        deleted: 'danger'
                    }
                    return statusMap[status]
                },
            },
            methods: {
                GetList: function () {
                    //模拟
                    var new_list = [];
                    for (var i = 0; i < 20; i++) {
                        var item = {
                            id: i,
                            timestamp: Date.now() + i,
                            author: '小明',
                            reviewer: '大明',
                            title: '这里是文章',
                            content_short: '这里是内容简洁',
                            content: '这里是内容内容内容内容内容内容内容内容内容内容',
                            forecast: '100',
                            importance: '2',
                            type: 'CN',
                            status: 'published',//published', 'draft', 'deleted'
                            display_time: '2019-1-1',
                            comment_disabled: true,
                            pageviews: '1000',
                            image_uri: 'https://picsum.photos/200',
                            platforms: ['a-platform'],
                            link: 'https://baidu.com?v=' + i,
                        }
                        new_list.push(item);
                    }
                    this.handRes({
                        data: new_list,
                        current_page: 1,
                        last_page: 20,
                        per_page: 20,
                        total: 400
                    })
                },
                handleFilter: function () {
                    this.listQuery.page = 1
                    this.getList()
                },
                sortChange: function (data) {
                    var order = data.order
                    var prop = data.prop
                    if (prop === 'id') {
                        this.sortByID(order)
                    }
                },
                sortByID: function (order) {
                    if (order === 'ascending') {
                        this.listQuery.sort = '+id'
                    } else {
                        this.listQuery.sort = '-id'
                    }
                    this.handleFilter()
                },
                getSortClass: function (key) {
                    const sort = this.listQuery.sort
                    return sort === `+${key}`
                        ? 'ascending'
                        : sort === `-${key}`
                            ? 'descending'
                            : ''
                },
                handleModifyStatus: function (row, status) {
                    this.$message({
                        message: '操作Success',
                        type: 'success'
                    })
                    row.status = status
                },
                //以窗口形式打开链接
                openArticleLink: function (url) {
                    layer.open({
                        type: 2,
                        title: '编辑',
                        content: url,
                        area: ['60%', '70%'],
                    })
                },
                // 跳转查看阅读此文章的用户列表
                linkToList: function (id) {
                    var url = '{:api_url("/Admin/Logs/loginLogList")}' + '?id=' + id;
                    Ztbcms.openNewIframeByUrl('用户列表', url)
                }
            },
            mounted: function () {
            },

        })
    })
</script>
