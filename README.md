# LikeGirl v5.2.2-Stable - 情侣小站

<p align="center">
  <img src="https://s1.locimg.com/2024/11/07/84df7db1ca34c.webp" alt="LikeGirl Preview" width="100%">
</p>

> 🌟 愿得一人心，白首不相离 | 记录爱情点滴，留住美好瞬间

---

## 📖 项目简介

LikeGirl（情侣小站）是一款开源的情侣记录网站，专为情侣设计，用于记录恋爱中的美好时刻。项目采用 PHP + MySQL 开发，前端使用 jQuery + Pjax 实现无刷新加载，提供流畅的用户体验。

**v5.2.2-Stable 是本项目的当前版本（2026年6月1日发布）**

---

## ✨ 功能特性

### 前台功能

| 功能模块 | 说明 |
|---------|------|
| 🏠 **首页** | 展示情侣头像、恋爱计时器、功能卡片导航 |
| 📝 **点点滴滴** | 文章/日记记录，支持 HTML 富文本 |
| 💬 **留言板** | 访客留言祝福，自动获取QQ头像昵称 |
| 📸 **恋爱相册** | 图片展示，支持分页加载、灯箱预览 |
| 💕 **恋爱列表** | 情侣约定事件清单，支持完成状态标记 |
| 🤖 **关于我们** | BotUI 机器人对话式展示 |
| 🎨 **全局快捷** | 返回顶部、首页、开源地址快捷按钮 |

### 后台功能

| 功能模块 | 说明 |
|---------|------|
| 👤 **基础设置** | 修改情侣昵称、网站标题、背景图等 |
| 📝 **文章管理** | 添加、编辑、删除文章 |
| 💬 **留言管理** | 查看、删除留言，设置违禁词 |
| 📸 **相册管理** | 添加、修改、删除相册图片，支持批量操作 |
| 💕 **恋爱清单** | 管理恋爱事件列表 |
| 🤖 **关于设置** | 配置关于页面对话内容 |
| 🔒 **安全设置** | 修改密码、安全码 |
| 🎨 **自定义** | 自定义CSS、页头页脚代码 |
| 🚫 **IP管理** | IP黑名单管理 |

---

## 🛠️ 技术栈

