## ZTBCMS

基于 ThinkPHP 的快速开发框架

| 版本 | 框架 | PHP 要求 | 说明 |
|------|------|---------|------|
| `tp6/` | ThinkPHP 6.1.5 | >= 7.4 | 稳定版，生产环境推荐 |
| `tp8/` | ThinkPHP 8.1.4 | >= 8.0 | 新版，开发测试中 |

相关文档：[ThinkPHP 6 文档](https://www.kancloud.cn/manual/thinkphp6_0/) | [ThinkPHP 8 文档](https://doc.thinkphp.cn/v8_0/) | [ThinkTemplate 模板](https://www.kancloud.cn/manual/think-template)

## 主要特性

- 完善的后台管理系统
    - 基于角色的权限管理
    - 计划任务、消息、上传、队列、Redis...
- 完善的前端功能
    - 后台的默认框架 [ElementUI](https://element.eleme.cn/)
    - 强大的弹出层组件 [Layer](https://layer.layui.com/)
- 扩展性强，支持安装卸载模块

## 设计理念

- 延续 ThinkPHP『大道至简』的设计
- 约定大于配置，上手即用
- 页面即使用手册，尽量减少文档的编写

## 环境要求

### ThinkPHP 6 (tp6/)
* PHP >= 7.4（< 8.0 或 >= 8.3）
* MySQL 5.6+
* Apache 2.4、Nginx 1.18

### ThinkPHP 8 (tp8/)
* PHP >= 8.0（推荐 8.1+）
* MySQL 5.7+
* Apache 2.4、Nginx 1.18

* 可选的配置 URL 重写，参考 [ThinkPHP - URL重写](http://document.thinkphp.cn/manual_3_2.html#url_rewrite)
* 建议使用 [宝塔](https://www.bt.cn/?invite_code=MV9xcml5enc=) 来部署

## 快速开始

### ThinkPHP 6

```shell
# 安装依赖
cd tp6 && composer install

# 启动开发服务器
make serve
# 访问 http://localhost:8081/
```

### ThinkPHP 8

```shell
# 安装依赖
cd tp8 && composer install

# 配置环境
cp tp8/.env.example tp8/.env
# 编辑 tp8/.env 填写数据库等配置

# 启动开发服务器
make serve-tp8
# 访问 http://localhost:8082/
```

详细文档见 `docs_tp8/` 目录。

## 下载安装

下载最新**稳定版**
```shell
$ git clone --branch master https://github.com/ztbcms/ztbcms.git
```

下载最新**开发版**(慎用)
```shell
$ git clone --branch develop https://github.com/ztbcms/ztbcms.git
```

下载 **ThinkPHP 8 版本**
```shell
$ git clone --branch tp8 https://github.com/ztbcms/ztbcms.git
```

## License 

[Apache License](LICENSE)

