### 安装

php 版本要求 7.2或以上

### 入口

- 后台入口：http://ztbcms.ztbweb.cn/admin/Login/index
- 安装向导：http://ztbcms.ztbweb.cn/install/index/index (重新安装请删除`tp6/app/install/install.lock`)


### 配置

拷贝`tp6/.example.env`配置到`tp6/.env`开启应用配置

`config/app.php`开启错误提示
```shell script
// 显示错误信息
'show_error_msg'   => true,
```

后台主要服务

类名 | 说明 | 
:----------- | :-----------: |
admin/RbacService        |     角色权限服务    |   
admin/ModuleService      |     模块服务，模块安装、卸载    |   


### Controller

- 管理后台请继承AdminController(只有继承这个AdminController才会自动启用session)
- 非管理后台请集成BaseController



## 状态码说明

- 200 正常
- 400 错误返回
- 401 未登录授权
- 403 禁止访问，没有权限
- 404 找不到资源

### 页面操作

NOTE: 请直接参考后台首页或文件`ztbcms.js`

1.打开新窗口
```js
//方法1. 封装后再调用
window.openNewIframe = function (title, url) {
    if (parent.window != window) {
        parent.window.__adminOpenNewFrame({
            title: title,
            url: url
        })
    } else {
        window.location.href = url;
    }
}.bind(this)

//调用
window.openNewIframe('标题','http://baidu.com');


//方法2.直接调用(兼容性差)

parent.window.__adminOpenNewFrame({
    title: '标题',
    url: 'http://baidu.com'
})

//方法3 底层实现方法,使用事件触发
var event = new CustomEvent('adminOpenNewFrame', {
  detail: {
    title: '启动父窗口1', 
    router_path: '/a/b/c', 
    url: 'http://baidu.com'
  }
})
window.parent.dispatchEvent(event)
```


2.刷新指定页面（一般很少用）

```js
var event = new CustomEvent('adminRefreshFrame', {
  detail: {
    refreshView: {
      name:'路由的name',
      meta:{
        url: "/index.php?g=Admin&m=Adminmanage&a=chanpass&menuid=6"
      },
    }
  }
})
window.parent.dispatchEvent(event)
```

3. 图标配置

到iconfont.cn选取icon,用的是svg
![图片](https://dn-coding-net-production-pp.codehub.cn/c02721e8-2d56-4407-8e59-8101e6f3fe1b.png)

在dashborad.php 引入js
![图片](https://dn-coding-net-production-pp.codehub.cn/8b6dea28-655d-4dc0-9525-848ab9452635.png)

设置菜单的icon
![图片](https://dn-coding-net-production-pp.codehub.cn/f856614b-fcbe-40f6-9f47-b332c34852dd.png)

拓展：ztbcms默认后台icon已经内置，请打开`/statics/css/iconfont/demo_index.html`查看



