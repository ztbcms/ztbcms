# 4. 前端页面手册（增强版）

## 4.1 适用范围

本文面向后台页面开发，重点覆盖本项目最常见的页面场景：列表、弹窗、表单、上传、权限按钮、页签。

- 页面整体仍以当前项目现有写法为准
- 优先复用已有公共能力，不额外引入新交互体系
- 新页面尽量做到结构统一、交互统一、返回处理统一

## 4.2 页面基础约定

### 页面结构

后台页面推荐统一放在 `#app` 容器内，并使用 `el-card` 包裹主体内容。

```html
<div id="app" v-cloak>
    <el-card>
        页面内容
    </el-card>
</div>
```

推荐约定：

- 页面根节点加 `v-cloak`
- 操作区、筛选区、表格区、分页区分块清晰
- 新增和编辑尽量复用同一份表单页
- 页面加载完成后再拉取首屏数据

## 4.3 列表页标准写法

### 推荐结构

一个完整列表页，通常包含四块：

1. 筛选区
2. 工具按钮区
3. 表格区
4. 分页区

最小结构示例：

```html
<el-row :gutter="10" style="margin-bottom: 15px;">
    <el-col :span="24">
        <el-button type="primary" size="small" @click="handleAdd">新增</el-button>
        <el-button size="small" @click="getList">刷新</el-button>
    </el-col>
</el-row>

<el-table :data="list" highlight-current-row style="width: 100%;">
    <el-table-column label="名称" prop="name"></el-table-column>
    <el-table-column label="操作" width="180">
        <template slot-scope="{row}">
            <el-button type="text" size="mini" @click="handleEdit(row.id)">编辑</el-button>
            <el-button type="text" size="mini" @click="handleDelete(row.id)">删除</el-button>
        </template>
    </el-table-column>
</el-table>
```

### 列表页建议

- 查询条件命名统一，和后端字段保持一致
- 刷新列表统一走 `getList()`
- 删除、启停、批量处理等操作成功后，统一刷新列表
- 树形列表、懒加载列表，优先参考现有地区管理页写法

## 4.4 弹窗场景

### 什么时候用弹窗

优先用弹窗的场景：

- 新增/编辑字段较少
- 只需要做一次短操作
- 操作完成后需要回到原列表继续处理

推荐用法：

```js
layer.open({
    type: 2,
    title: '编辑数据',
    content: url,
    area: ['700px', '550px'],
    end: function () {
        that.getList();
    }
});
```

### 什么时候不要用弹窗

以下场景更适合新页签：

- 表单很长
- 需要多块内容协同编辑
- 需要频繁切换详情、日志、配置子页

## 4.5 表单场景

### 推荐结构

表单页统一使用 `el-form`、`rules`、`submitForm` 这套结构。

```html
<el-form ref="elForm" :model="formData" :rules="rules" label-width="120px">
    <el-form-item label="名称" prop="name">
        <el-input v-model="formData.name" placeholder="请输入名称"></el-input>
    </el-form-item>
    <el-form-item>
        <el-button type="primary" @click="submitForm">保存</el-button>
        <el-button @click="closeWindow">取消</el-button>
    </el-form-item>
</el-form>
```

```js
submitForm: function () {
    var that = this;
    that.$refs.elForm.validate(function (valid) {
        if (!valid) return;

        that.httpPost(url, that.formData, function (res) {
            if (res.status) {
                that.$message.success(res.msg || '保存成功');
            } else {
                that.$message.error(res.msg || '保存失败');
            }
        });
    });
}
```

### 表单建议

- 新增和编辑尽量共用一份页面
- 编辑时先拉详情，再回填数据
- 规则写在 `rules`，不要把所有提示都塞进提交时判断
- 保存成功后，要么关闭弹窗并刷新父页面，要么跳回列表，不要停在半完成状态

## 4.6 上传场景

### 常见场景

项目里已有上传面板、图库分组、上传设置、前台上传接口，不要重复造轮子。

常见上传类型：

- 单图上传
- 多图上传
- 附件上传
- 富文本图片上传

### 组件写法

最小上传示例：

```html
<el-upload
    :action="uploadConfig.uploadUrl"
    :accept="uploadConfig.accept"
    :on-success="handleUploadSuccess"
    :on-error="handleUploadError"
    :show-file-list="false">
    <el-button type="primary" size="small">点击上传</el-button>
</el-upload>
```

