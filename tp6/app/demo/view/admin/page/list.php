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
            <el-select v-model="listQuery.sort" style="width: 140px" class="filter-item" @change="handleFilter">
                <el-option v-for="item in sortOptions" :key="item.key" :label="item.label"
                           :value="item.key"></el-option>
            </el-select>
            <el-button class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">
                搜索
            </el-button>
            <el-button class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-edit"
                       @click="openEditPage(0)">
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
                    <span v-if="row.pageviews">{{ row.pageviews }}</span>
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
                    <el-button type="primary" size="mini" @click="openEditPage(row.id)">
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
                tableKey: 0,
                listQuery: {
                    page: 1,
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
                data_list: [],
                total_num: 0,
                current_page: 1,
                per_page: 20,
            },
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
                // 获取列表（本地模拟数据，实际项目中替换为 ajax 请求）
                getList: function () {
                    var total = 400
                    var per_page = this.per_page
                    var page = this.listQuery.page
                    var desc = this.listQuery.sort === '-id'
                    var new_list = [];
                    for (var i = 0; i < per_page; i++) {
                        var offset = (page - 1) * per_page + i
                        var id = desc ? total - offset : offset + 1
                        new_list.push({
                            id: id,
                            timestamp: Date.now() - id * 3600 * 1000,
                            author: '小明',
                            reviewer: '大明',
                            title: '这里是文章 ' + id,
                            importance: String(id % 3 + 1),
                            type: 'CN',
                            status: 'published',
                            pageviews: String(id * 10),
                        });
                    }
                    this.handleRes({
                        data: new_list,
                        current_page: page,
                        per_page: per_page,
                        total: total
                    })
                },
                handleRes: function (data) {
                    this.data_list = data.data
                    this.current_page = data.current_page
                    this.per_page = data.per_page
                    this.total_num = data.total
                },
                handleFilter: function () {
                    this.listQuery.page = 1
                    this.getList()
                },
                currentChangeEvent: function (page) {
                    this.listQuery.page = page
                    this.getList()
                },
                sortChange: function (data) {
                    if (data.prop === 'id') {
                        this.listQuery.sort = data.order === 'ascending' ? '+id' : '-id'
                        this.handleFilter()
                    }
                },
                getSortClass: function (key) {
                    var sort = this.listQuery.sort
                    if (sort === '+' + key) return 'ascending'
                    if (sort === '-' + key) return 'descending'
                    return ''
                },
                handleModifyStatus: function (row, status) {
                    this.$message({
                        message: '操作成功',
                        type: 'success'
                    })
                    row.status = status
                },
                // 以弹窗形式打开编辑页
                openEditPage: function (id) {
                    var url = '{:api_url("/demo/admin.page/form")}'
                    if (id) {
                        url += '?id=' + id
                    }
                    layer.open({
                        type: 2,
                        title: id ? '编辑' : '添加',
                        content: url,
                        area: ['60%', '70%'],
                    })
                },
            },
            mounted: function () {
                this.getList()
            },
        })
    })
</script>
