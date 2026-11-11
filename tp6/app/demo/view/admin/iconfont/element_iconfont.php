<div id="app" style="padding: 8px;" v-cloak>
    <el-card>
        <h3>Element UI 自带图标</h3>
        <p>用法：<code>&lt;i class="el-icon-edit"&gt;&lt;/i&gt;</code>，点击图标可复制类名。更多说明见
            <el-link type="primary" href="https://element.eleme.cn/2.15/#/zh-CN/component/icon" target="_blank"
                     rel="noopener noreferrer">Element UI 官方文档</el-link>
        </p>
        <el-input v-model="keyword" placeholder="搜索图标" clearable style="width: 240px;margin-bottom: 12px;"></el-input>
        <div class="icon-list">
            <div class="icon-item" v-for="name in filteredIcons" :key="name" @click="copyName(name)">
                <i :class="name"></i>
                <span>{{ name }}</span>
            </div>
        </div>
    </el-card>
</div>

<style>
    .icon-list {
        display: flex;
        flex-wrap: wrap;
    }

    .icon-item {
        width: 160px;
        height: 90px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #606266;
        cursor: pointer;
        border-radius: 4px;
    }

    .icon-item:hover {
        color: #409EFF;
        background: #f5f7fa;
    }

    .icon-item i {
        font-size: 24px;
        margin-bottom: 8px;
    }

    .icon-item span {
        font-size: 12px;
    }
</style>

<script>
    $(document).ready(function () {
        new Vue({
            el: '#app',
            data: {
                keyword: '',
                icons: []
            },
            computed: {
                filteredIcons: function () {
                    var keyword = this.keyword.trim()
                    return this.icons.filter(function (name) {
                        return !keyword || name.indexOf(keyword) !== -1
                    })
                }
            },
            methods: {
                // 从已加载的 Element UI 样式表中读取图标类名，避免硬编码或依赖外部站点
                loadIcons: function () {
                    var names = {}
                    var reg = /^\.(el-icon-[a-z0-9-]+)::?before$/
                    Array.prototype.forEach.call(document.styleSheets, function (sheet) {
                        var rules
                        try {
                            rules = sheet.cssRules
                        } catch (e) {
                            return // 跨域样式表无法读取，跳过
                        }
                        Array.prototype.forEach.call(rules || [], function (rule) {
                            (rule.selectorText || '').split(',').forEach(function (selector) {
                                var match = selector.trim().match(reg)
                                if (match) {
                                    names[match[1]] = true
                                }
                            })
                        })
                    })
                    this.icons = Object.keys(names).sort()
                },
                copyName: function (name) {
                    var that = this
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(name).then(function () {
                            that.$message.success('已复制：' + name)
                        })
                    } else {
                        that.$message.info(name)
                    }
                }
            },
            mounted: function () {
                this.loadIcons()
            },
        })
    })
</script>
