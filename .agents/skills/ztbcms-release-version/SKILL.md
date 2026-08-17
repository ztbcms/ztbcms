---
name: ztbcms-release-version
description: 用于发布 ztbcms 版本（基于 Git Flow 流程）。当用户要求发布 ztbcms 版本（例如"发布版本 x.y.z"、"发版 x.y.z"、"release x.y.z"、"发布 ztbcms x.y.z"等）时触发。
---

# ztbcms 版本发布工作流

用于根据 Git Flow 规范自动化执行 ztbcms 的版本发布流程。

## 触发条件

用户输入包含以下意图时触发：
- 发布版本 `x.y.z`（例如 `1.0.0`、`4.11.0` 等）
- 发版 / Release `vx.y.z` 或 `x.y.z`

---

## 详细执行流程

### 步骤 0：前置检查

在开始发版前，逐一检查以下条件，任一不满足则停止并提示用户：

1. 确认 Git 工作区干净（`git status --porcelain` 为空），否则提示用户先暂存或提交修改。
2. 确认 `develop` 和 `master` 分支存在。
3. 确认本地不存在 `release/x.y.z` 分支（`git branch --list release/x.y.z` 为空）。
4. 确认 Tag `vx.y.z` 不存在（`git tag -l vx.y.z` 为空），避免覆盖已有发布标签。
5. 确认 `changelog/` 目录存在（不存在则自动创建）。

---

### 步骤 1：切换并更新 develop 分支，检出 release 分支

1. 切换到 `develop` 分支并拉取最新代码：
   ```bash
   git checkout develop
   git pull
   ```
2. 从 `develop` 检出并切换到发布分支 `release/x.y.z`：
   ```bash
   git checkout -b release/x.y.z
   ```

---

### 步骤 2：在 release/x.y.z 分支上进行发版准备

#### 2.1 更新系统配置文件 `tp6/config/admin.php`
- `cms_build`：更新为当前时间戳，格式为 12 位数字 `yyyymmddHHmm`（示例：`202607210255`），不含秒。
- `cms_version`：更新为当前目标发布版本号 `x.y.z`（示例：`1.0.0`，不带前缀 `v`）。

#### 2.2 生成版本 Changelog
1. 在项目根目录 `changelog/` 目录下新建 `x.y.z.md` 文件。
2. 查找最近一条发版提交作为起点：
   ```bash
   git log --format='%H%x09%s' --extended-regexp --grep='Release v[0-9]+\.[0-9]+\.[0-9]+' -n 1
   ```
3. 获取距离上个发布版本以来的所有提交标题（按时间从旧到新排序）：
   ```bash
   git log --reverse --format='%s' <last_release_commit>..HEAD
   ```
   > 若未找到历史发布提交，则回退为获取所有历史提交。
4. 过滤与清理提交：
   - 跳过以下提交：
     - `Merge branch ...`、`Merge pull request ...` 等所有 merge commit（包括 Git Flow 产生的 `Merge branch 'release/...'`、`Merge branch 'master' into develop` 等）
     - `Release v...` 发版提交
     - 空标题
   - 过滤重复内容。
5. 按分类归纳到 `Changes` 和 `Fixes`：
   - **`Fixes`**：提交标题以 `fix`、`hotfix`、`bugfix` 开头，或包含"修复"、"修正"、"修补"等含义。
   - **`Changes`**：其余提交归入此类。
   - **格式规范**：
     - 若符合 `type(scope): summary`，提取并转换为 `- scope: summary`。
     - 若已有模块/路径前缀（如 `admin: xxx` 或 `Dreaming/memory-wiki: xxx`），保留该结构。
     - 若无法提取模块名，直接输出 `- 原始标题的主要内容`。
6. 文件内容必须严格遵循以下 Markdown 骨架：

```markdown
# {替换为版本号}

发布日期：{替换为日期，格式 yyyy-mm-dd}

## Changes

- ...

## Fixes

- ...
```

> **注意**：
> - 某一组没有内容时，写 `- 无`。
> - 只输出纯净的 Changelog 内容，不要输出 commit hash、作者、分支名或命令结果。

#### 2.3 提交发版文件
将修改的文件暂存并提交，提交信息固定为 `Release vx.y.z`：
```bash
git add tp6/config/admin.php changelog/x.y.z.md
git commit -m "Release vx.y.z"
```

---

### 步骤 3：合并到 master 分支

1. 切换到 `master` 分支并拉取最新代码：
   ```bash
   git checkout master
   git pull
   ```
2. 将 `release/x.y.z` 分支合并到 `master`：
   ```bash
   git merge --no-ff release/x.y.z -m "Merge branch 'release/x.y.z'"
   ```

---

### 步骤 4：打 Tag

在 `master` 分支的合并节点上创建带版本号的 Tag：
```bash
git tag -a vx.y.z -m "Release vx.y.z"
```

---

### 步骤 5：合并回 develop 分支并收尾

1. 切换回 `develop` 分支：
   ```bash
   git checkout develop
   ```
2. 将 `release/x.y.z` 合并回 `develop` 分支：
   ```bash
   git merge --no-ff release/x.y.z -m "Merge branch 'release/x.y.z' into develop"
   ```
3. 删除本地 `release/x.y.z` 分支（已合并到 master 和 develop，不再需要）：
   ```bash
   git branch -d release/x.y.z
   ```
4. 提示用户已完成本地发版流程，并给出推送命令供用户确认后执行：
   ```bash
   # 推送 master、develop 分支和新创建的 tag
   git push origin master develop --tags
   ```

---

## 异常处理

- **合并冲突**：在合并到 `master` 或 `develop` 时若产生冲突，暂停流程并明确指出冲突文件，协助用户解决冲突后再继续。
