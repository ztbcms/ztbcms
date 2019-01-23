
<p align="center"><a href="http://ztbcms.com" target="_blank"><img width="100" src="favicon.ico"></a></p>
 
[在线文档](http://ztbcms.com)

<a href="http://www.phptherightway.com">
    <img src="http://www.phptherightway.com/images/banners/leaderboard-728x90.png" alt="PHP: The Right Way"/>
</a>

## 环境要求

* PHP版本需要5.4+ 推荐5.6
    * php5-curl 
    * php5-gd
    * php5-mysql
* Mysql 5.5+
    * mysql-client
* Apache 2.2(推荐2.4) 
* 可选的配置URL重写，参考[ThinkPHP - URL重写](http://document.thinkphp.cn/manual_3_2.html#url_rewrite)

## 下载安装

下载最新**稳定版**
```shell
$ git clone --branch master https://github.com/ztbcms/ztbcms.git
```

下载最新**开发版**(慎用)
```shell
$ git clone https://github.com/ztbcms/ztbcms.git
```

初始化环境,详情请看[Makefile](Makefile)

```shell
#修改权限
$ make setup-env
```

删除多余的`dev.gitignore`
```shell
$ rm dev.ignore
```

## 版本描述

版本含有4部分, 如`1.2.3.4`, 采用`MAJOR.MINOR.FEATURE.PATCH`来描述版本

- MAJOR 大版本号,代码被大量重写,有大部分不兼容的更新
- MINOR 有重要的核心结构改变,可能会导致部分第三方不兼容
- FEATURE  有新特性/扩展加入或更新
- PATCH bug修复

## 状态码说明

- 200 正常
- 400 错误返回
- 401 未登录授权
- 403 禁止访问，没有权限
- 404 找不到


### 页面操作

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
```

2.刷新指定页面（一般很少用）

```
const event = new CustomEvent('adminRefreshFrame', {
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
