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
                                <i v-html="item.icon_html"></i>
                                <span slot="title">{{item.name}}</span>
                            </el-menu-item>
                            <el-submenu v-else :index="item.id">
                                <template slot="title">
                                    <i v-html="item.icon_html"></i>
                                    <span class="title">{{item.name}}</span>
                                </template>
                                <div v-for="(val, j) in item.items" :key="val.id">
                                    <el-menu-item v-if="val.items.length === 0" :index="val.id" @click="goUrl([item.name, val.name], val)">
                                        <template slot="title">
                                            <span class="title">{{val.name}}</span>
                                        </template>
                                    </el-menu-item>
                                    <el-submenu :index="val.id" v-else>
                                        <template slot="title">
                                            <span class="title">{{val.name}}</span>
                                        </template>
                                        <el-menu-item v-for="(sub, v) in val.items" :key="sub.id" :index="sub.id" @click="goUrl([item.name, val.name, sub.name], sub)">
                                            <template slot="title">
                                                <span class="title">{{sub.name}}</span>
                                            </template>
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

                    <div class="el-dropdown">
                        <el-popover
                                placement="bottom"
                                width="400"
                                trigger="manual"
                                v-model="showMsg"
                        >
                            <div v-for="item in msgList" style="margin-bottom: 10px;" @click="readMsg(item.id)" >
                                <span>
                                    <i class="el-icon-s-opportunity" style="color: red;" v-if="item.read_status == 0"></i>
                                    <span>{{item.content | ellipsis}}</span>
                                </span>
                            </div>
                            <div style="text-align: center" @click="toShowMsg">
                                <p style="color:#000;" >查看所有通知 >></p>
                            </div>

                            <div slot="reference" @click="doShowMsg">
                                    <span style="position: relative">
                                        <svg style="width: 1.6rem;height: 1.6rem;" t="1594714631615" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1137" width="200" height="200"><path d="M889.92 749.728c-1.184-1.632-119.232-166.144-119.232-287.392 0-168.448-76.16-254.784-162.688-287.008V160c0-52.928-43.072-96-96-96s-96 43.072-96 96v16.576c-10.784 7.904-16.096 21.792-11.808 35.232 5.376 16.832 23.424 26.048 40.224 20.736 17.76-5.664 36.928-8.544 56.896-8.544h21.376c115.232 0 184 89.088 184 238.304 0 108.032 72.896 235.392 110.176 293.504C759.488 781.984 633.824 832 512 832c-121.504 0-247.424-50.368-304.832-76.64 37.312-57.984 110.144-185.024 110.144-293.056 0-62.432 11.616-114.208 34.528-153.92 8.832-15.328 3.584-34.88-11.712-43.712-15.392-8.864-34.88-3.584-43.712 11.712-28.608 49.536-43.104 112.096-43.104 185.92 0 121.216-118.016 285.248-119.2 286.88a32.13 32.13 0 0 0-5.312 25.952c2.048 8.96 7.872 16.672 16 21.024 5.664 3.072 107.392 57.28 233.536 84.512C399.744 947.072 452.16 992 512 992s112.256-44.928 133.632-111.296c126.112-27.104 227.84-80.992 233.504-84.032 8.128-4.352 14.016-12.032 16.064-21.024s0.128-18.432-5.28-25.92zM501.312 160c-7.264 0-14.208 0.992-21.312 1.568V160c0-17.632 14.368-32 32-32s32 14.368 32 32v0.928a252.138 252.138 0 0 0-21.312-0.928h-21.376zM512 928c-22.336 0-43.136-13.408-57.984-35.296C473.216 894.72 492.608 896 512 896s38.784-1.28 57.984-3.296C555.136 914.624 534.304 928 512 928z" p-id="1138"></path></svg>
                                    </span>
                                    <el-badge class="mark" :value="msgListTotal" v-if="msgListTotal > 0" style="position: absolute;"/>
                                </div>
                        </el-popover>
                    </div>
                    <el-dropdown>
                        <span class="el-dropdown-link" >
                            <template v-if="adminUserInfo && adminUserInfo.name">
                            {{ adminUserInfo.name }}（{{ adminUserInfo.role_name }}）<i class="el-icon-arrow-down el-icon--right"></i>
                            </template>
                        </span>
                        <el-dropdown-menu slot="dropdown">
                            <el-dropdown-item v-if="hasPermission_cleanCache" >
                                <span @click="click_cleancache">清理缓存</span>
                            </el-dropdown-item>
                            <el-dropdown-item>
                                <span @click="click_logout">
                                    注销
                                </span>
                            </el-dropdown-item>
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
                                @click="clickTag(index)"
                                @contextmenu.prevent="openContextMenu($event, item, index)"
                        >
                            <div class="chebox"></div>
                            {{item.name}}
                            <i class="el-icon-close" @click.stop="closeTag(index)"></i>
                        </div>
                    </div>
                </div>
                <ul class="contextmenu" ref="contextmenu">
                    <li @click="refreshTag(tempIndex)">刷新</li>
                    <li @click="closeTag(tempIndex)">关闭本页</li>
                    <li @click="closeOtherTag(tempIndex)">关闭其他</li>
                    <li @click="closeAllTag">关闭所有</li>
                </ul>
            </div>
            <section class="app-main">
                <template v-for="(url, index) in iframeUrls">
                    <iframe :id="'iframe-'+index" :src="url" :key="url" frameborder="0" class="iframe" :style="{display: iframeUrl == url ? 'block': 'none'}"></iframe>
                </template>
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

        .el-submenu__title .title{
            color: rgb(191, 203, 217);
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
            font-size: 16px;
            margin-right: 4px;
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
                // 左侧菜单
                navData: [],
                // 当前iframe内容页
                iframeUrl: '',
                iframeUrls:[],
                // 标签列表
                tags: [
                    // {name: '概括', url: 'http://ztbweb.cn', breadcrumb: ['概括'], defaultActive: '1Admin'}
                ],
                // 单线右键选中项
                temData: {},
                // 单线右键选中项序号
                tempIndex: -1,
                //用户等级
                adminUserInfo: {
                    role_name: '',
                    name: '',
                },
                // 角色权限列表
                roleAccessList: [],
                // 信息列表
                msgList: [],
                showMsg: false,
                msgListTotal: 0
            },
            filters: {
                ellipsis(value) {
                    if (!value) return "";
                    if (value.length > 70) {
                        return value.slice(0, 70) + "...";
                    }
                    return value;
                }
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
                        this.iframeUrls.push(data.url)
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
                clickTag: function(index) {
                    var tag = this.tags[index]
                    if(tag){
                        this.iframeUrl = tag.url
                    } else {
                        this.iframeUrl = ''
                    }
                },
                // tag删除事件
                closeTag: function(index) {
                    var tag = this.tags[index]
                    this.tags.splice(index, 1)
                    this.iframeUrls.splice(index, 1)
                    // 判断当前显示的页面是不是要被删除的页面
                    if (this.iframeUrl === tag.url) {
                        // tag定位到前一个
                        if(index > 0){
                            this.clickTag(index-1)
                        } else {
                            this.iframeUrl = ''
                        }
                        var tagList = this.$refs.tagList
                        this.setTagScroll(tagList.clientWidth)
                    }
                },
                setTagScroll: function(x) {
                    var container = this.$refs.tagScroll
                    container.scrollTo(x, 0)
                },
                // 弹出左侧导航icon,小屏和大屏区分操作
                navShow: function() {
                    this.isnavHid = !this.isnavHid
                    if (this.windowSize <= 960) {
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
                    if (clientWidth <= 960) {
                        this.minHid = true
                        this.isnavHid = true
                        this.windowSize = 960
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
                openContextMenu: function(e, data, index) {
                    this.temData = data
                    this.tempIndex = index
                    var navWidth = this.$refs.nav.offsetWidth
                    var pageX = e.pageX - navWidth
                    var pageY = e.pageY
                    this.$refs.contextmenu.style.display = 'block'
                    this.$refs.contextmenu.style.left = pageX + 'px'
                    this.$refs.contextmenu.style.top = pageY + 'px'
                },
                // 刷新
                refreshTag: function(index = 0) {
                    if(index >= 0){
                        var iframeUrl = this.iframeUrls[index]
                        document.getElementById('iframe-'+index).contentWindow.location.href = iframeUrl
                    }
                },
                // 关闭其他
                closeOtherTag: function() {
                    var that = this
                    if(this.iframeUrl !== this.tags[index].url){
                        this.iframeUrl = this.tags[index].url
                    }
                    this.tags = [this.tags[index]]
                    this.iframeUrls = [this.iframeUrls[index]]
                },
                // 关闭全部
                closeAllTag: function() {
                    this.iframeUrl = ''
                    this.tags = []
                    this.iframeUrls = []
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
                    var isFF = /FireFox/i.test(navigator.userAgent)
                    if (!isFF) {
                        container.addEventListener("mousewheel", function(e) {
                            var v = -e.wheelDelta / 2
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
                        if (res.status) {
                            that.roleAccessList = res.data.roleAccessList
                            var _menuList = res.data.menuList
                            for(var i=0;i<_menuList.length;i++){
                                // 默认icon
                                var icon = _menuList[i]['icon'] || 'dashboard'
                                _menuList[i]['icon_html'] = ' <i class="iconfont icon-'+ icon +'"></i>'
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
                //注册事件
                registerEvent() {
                    window.addEventListener('adminOpenNewFrame', this.handleEvent_adminOpenNewFrame.bind(this))
                    window.__adminOpenNewFrame = function(config) {
                        var title = config.title || ''
                        var url = config.url || ''
                        var event = new CustomEvent('adminOpenNewFrame', {
                            detail: {
                                title: title,
                                url: url
                            }
                        })
                        window.parent.dispatchEvent(event)
                    }
                },
                unregisterEvent() {
                    window.__adminOpenNewFrame = null
                    window.removeEventListener('adminOpenNewFrame', this.handleEvent_adminOpenNewFrame.bind(this))
                },
                // 获取管理员信息
                getAdminUserInfo() {
                    var that = this
                    $.ajax({
                        url: '/Admin/AdminApi/getAdminUserInfo',
                        method: 'get',
                        params: {}
                    }).then(function(res){
                        if (res.status) {
                            that.adminUserInfo.name = res.data.username
                            that.adminUserInfo.role_name = res.data.role_name
                        } else {
                            layer.msg(res.msg)
                        }
                    })
                },
                /**
                 * 打开新窗口
                 * @param title 标题
                 * @param url 对应的URL
                 */
                openNewFrame(title, url) {
                    this.goUrl([title], {
                        name: title,
                        url: url,
                    })
                },
                // 打开新窗口
                handleEvent_adminOpenNewFrame(event) {
                    this.openNewFrame(event.detail.title, event.detail.url)
                },
                /**
                 * 检测是否存在权限
                 * @param access_router 访问的路由，格式必须是：/module/controller/action   (与后台的权限配置时一致的)
                 * @returns {boolean}
                 */
                hasRolePermission: function(access_router) {
                    var roleAccessList = this.roleAccessList

                    var access_router_arr = access_router.split('/')

                    if (access_router_arr.length < 4) {
                        return false
                    }
                    var module = access_router_arr[1]
                    var controller = access_router_arr[2]
                    var action = access_router_arr[3]

                    // 检测 module 是否有权限
                    for (var i = 0; i < roleAccessList.length; i++) {

                        if (roleAccessList[i].app === '%') {
                            return true
                        }
                        if (roleAccessList[i].app === module) {
                            // 检测 controller 是否有权限
                            if (roleAccessList[i].controller === '%') {
                                return true
                            }
                            if (roleAccessList[i].controller === controller) {
                                // 检测 action 是否有权限
                                if (roleAccessList[i].action === '%') {
                                    return true
                                }
                                if (roleAccessList[i].action === action) {
                                    return true
                                }
                            }
                        }
                    }

                    return false
                },
                // 点击清理缓存
                click_cleancache: function(){
                    this.openNewFrame('缓存更新', '/Admin/Index/cache')
                },
                // 点击退出
                click_logout: function(){
                    var that = this
                    $.ajax({
                        url: '/Admin/AdminApi/logout',
                        method: 'get',
                        params: {}
                    }).then(function(res){
                        if (res.status) {
                            layer.msg(res.msg)
                            setTimeout(function(){
                                window.location.replace(res.data.redirect)
                            }, 700)
                        } else {
                            layer.msg(res.msg)
                        }
                    })
                },
                // 获取后台消息数量
                getAdminMessage:function(){
                    var that = this;
                    $.ajax({
                        url: '/AdminMessage/index/getAdminMsgList',
                        method: 'get',
                        data: {
                            page: 1,
                            limit: 6,
                            read_status: 0
                        }
                    }).then(function(res){
                        if (res.status) {
                            that.msgList = res.data.items
                            that.msgListTotal = res.data.total_items
                            if(res.data.total_items > 0){
                            }else{
                                that.showMsg = false;
                            }
                        } else {
                        }
                    })

                    setTimeout(function () {
                        that.getAdminMessage()
                    },10000)
                },
                // 显示消息框
                doShowMsg:function(){
                    if(this.msgListTotal > 0){
                        this.showMsg = !this.showMsg
                    }else{
                        this.showMsg = false;
                    }
                },
                // 已读信息
                readMsg:function (id) {
                    var that = this;
                    $.ajax({
                        url: '/AdminMessage/index/readMsg',
                        method: 'post',
                        data: {
                            id: id,
                        }
                    }).then(function(res){
                        if (res.status) {
                            that.getAdminMessage()
                        } else {
                            console.log(res)
                        }
                    })
                },
                // 跳转到消息列表
                toShowMsg:function () {
                    this.openNewFrame('所有消息', '/AdminMessage/Index/index')
                    this.showMsg = false;
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
                },
                // 是否有清理缓存权限
                hasPermission_cleanCache: function() {
                    return this.hasRolePermission('/Admin/Index/cache')
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
                this.getAdminMessage();
            },
            unmount: function(){
                this.unregisterEvent()
            }
        })
    </script>
</block>