- **后端**: PHP 7.4+ / MySQL 5.7+
- **前端**: jQuery 3.x + Pjax（无刷新加载）
- **UI组件**:
  - [BotUI](https://botui.org/) - 机器人对话界面
  - [Toastr](https://github.com/CodeSeven/toastr) - 弹窗提示
  - [NProgress](https://ricostacruz.com/nprogress/) - 进度条
  - [Spotlight.js](https://github.com/nicholasruggeri/spotlight) - 图片灯箱
  - [FunLazy](https://github.com/nicholasruggeri/funlazy) - 懒加载
  - [Animate.css](https://animate.style/) - CSS动画

---

## 📦 安装部署

### 环境要求

- PHP >= 7.4
- MySQL >= 5.7
- Apache/Nginx Web服务器

### 安装步骤

1. **下载项目**
   ```bash
   git clone https://gitee.com/kiCode111/like-girl-v5.2.0.git
   ```

2. **导入数据库**
   - 使用 phpMyAdmin 或 MySQL 命令行导入 `love_db.sql`

3. **配置数据库连接**
   - 编辑 `admin/Config_DB.php` 文件：
   ```php
   $db_address = "localhost";      // 数据库地址
   $db_username = "your_username"; // 数据库用户名
   $db_password = "your_password"; // 数据库密码
   $db_name = "lovey";            // 数据库名
   $Like_Code = "your_code";      // 安全码（请设置复杂）
   ```

4. **设置目录权限**
   - 确保网站目录有读写权限

5. **访问网站**
   - 前台：`http://your-domain/`
   - 后台：`http://your-domain/admin/`
   - 默认账号：`admin` / `love2025`

---

## 🗂️ 项目结构

```
LikeGirl/
├── admin/                  # 后台管理
│   ├── assets/            # 后台资源文件
│   ├── editormd/          # EditorMD编辑器
│   ├── Config_DB.php      # 数据库配置
│   ├── connect.php        # 数据库连接
│   ├── Database.php       # 数据库预处理连接
│   ├── Function.php       # 自定义函数
│   ├── login.php          # 登录页面
│   ├── index.php          # 后台首页
│   └── ...                # 其他后台功能文件
├── Botui/                  # BotUI组件
├── Style/                  # 前端资源
│   ├── css/               # 样式文件
│   ├── js/                # JavaScript文件
│   ├── img/               # 图片资源
│   ├── Font/              # 字体图标
│   ├── jquery/            # jQuery库
│   ├── pagelir/           # Spotlight组件
│   └── toastr/            # Toastr组件
├── index.php              # 前台首页
├── little.php             # 点点滴滴（文章列表）
├── page.php               # 文章详情页
├── leaving.php            # 留言板
├── about.php              # 关于我们
├── loveImg.php            # 恋爱相册
├── list.php               # 恋爱列表
├── head.php               # 公共头部
├── footer.php             # 公共底部
├── ip.php                 # IP记录
├── ipjc.php               # IP检测
├── love_db.sql            # 数据库SQL文件
└── README.md              # 项目说明
```

---

## 📊 数据库结构

| 表名 | 说明 |
|------|------|
| `text` | 网站基本信息（标题、情侣昵称、背景图等） |
| `article` | 文章/日记内容 |
| `leaving` | 留言数据 |
| `lovelist` | 恋爱事件列表 |
| `loveImg` | 恋爱相册图片 |
| `about` | 关于页面配置 |
| `login` | 管理员登录信息 |
| `diySet` | 自定义设置（CSS、页头页脚） |
| `leavSet` | 留言设置（违禁词、截取长度） |
| `IPerror` | IP黑名单 |
| `warning` | 安全日志 |

---

## 🔧 主要配置

### 修改网站信息

登录后台 → 基础设置，可修改：
- 男女主角昵称
- 网站标题和Logo
- 恋爱开始时间
- 首页背景图片
- 网站备案号和版权信息

### 修改密码

登录后台 → 安全设置，需要输入**安全码**才能修改：
- 管理员密码
- 安全码本身

### 自定义样式

登录后台 → 自定义设置：
- **页头代码**: 添加自定义 `<head>` 内容
- **页脚代码**: 添加自定义 `</body>` 前内容
- **自定义CSS**: 添加全局样式
- **Pjax开关**: 启用/禁用无刷新加载
- **高斯模糊**: 启用/禁用背景模糊效果

---

## 📄 更新日志

### v5.2.2-Stable (2026-06-01)
- 新增相册图片批量操作功能（批量删除、批量转移相册）
- 优化相册管理界面，增加批量选择面板
- 批量添加图片功能优化：图片描述字段改为非必填项
- 修复留言管理删除链接缺少等号的 bug
- 优化相册加载日志输出，规范代码格式
- 图片查询接口（getPhotos.php）新增返回 id 字段

### v5.2.1-Stable (2024-11-07)
- 新增全局快捷功能（返回首页、返回顶部、开源地址入口）
- 恋爱事件未完成项标题色弱化处理
- 相册升级分页加载与动态加载更多功能
- 导航栏文案过长时自动触发 ToolTip 提示
- 留言板新增游客身份标识

### v5.2.0
- 全新UI界面设计
- 优化移动端适配
- 新增Pjax无刷新加载

---

## ⚠️ 项目声明

1. **本项目完全免费**，禁止以任何形式出售
2. 可用于学习交流，欢迎二次开发
3. 请保留版权信息（前端页面底部）
4. 开源地址：[Gitee](https://gitee.com/kiCode111/like-girl-v5.2.0)

---

## 📧 联系作者

- **作者**: Ki
- **QQ**: 3439780232
- **邮箱**: mail@kikiw.cn
- **博客**: [blog.kikiw.cn](https://blog.kikiw.cn)

---

## 🙏 致谢

感谢所有支持 LikeGirl 项目的用户，愿每一对情侣都能白首不相离。

---

<p align="center">
  <img src="https://s1.locimg.com/2024/11/07/9ab5cd34a4e5d.webp" alt="LikeGirl" width="80%">
</p>