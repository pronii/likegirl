# LikeGirl 情侣小站

LikeGirl 是一个基于 PHP + MySQL 的情侣记录网站，用来展示恋爱主页、文章记录、留言、恋爱清单、相册图片、视频和背景音乐。项目包含前台展示页面和后台管理系统，适合部署在 phpStudy、宝塔面板、Apache 或 Nginx 环境中。

当前仓库已经清理掉旧的测试脚本、临时文档和未使用的后台演示脚本，`README.md` 是项目内保留的主要说明文档。

## 主要功能

### 前台页面

- `index.php`：网站首页，展示情侣信息、恋爱计时和入口导航。
- `little.php` / `page.php`：点点滴滴文章列表和文章详情。
- `leaving.php`：留言板，支持访客留言展示。
- `loveImg.php`：恋爱相册，支持图片、视频、灯箱预览和左右切换。
- `list.php`：恋爱清单，展示待完成和已完成事项。
- `about.php`：关于我们页面，使用 BotUI 对话式展示。

### 媒体能力

- 图片相册：支持相册分类、分页加载、图片预览和懒加载。
- 视频播放：使用自定义播放器，支持播放/暂停、进度条、左右切换、全屏和键盘操作。
- 视频缩略图：支持视频封面和缩略图管理。
- 背景音乐：后台可维护音乐列表，前台通过接口加载播放列表。

### 后台管理

后台入口：`/admin/`

- 基础设置：站点标题、情侣昵称、背景图、恋爱时间、备案和版权信息。
- 文章管理：新增、编辑、删除点滴文章。
- 留言管理：查看、删除留言，维护留言设置和违禁词。
- 相册管理：新增图片/视频、修改媒体信息、分类管理、批量操作、恢复图片。
- 批量上传：支持后台批量添加本地图片和上传图片。
- 音乐管理：维护音乐列表和音乐接口配置。
- 恋爱清单：维护恋爱事项列表。
- 备份管理：创建、查看、下载、上传、恢复和删除备份。
- IP 管理：记录访问 IP，维护黑名单。
- 安全设置：修改管理员信息、安全码和相关配置。
- 自定义设置：维护自定义 CSS、页头代码、页脚代码和 Pjax 开关。

## 技术栈

- 后端：PHP、MySQL
- 前端：HTML、CSS、JavaScript、jQuery、Pjax
- 后台 UI：Bootstrap 风格后台模板
- 富文本编辑：Editor.md
- 图片预览：Spotlight
- 消息提示：Toastr
- 对话展示：BotUI

建议环境：

- PHP 7.2 或更高版本
- MySQL 5.7 或更高版本
- Apache 或 Nginx

## 项目结构

```text
.
├── admin/                  后台管理系统
│   ├── assets/             后台 CSS、JS、图片、字体等资源
│   ├── editormd/           Markdown 富文本编辑器资源
│   ├── Config_DB.php       数据库连接和安全码配置
│   ├── connect.php         数据库连接入口
│   ├── Database.php        数据库封装
│   ├── Function.php        后台公共函数
│   ├── index.php           后台首页
│   ├── login.php           后台登录页
│   ├── loveImgSet.php      相册媒体管理
│   ├── musicSet.php        音乐管理
│   ├── backupManager.php   备份管理
│   └── ...                 其他后台功能页面和处理接口
├── api/                    前台接口
│   ├── getMusicList.php    音乐列表接口
│   └── image_cache.php     图片缓存接口
├── Botui/                  BotUI 对话组件
├── Style/                  前台静态资源
│   ├── css/                前台样式
│   ├── img/                默认图片资源
│   ├── js/                 前台脚本
│   │   ├── loveAlbum/      相册模块脚本
│   │   ├── music-player.js 音乐播放器
│   │   └── videoPlayer*.js 视频播放器
│   ├── jquery/             jQuery
│   ├── pagelir/            图片灯箱资源
│   └── toastr/             消息提示资源
├── uploads/                用户上传内容目录
│   ├── images/             原图
│   ├── thumbs/             图片缩略图
│   ├── videos/             视频文件
│   └── video_thumbs/       视频缩略图
├── backups/                本地备份目录
├── logs/                   日志目录
├── phpMyAdmin4.8.5/        随项目保留的 phpMyAdmin
├── index.php               前台首页
├── loveImg.php             恋爱相册页
├── leaving.php             留言页
├── list.php                恋爱清单页
├── little.php              点滴文章列表
├── page.php                点滴文章详情
├── about.php               关于我们页
├── getPhotos.php           相册媒体接口
├── getAlbums.php           相册分类接口
├── transferPhotos.php      图片迁移/转移处理
├── install_complete.sql    完整数据库初始化脚本
├── .htaccess               Apache 访问和缓存配置
└── README.md               项目说明
```

## 安装部署

1. 克隆或上传项目到网站根目录。

   ```bash
   git clone https://github.com/pronii/likegirl.git
   ```

2. 创建 MySQL 数据库，例如 `likegirl`。

3. 导入数据库脚本：

   ```text
   install_complete.sql
   ```

4. 修改数据库配置：

   ```php
   // admin/Config_DB.php
   $db_address = "localhost";
   $db_username = "root";
   $db_password = "root";
   $db_name = "likegirl";
   $Like_Code = "请改成自己的安全码";
   ```

5. 确保这些目录可写：

   ```text
   uploads/images/
   uploads/thumbs/
   uploads/videos/
   uploads/video_thumbs/
   backups/
   logs/
   ```

6. 访问网站：

   ```text
   前台：http://你的域名/
   后台：http://你的域名/admin/
   ```

默认后台账号来自 `install_complete.sql`：

```text
用户名：admin
密码：love2025
```

首次部署后请立即修改后台密码和 `admin/Config_DB.php` 中的安全码。

## 数据库表

`install_complete.sql` 当前包含这些主要表：

| 表名 | 用途 |
| --- | --- |
| `text` | 网站基础信息 |
| `article` | 点滴文章 |
| `leaving` | 留言数据 |
| `leavSet` | 留言设置 |
| `lovelist` | 恋爱清单 |
| `loveImg` | 相册图片和视频 |
| `love_album` | 相册分类 |
| `music` | 音乐列表 |
| `music_api_config` | 音乐接口配置 |
| `about` | 关于页面内容 |
| `login` | 管理员账号 |
| `diySet` | 自定义代码和样式 |
| `IPerror` | IP 黑名单 |
| `warning` | 安全日志 |

## 上传和备份

- 用户上传内容位于 `uploads/`，其中图片、视频和缩略图默认不提交到 Git。
- 后台备份文件位于 `backups/`，默认不提交到 Git。
- 访问日志 `ip.txt` 默认不提交到 Git。
- 调试、测试、诊断类临时文件已在 `.gitignore` 中忽略。

## 维护说明

- 项目主配置文件是 `admin/Config_DB.php`。
- 前台公共头部和底部分别是 `head.php`、`footer.php`。
- 相册前台核心逻辑在 `Style/js/loveAlbum/`。
- 自定义视频播放逻辑在 `Style/js/videoPlayer.js`、`Style/js/videoPlayerCustom.js` 和相册模块中配合使用。
- 后台仍在使用的演示命名脚本只有 `admin/assets/js/pages/demo.datatable-init.js` 和 `admin/assets/js/pages/demo.toastr.js`，其他未引用的后台 demo 脚本已删除。

## 许可和声明

本项目保留原 LikeGirl 项目版权声明，适合学习、交流和自用部署。请勿将项目以任何形式倒卖，二次开发时请保留必要的版权信息。
