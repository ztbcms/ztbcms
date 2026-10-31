# 文档

`docs/` 是这份仓库的文档入口。框架手册和项目手册分开放，不要混在同一层。

| 目录 | 放什么 | 谁来改 |
|---|---|---|
| [framework/](./framework/README.md) | 框架手册。换一个项目仍然成立的内容 | 随框架升级，项目不要改 |
| [project/](./project/README.md) | 当前这个系统的文档。功能说明、操作手册、部署、运维 | 随业务迭代 |

判断只看一条：这篇离开当前部署还成立吗？成立进 `framework/`，不成立进 `project/`。

业务模块自己的说明放模块目录（如 `tp6/app/{模块}/README.md`），不要再抄一份到这里。

## 旧路径

文件已从 `docs/` 根目录迁入 `framework/`。旧链接对照：

| 旧路径 | 新路径 |
|---|---|
| `docs/GetStart.md` | [framework/get-started/GetStart.md](./framework/get-started/GetStart.md) |
| `docs/Faq.md` | [framework/get-started/Faq.md](./framework/get-started/Faq.md) |
| `docs/ModuleDevelopment.md` | [framework/architecture/ModuleDevelopment.md](./framework/architecture/ModuleDevelopment.md) |
| `docs/Menu.md` | [framework/architecture/Menu.md](./framework/architecture/Menu.md) |
| `docs/KVComponent.md` | [framework/components/KVComponent.md](./framework/components/KVComponent.md) |
| `docs/LockComponent.md` | [framework/components/LockComponent.md](./framework/components/LockComponent.md) |
| `docs/QueueComponent.md` | [framework/components/QueueComponent.md](./framework/components/QueueComponent.md) |
| `docs/CronComponent.md` | [framework/components/CronComponent.md](./framework/components/CronComponent.md) |
| `docs/DownloaderComponent.md` | [framework/components/DownloaderComponent.md](./framework/components/DownloaderComponent.md) |
| `docs/UPLOAD_GUIDE.md` | [framework/components/UPLOAD_GUIDE.md](./framework/components/UPLOAD_GUIDE.md) |
| `docs/backend.md` | [framework/dev-guides/backend.md](./framework/dev-guides/backend.md) |
| `docs/RouteGuide.md` | [framework/dev-guides/RouteGuide.md](./framework/dev-guides/RouteGuide.md) |
| `docs/MigrateGuide.md` | [framework/dev-guides/MigrateGuide.md](./framework/dev-guides/MigrateGuide.md) |
| `docs/ThinkCommand.md` | [framework/dev-guides/ThinkCommand.md](./framework/dev-guides/ThinkCommand.md) |
| `docs/frontend.md` | [framework/frontend/frontend.md](./framework/frontend/frontend.md) |
