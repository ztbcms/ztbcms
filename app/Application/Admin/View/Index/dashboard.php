<extend name="../../Admin/View/Common/element_layout"/>

<block name="header">
    <link href=/statics/admin/pages/dashboard/static/css/reset.css rel=stylesheet>
</block>

<block name="content">

    <div id="app" v-cloak>
        <!-- 导航 -->
        <div class="sidebar-container" ref="nav">
            <el-row class="tac">
                <el-col class="scroll-Y">
                    <el-menu
                            default-active="2"
                            class="el-menu-vertical-demo"
                            text-color="rgb(191, 203, 217)"
                            active-text-color="#409eff"
                            :collapse="isnavHid"
                            :default-active="tagData.defaultActive"
                            :class="{hid : minHid}"
                    >
                        <div v-for="(item, index) in navData" :key="item.id">
                            <el-menu-item v-if="item.items.length === 0" :index="item.id" @click="goUrl([item.name], item)">
                                <i class="iconfont el-icon-location"></i>
                                <span slot="title">{{item.name}}</span>
                            </el-menu-item>
                            <el-submenu v-else :index="item.id">
                                <template slot="title">
                                    <i class="iconfont el-icon-location"></i>
                                    <span>{{item.name}}</span>
                                </template>
                                <div v-for="(val, j) in item.items" :key="val.id">
                                    <el-menu-item v-if="val.items.length === 0" :index="val.id" @click="goUrl([item.name, val.name], val)">
                                        <span slot="title">{{val.name}}</span>
                                    </el-menu-item>
                                    <el-submenu :index="val.id" v-else>
                                        <template slot="title">
                                            <span>{{val.name}}</span>
                                        </template>
                                        <el-menu-item v-for="(sub, v) in val.items" :key="sub.id" :index="sub.id" @click="goUrl([item.name, val.name, sub.name], sub)">
                                            <span slot="title">{{sub.name}}</span>
                                        </el-menu-item>
                                    </el-submenu>
                                </div>
                            </el-submenu>
                        </div>
                    </el-menu>
                </el-col>
            </el-row>
        </div>
        <!-- 内容 -->
        <div class="centent" :class="{hid : !isnavHid}">
            <div class="navbar">
                <div class="left">
                    <div class="nav">
                        <i class="el-icon-s-fold navicon" @click="navShow()" :class="{navShow : isnavHid}"></i>
                        <el-breadcrumb separator="/">
                            <el-breadcrumb-item v-for="(item, index) in tagData.breadcrumb" :key="index">{{item}}
                            </el-breadcrumb-item>
                        </el-breadcrumb>
                    </div>
                    <el-dropdown>
                        <span class="el-dropdown-link">
                            admin（超级管理员）<i class="el-icon-arrow-down el-icon--right"></i>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item>设置</el-dropdown-item>
                            <el-dropdown-item>退出</el-dropdown-item>
                        </el-dropdown-menu>
                    </el-dropdown>
                </div>
                <div class="tag" ref="tagScroll">
                    <div class="list" ref="tagList">
                        <div
                                class="tag-item"
                                :class="{show : item.url === iframeUrl}"
                                v-for="(item, index) in tags"
                                :key="item.name"
                                :ref="'tag-' + index"
                                @click="tagClick(item, index)"
                                @contextmenu.prevent="openMenu($event, item)"
                        >
                            <div class="chebox"></div>
                            {{item.name}}
                            <i class="el-icon-close" @click.stop="closeTag(item, index)"></i>
                        </div>
                    </div>
                </div>
                <ul class="contextmenu" ref="contextmenu">
                    <li @click="urlRefresh">刷新</li>
                    <li @click="closeTag(temData)">关闭</li>
                    <li @click="urlOther">关闭其他</li>
                    <li @click="endAll">关闭所有</li>
                </ul>
            </div>
            <section class="app-main">
                <iframe :src="iframeUrl" frameborder="0" class="iframe" id="iframe" ref="iframe"></iframe>
            </section>
        </div>
        <div class="mask" v-show="!minHid" @click="maskClick"></div>
    </div>

    <style>
        [v-cloak]{
            display: none;
        }

        #app {
            /* display: flex; */
        }

        .sidebar-container {
            transition: .23s;
            width: max-content;
            height: 100%;
            position: fixed;
            font-size: 0;
            top: 0;
            bottom: 0;
            left: 0px;
            z-index: 1001;
            overflow: hidden;
            background: rgb(48, 65, 86);
            z-index: 11;
        }

        .el-dropdown-link {
            cursor: pointer;
        }

        .sidebar-container .tac {
            position: relative;
            height: 100%;
            z-index: 2;
        }

        .sidebar-container .el-col {
            height: 100%;
        }

        .scroll-Y {
            overflow-y: auto;
            overflow-x: hidden;
        }

        .scroll-Y::-webkit-scrollbar {
            display: none
        }

        .sidebar-container .el-menu {
            border-right: 0;
            background: inherit;
        }

        .el-menu-vertical-demo:not(.el-menu--collapse) {
            width: 180px;
            min-height: 400px;
        }

        .sidebar-container .el-menu-item:hover,
        .sidebar-container .el-submenu__title:hover,
        .el-menu-item:hover {
            background-color: #001528 !important;
        }

        .centent {
            position: relative;
            height: 100%;
            margin-left: 36px;
            flex: 1;
            transition: .23s ease-in;
            z-index: 10;
        }

        .centent .navicon {
            transition: .23s;
            transform: rotate(0deg);
            cursor: pointer;
        }

        .centent .navShow {
            transform: rotate(-90deg);
        }

        .centent .el-breadcrumb__inner {
            color: #333 !important;
        }

        .centent .el-breadcrumb__item:last-of-type .el-breadcrumb__inner {
            color: #97a8be !important;
        }

        .el-submenu .el-menu--inline {
            background-color: #1f2d3d;
        }

        .el-submenu {
            transition: .23s;
        }

        .el-menu-item:focus {
            background-color: initial;
        }

        .el-submenu__title {
            overflow: hidden;
        }

        .el-menu--collapse {
            width: 36px;
        }
        .el-menu--collapse.hid{
            width: 0px;
        }

        .el-menu--collapse .el-submenu__title>span,
        .el-menu--collapse .el-submenu__title .el-icon-arrow-right {
            height: 0;
            width: 0;
            overflow: hidden;
            visibility: hidden;
            display: inline-block;
        }

        .el-menu--collapse .el-menu-item {
            padding: 0 !important;
        }

        .el-menu--collapse .el-submenu__title,
        .el-menu--collapse .el-tooltip {
            padding-left: 10px !important;
        }

        .el-menu--collapse .el-submenu__title:hover {
            background-color: rgb(38, 52, 69) !important;
        }

        .el-menu--popup-right-start {
            background-color: #304156;
        }

        .el-submenu__title:focus,
        .el-submenu__title:hover {
            background-color: #001528 !important;
        }

        .navbar {
            width: 100%;
            height: 85px;
            border-bottom: 1px solid #d8dce5;
            background: #fff;
        }

        .navbar .left {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 0 10px;
        }

        .iconfont {
            /* width: 15px; */
        }

        .navbar .left .nav {
            display: flex;
            align-items: center;
            height: 50px;
        }

        .navbar .tag {
            margin-left: 10px;
            overflow-x: auto;
        }

        .navbar .list {
            display: flex;
            width: max-content;
        }

        .navbar .tag::-webkit-scrollbar {
            height: 4px;
        }

        .navbar .tag::-webkit-scrollbar-thumb {
            border-radius: 10px;
            background-color: rgba(0, 0, 0, 0);
            transition: 1s;
        }

        .navbar .tag:hover::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, .2);
        }

        .navbar .tag .el-tag {
            cursor: pointer;
            margin-right: 10px;
        }

        .navbar .el-icon-s-fold {
            font-size: 26px;
            margin-right: 10px;
        }

        .navbar .right {
            margin: 10px 20px 0;
            cursor: pointer;
        }

        .app-main {
            height: calc(100% - 86px);
            background: #fff;
        }

        .iframe {
            width: 100%;
            height: 100%;
        }

        .sidebar-container.hid {
            width: 0px;
            /* left: -180px; */
        }

        .mask {
            position: fixed;
            background-color: rgba(0, 0, 0, .2);
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
        }

        .centent.hid {
            margin-left: 180px;
        }

        .tag-item {
            display: inline-flex;
            position: relative;
            cursor: pointer;
            height: 26px;
            border: 1px solid #d8dce5;
            color: #495060;
            background: #fff;
            padding: 0 8px;
            font-size: 12px;
            margin-left: 5px;
            margin-top: 4px;
            border-radius: 4px;
            align-items: center;
        }
        .tag-item.show{
            background-color: #2196f3;
            color: #fff;
            border-color: #2196f3;
        }
        .tag-item.show .chebox{
            display: inline-block;
        }
        .tag-item .chebox{
            display: none;
            width: 8px;
            height: 8px;
            background: #fff;
            border-radius: 50%;
            margin-right: 8px;
        }
        .tag-item .el-icon-close{
            width: 15px;
            height: 15px;
            text-align: center;
            line-height: 15px;
            font-size: 12px;
            border-radius: 50%;
            margin-left: 8px;
            transition: .3s;
        }
        .tag-item .el-icon-close:hover{
            background-color: #b4bccc;
            color: #fff;
        }

        .contextmenu {
            display: none;
            position: fixed;
            margin: 0;
            background: #fff;
            z-index: 100;
            position: absolute;
            list-style-type: none;
            padding: 5px 0;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 400;
            color: #333;
            -webkit-box-shadow: 2px 2px 3px 0 rgba(0, 0, 0, .3);
            box-shadow: 2px 2px 3px 0 rgba(0, 0, 0, .3);
        }

        .contextmenu li {
            margin: 0;
            padding: 7px 16px;
            cursor: pointer;
            min-width: 80px;
        }

        .contextmenu li:hover {
            background: #eee;
        }

        @media screen and (max-width: 1024px) {
            .centent {
                margin-left: 0 !important;
                display: block;
            }
            .mask {
                z-index: 10 !important;
            }
        }

        @media screen and (max-width: 1200px) {
            .centent {
                margin-left: 36px;
            }
            .sidebar-container {
                left: 0;
            }
            .mask {
                z-index: -10;
            }
        }
    </style>
