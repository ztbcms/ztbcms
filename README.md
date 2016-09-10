
<p align="center"><a href="http://ztbcms.com" target="_blank"><img width="100" src="favicon.ico"></a></p>
 
# 文档

- 在线文档http://ztbcms.com

----
# 环境要求
* PHP版本需要5.4+ 推荐5.6(未支持PHP 7)
    * php5-curl 
    * php5-gd
* Mysql 5.5+
    * mysql-client
* Apache 2.2(推荐2.4) 

# 简介 

* 基于ThinkPHP框架开发，采用独立分组的方式开发的内容管理系统
* 支持模块安装/卸载，模型自定义，整合UCenter通行证等
* 同时系统对扩展方面也支持比较大，可以使用内置的行为控制，对现有功能进行扩展

## 根据安装程序安装好后，进入后台需要进行如下操作：

* 更新站点缓存。
* 进入 内容 -> 批量更新URL 更新地址
* 进入 内容 -> 批量更新栏目页 进行生成栏目页
* 进入 内容 -> 批量更新内容页 进行生成内容页
* 进入 内容 -> 批量更新内容页 进行生成内容页
* 进入 模块 -> 搜索配置 -> 重建索引 进行搜索数据的重建

# 下载安装


下载最新**稳定版**
```shell
$ git clone --branch master https://github.com/Zhutibang/ZtbCMS.git
```

下载最新**开发版**(慎用)
```shell
$ git clone https://github.com/Zhutibang/ZtbCMS.git
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

# ZtbCMS 开发Tips

## 模块

CMS没有预装任何模块，一般需要到用户系统的应该先安装会员中心模块。不需要的模块可以手动删除

## 自定义success、error页面

举例（以自定义Content模块）： 
1. 在需要自定的模块中`Conf/config.php`中添加

```
'TMPL_ACTION_ERROR' => APP_PATH . 'Content/View/error.php', // 默认错误跳转对应的模板文件
'TMPL_ACTION_SUCCESS' => APP_PATH . 'Content/View/success.php', // 默认成功跳转对应的模板文件
```

2. 然后在Content模块中的View中新增`error.php`和`success.php`


## 更换后台iconfont

CMS采用了http://www.iconfont.cn/上提供的iconfont,可以现在该网站生成iconfont字体后替换
`/statics/css/default_iconfont.css`内容