成功处理建议：

```js
handleUploadSuccess: function (res) {
    if (res.status) {
        this.$message.success('上传成功');
    } else {
        this.$message.error(res.msg || '上传失败');
    }
}
```

### 上传注意事项

- 上传前先确认允许的类型、大小、数量
- 页面真正要展示的是访问地址，不是内部保存路径
- 上传完成后要把结果写回表单或列表，不要只提示成功
- 多图场景要处理好新增、删除、排序、回显

## 4.7 权限按钮

### 基本原则

- 有权限才显示按钮
- 没权限时优先直接不显示，而不是显示后点了再报错
- 页面按钮权限应与后端接口权限保持一致

现有页面常见写法：

```php
<?php if (\app\admin\service\AdminUserService::getInstance()->hasPermission('common', 'cron.dashboard', 'addOrEditCron')){ ?>
    <el-button @click="createCron" type="primary" size="mini">新增任务</el-button>
<?php } ?>
```

使用建议：

- 列表顶部按钮做一次权限判断
- 行内操作按钮也要单独判断
- 删除、导出、审核这类高风险操作不要漏权限控制

## 4.8 页签与页面跳转

### 推荐用法

本项目后台打开新页签，优先使用：

```js
Ztbcms.openNewIframeByUrl('标题', url);
```

说明：

- 在后台框架内会打开新页签
- 脱离后台框架时会直接跳转当前页面

### 打开新页签示例

```js
openTaskLog: function () {
    Ztbcms.openNewIframeByUrl('任务日志', "{:api_url('/common/cron.dashboard/cronLog')}");
}
```

### 刷新指定页面

需要刷新指定页签时，可继续使用现有事件方式：

```js
var event = new CustomEvent('adminRefreshFrame', {
    detail: {
        refreshView: {
            name: '路由的name',
            meta: {
                url: '/index.php?g=Admin&m=Adminmanage&a=chanpass&menuid=6'
            }
        }
    }
});
window.parent.dispatchEvent(event);
```

### 使用建议

- 新增、编辑、日志、详情等独立页面，优先开新页签
- 简单录入、短表单优先弹窗
- 不再推荐继续新增底层事件式打开方式，能用 `Ztbcms.openNewIframeByUrl` 就直接用

## 4.9 页签场景

页面需要按分类、状态、资源类型切换内容时，可使用 `el-tabs`。

最小示例：

```html
<el-tabs value="1">
    <el-tab-pane :label="'Redis 链接（' + total + '）'" name="1"></el-tab-pane>
</el-tabs>
```

适用场景：

- 附件管理中区分图片、视频、文件
- 概览页切不同资源类型
- 详情页切基础信息、日志、配置

使用建议：

- 页签名称要让人一眼知道内容差异
- 切换页签时统一重新加载当前页签数据
- 不要把互不相关的功能硬塞进同一组页签

## 4.10 常见页面组合

### 列表 + 弹窗编辑

适用于轻量增删改，操作完成后回到原列表继续处理。

### 列表 + 新页签详情

适用于详情内容较多、需要继续向下操作的页面。

### 详情 + 子页签

适用于一个对象下挂多类信息，例如基础信息、日志、附件、配置。

选择原则：

- 内容短、动作短，用弹窗
- 内容长、结构复杂，用新页签
- 同一个对象下多块内容切换，用页签

## 4.11 图标配置

图标配置仍沿用现有做法：

1. 到 `iconfont.cn` 选取图标
2. 在对应后台页面引入图标脚本
3. 在菜单或页面中设置图标

补充说明：

- ztbcms 默认后台图标已内置，可查看 `/statics/css/iconfont/demo_index.html`
- 替换 icon 后，请同步检查 `/admin/Iconfont/index` 页面内容

## 4.12 开发检查清单

页面完成前，至少逐项检查：

- 首屏是否能正常打开
- 列表是否能正确加载、刷新、分页
- 弹窗是否能打开、关闭、回刷父页面
- 表单是否能校验、提交、回显
- 上传是否能成功并正确回填
- 权限按钮是否符合当前账号权限
- 新页签打开是否正常
- 页签切换后数据是否正确刷新
- 成功、失败、空数据三类场景是否都验证过