</block>


<block name="footer">
    <script>
        var app = new Vue({
            el: '#app',
            data: {
                isnavHid: false,
                minHid: false,
                windowSize: 1920,
                navData: [],
                iframeUrl: 'http://sha.hb.ztbweb.cn/index.php?m=Main&menuid=1',
                tags: [
                    // {name: '概括', url: 'http://sha.hb.ztbweb.cn/index.php?m=Main&menuid=1', breadcrumb: ['概括'], defaultActive: '1Admin'}
                ],
            },
            methods: {
                // url点击事件
                goUrl: function(breadcrumb, data) {
                    var that = this
                    this.iframeUrl = data.url
                    // 判断tags里面是否包含了当前点击的url
                    var tagIndex = this.tags.findIndex(function(item) {
                        return item.url === data.url
                    })
                    if (tagIndex === -1) {
                        // 把点击的导航添加到tag
                        this.tags.push({
                            name: data.name,
                            url: data.url,
                            breadcrumb,
                            defaultActive: data.id
                        })
                        setTimeout(function() {
                            var tagList = that.$refs.tagList
                            that.setTagScroll(tagList.clientWidth)
                        }, 100)
                        // 判断是否来自小屏的点击
                        if (this.windowSize <= 1024) {
                            this.minHid = true
                            this.isnavHid = true
                        }
                    } else {
                        // 获取tag当前位置的offsetLeft值，并且赋值到导航条上
                        var tag = this.$refs['tag-' + tagIndex]
                        if (tag) {
                            tag = tag[0]
                            var container = this.$refs.tagScroll
                            container.scrollTo(tag.$el.offsetLeft - 10, 0)
                        }
                    }
                },
                // 点击tag
                tagClick: function(data, index) {
                    if(data){
                        this.iframeUrl = data.url
                    } else {
                        this.iframeUrl = ''
                    }
                },
                // tag删除事件
                closeTag: function(data) {
                    var index = this.tags.findIndex(function(item) {
                        return item.url === data.url
                    })
                    if (this.tags.length > 1) {
                        this.tags.splice(index, 1)
                        // 判断当前显示的页面是不是要被删除的页面
                        if (this.iframeUrl === data.url) {
                            // tag定位到前一个
                            this.tagClick(this.tags[index-1], index-1)
                            var tagList = this.$refs.tagList
                            this.setTagScroll(tagList.clientWidth)
                        }
                    }
                },
                setTagScroll: function(x) {
                    var container = this.$refs.tagScroll
                    container.scrollTo(x, 0)
                },
                // 弹出左侧导航icon,小屏和大屏区分操作
                navShow: function() {
                    this.isnavHid = !this.isnavHid
                    if (this.windowSize <= 1024) {
                        this.minHid = !this.minHid
                    }
                },
                // 点击左侧菜单弹出时的浮层，关闭左侧菜单
                maskClick: function () {
                    this.minHid = !this.minHid
                    this.isnavHid = !this.isnavHid
                },
                // 判断当前浏览器的大小做左边导航的适配
                onresize: function() {
                    var clientWidth = document.documentElement.clientWidth
                    if (clientWidth <= 1024) {
                        this.minHid = true
                        this.isnavHid = true
                        this.windowSize = 1024
                    } else if (clientWidth <= 1200) {
                        this.minHid = false
                        this.isnavHid = true
                        this.windowSize = 1200
                    } else {
                        this.isnavHid = false
                        // this.minHid = false
                        this.windowSize = 1920
                    }
                },
                // 右键弹出菜单
                openMenu: function(e, data) {
                    this.temData = data
                    var navWidth = this.$refs.nav.offsetWidth
                    var pageX = e.pageX - navWidth
                    var pageY = e.pageY
                    this.$refs.contextmenu.style.display = 'block'
                    this.$refs.contextmenu.style.left = pageX + 'px'
                    this.$refs.contextmenu.style.top = pageY + 'px'
                },
                // 刷新
                urlRefresh: function() {
                    var that = this
                    var iframeUrl = this.iframeUrl
                    this.$refs.iframe.src = ''
                    setTimeout(function(el) {
                        that.$refs.iframe.src = iframeUrl
                    }, 50)
                },
                // 关闭其他
                urlOther: function() {
                    var index = this.tags.findIndex(function(item){
                        return item.url === this.temData.url
                    })
                    this.iframeUrl = this.tags[index].url
                    this.tags = [this.tags[index]]
                },
                // 关闭全部
                endAll: function() {
                    location.reload()
                },
                // 初始化
                init: function(){
                    var that = this
                    window.onresize = function() {
                        that.onresize()
                    }
                    // 关闭tag上下文菜单
                    document.onclick = function(){
                        that.$refs.contextmenu.style.display = 'none'
                    }
                    // tag的滚动事件
                    var container = this.$refs.tagScroll
                    let isFF = /FireFox/i.test(navigator.userAgent)
                    if (!isFF) {
                        container.addEventListener("mousewheel", function(e) {
                            let v = -e.wheelDelta / 2
                            container.scrollLeft += v
                            e.preventDefault()
                        }, false)
                    } else {
                        container.addEventListener("DOMMouseScroll", function(e) {
                            container.scrollLeft += e.detail * 80
                            e.preventDefault()
                        }, false)
                    }
                },


                // 获取菜单
                getMenuList() {
                    var that = this
                    $.ajax({
                        url: '/Admin/AdminApi/getPermissionInfo',
                        method: 'get',
                        params: {}
                    }).then(function(res) {
                        console.log(res)
                        if (res.status) {
                            // this.addRoleAccessList(res.data.roleAccessList)
                            // this.addMenus(res.data.menuList)
                            // that.navData = res.data.menuList
                            var menu = [];
                            var _menuList = res.data.menuList
                            for(var i=0;i<_menuList.length;i++){
                                _menuList[i]['icon_html'] = ' <i class="iconfont icon-'+_menuList[i]['icon']+'"></i>'
                            }
                            that.navData = _menuList
                            if(_menuList && _menuList[0]){
                                var menu1 = _menuList[0]
                                if(menu1['items'].length === 0){
                                    that.goUrl([menu1['name']], menu1)
                                } else {
                                    var menu2 = menu1['items'][0]
                                    if(menu2['items'].length === 0){
                                        that.goUrl([menu1['name'], menu2['name']], menu2)
                                    } else {
                                        var menu3 = menu2['items'][0]
                                        if(menu2['items'].length === 0){
                                            that.goUrl([menu1['name'], menu2['name'], menu3['name']], menu3)
                                        }
                                    }
                                }
                            }

                        } else {
                            layer.msg(res.msg)
                        }
                    })
                },
                //注册时间
                registerEvent() {
                    window.addEventListener('adminOpenNewFrame', this.handleEvent_adminOpenNewFrame.bind(this))
                    window.addEventListener('adminRefreshFrame', this.handleEvent_adminRefreshFrame.bind(this))
                    window.__adminOpenNewFrame = function(config) {
                        var title = config.title || ''
                        var router_path = '/' + md5(config.url)
                        var url = config.url || ''
                        var event = new CustomEvent('adminOpenNewFrame', {
                            detail: {
                                title: title,
                                router_path: router_path,
                                url: url
                            }
                        })
                        window.parent.dispatchEvent(event)
                    }
                },
                unregisterEvent() {
                    window.__adminOpenNewFrame = null
                    window.removeEventListener('adminOpenNewFrame', this.handleEvent_adminOpenNewFrame.bind(this))
                    window.removeEventListener('adminRefreshFrame', this.handleEvent_adminRefreshFrame.bind(this))
                },
                handleClickOutside() {
                    this.$store.dispatch('closeSideBar', { withoutAnimation: false })
                },
                // 获取管理员信息
                getAdminUserInfo() {
                    $.ajax({
                        url: '/Admin/AdminApi/getAdminUserInfo',
                        method: 'get',
                        params: {}
                    }).then(function(res){
                        if (res.status) {
                            // this.$store.commit('SET_ROLES', [res.data.role_id])
                            // this.$store.commit('SET_NAME', res.data.nickname)
                            // this.$store.commit('SET_AVATAR', res.data.avatar)
                            // this.$store.commit('SET_LOGIN_USER_INFO', res.data)
                        } else {
                            layer.msg(res.msg)
                        }
                    })
                },
                /**
                 * 打开新窗口
                 * @param title 标题
                 * @param router_path 路由(相对路径)
                 * @param url 对应的URL
                 */
                openNewFrame(title, router_path, url) {

                },
                // 打开新窗口
                handleEvent_adminOpenNewFrame(event) {
                    var title = event.detail.title || ''
                    var router_path = event.detail.router_path || ''
                    var url = event.detail.url || ''
                    this.openNewFrame(title, router_path, url)
                    //{name: data.name, url: data.url, breadcrumb, defaultActive: data.id}
                    this.openNewFrame([event.detail.title], {
                        name: event.detail.name,
                        url: event.detail.url,
                        id: event.detail.url,
                    })
                },
                // 刷新窗口
                handleEvent_adminRefreshFrame(event) {
                    var refreshView = event.detail.refreshView
                    console.log('handleEvent_adminRefreshFrame', refreshView)
                    if (refreshView) {
                        document.getElementById(refreshView.name).src = refreshView.meta.url
                    }
                }
            },
            computed: {
                tagData: function() {
                    var that = this
                    var data = this.tags.find(function(item) {
                        return item.url === that.iframeUrl
                    }) || {}
                    return {
                        breadcrumb: data.breadcrumb,
                        defaultActive: data.defaultActive
                    }
                }
            },
            created () {
                this.onresize()
            },
            mounted () {
                this.init()
                this.registerEvent()
                this.getMenuList()
                this.getAdminUserInfo()
            },
            unmount: function(){
                this.unregisterEvent()
            }
        })
    </script>
</block>
