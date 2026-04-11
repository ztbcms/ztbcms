---
name: release-log-generator
description: Generate release notes/changelog from Git history. Use when the user asks to generate 发布日志、版本日志、更新日志、release notes、changelog, especially when the workflow should start from the most recent commit whose subject matches `Release vX.X.X` and summarize later commits into `Changes` and `Fixes` sections.
---

# 发布日志生成

使用此工作流根据 Git 提交生成版本发布日志。

## 工作流程

1. 确认当前目录是 Git 仓库。
2. 查找最近一条提交标题匹配 `Release vX.X.X` 的提交，例如：
   `git log --format='%H%x09%s' --grep='Release v[0-9]\+\.[0-9]\+\.[0-9]\+' -n 1`
3. 以这条发布提交作为分界，只统计它之后到当前最新提交的记录，使用 `<release_commit>..HEAD`。
4. 读取这段范围内的提交标题，优先使用旧到新的顺序，例如：
   `git log --reverse --format='%s' <release_commit>..HEAD`
5. 跳过明显不应进入发布日志的内容：
   - `Merge ...`
   - 再次出现的 `Release vX.X.X`
   - 空标题
6. 对提交标题去重，并按规则拆分到 `Changes` 和 `Fixes`。
7. 以当天日期作为标题，按固定 Markdown 模板直接返回最终结果。

## 分类规则

- `Fixes`：提交标题以 `fix`、`hotfix`、`bugfix` 开头，或包含“修复”“修正”“修补”等含义时，归入这里。
- `Changes`：除上述情况外，其余提交统一归入这里。
- 如果提交标题符合 `type(scope): summary`，优先转换为 `- scope: summary`。
- 如果提交标题本身已经带有模块或路径前缀，例如 `Dreaming/memory-wiki: xxx`，直接保留该结构。
- 如果无法提取模块名，直接输出 `- 原始标题的主要内容`。
- 保持从旧到新的顺序，便于阅读。

## 输出规范

- 只输出最终 Markdown，不要添加解释、分析过程或命令。
- 始终使用下面这个骨架：

```markdown
## yyyy.mm.dd

### Changes

- ...

### Fixes

- ...
```

- 将 `yyyy.mm.dd` 替换为当前日期。
- 某一组没有内容时，写 `- 无`。
- 不要输出 commit hash、作者、分支名、命令结果。

## 失败处理

- 如果找不到匹配 `Release vX.X.X` 的最近提交，明确说明未找到发布标记，无法按既定规则生成。
- 如果找到了发布标记，但之后没有新提交，仍按模板输出，并在 `Changes` 与 `Fixes` 下都写 `- 无`。

## 示例

输入范围内的提交标题示例：

```text
feat(api): 增加短信登录
fix(auth): 修复 token 过期判断
Dreaming/memory-wiki: support search alias
Merge branch 'main' into develop
```

输出示例：

```markdown
## 2026.04.11

### Changes

- api: 增加短信登录
- Dreaming/memory-wiki: support search alias

### Fixes

- auth: 修复 token 过期判断
```
