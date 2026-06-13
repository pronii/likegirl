-- ============================================
-- LikeGirl v5.2.3-Stable 完整安装脚本
-- 版本：v5.2.3-Stable (2026-06-09)
-- 说明：这是一个完整的数据库安装文件，包含所有必需的表和数据
-- 使用方法：
--   1. 创建数据库：CREATE DATABASE likegirl CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
--   2. 选择数据库：USE likegirl;
--   3. 导入此文件
-- ============================================
-- 作者: Ki
-- 项目地址: https://gitee.com/kiCode111/like-girl-v5.2.0
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- ============================================
-- 1. 基础表结构（来自 love_db.sql）
-- ============================================

-- 表的结构 `about`
DROP TABLE IF EXISTS `about`;
CREATE TABLE `about` (
  `id` int(11) NOT NULL,
  `title` varchar(30) NOT NULL COMMENT '标题',
  `aboutimg` varchar(100) NOT NULL COMMENT '背景图片',
  `info1` varchar(50) NOT NULL COMMENT '对话1',
  `info2` varchar(50) NOT NULL COMMENT '对话2',
  `info3` varchar(50) NOT NULL COMMENT '对话3',
  `btn1` varchar(30) NOT NULL COMMENT '按钮确定',
  `btn2` varchar(30) NOT NULL COMMENT '按钮取消',
  `infox1` varchar(30) NOT NULL COMMENT 'x2',
  `infox2` varchar(30) NOT NULL COMMENT 'x2',
  `infox3` varchar(30) NOT NULL COMMENT 'x2',
  `infox4` varchar(30) NOT NULL COMMENT 'x2',
  `infox5` varchar(30) NOT NULL COMMENT 'x2',
  `infox6` varchar(30) NOT NULL COMMENT 'x2',
  `btnx2` varchar(30) NOT NULL COMMENT 'btnx2',
  `infof1` varchar(30) NOT NULL COMMENT 'f3',
  `infof2` varchar(30) NOT NULL COMMENT 'f3',
  `infof3` varchar(30) NOT NULL COMMENT 'f3',
  `infof4` varchar(30) NOT NULL COMMENT 'f3',
  `btnf3` varchar(30) NOT NULL COMMENT 'btnf3',
  `infos1` varchar(30) NOT NULL,
  `infos2` varchar(30) NOT NULL,
  `infos3` varchar(30) NOT NULL,
  `btns4` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `about` (`id`, `title`, `aboutimg`, `info1`, `info2`, `info3`, `btn1`, `btn2`, `infox1`, `infox2`, `infox3`, `infox4`, `infox5`, `infox6`, `btnx2`, `infof1`, `infof2`, `infof3`, `infof4`, `btnf3`, `infos1`, `infos2`, `infos3`, `btns4`) VALUES
(1, '关于我们', 'https://picx.zhimg.com/70/v2-4d82dd1d1c1bbfcb6d52d7e4e2b7e48f_1440w.avis?source=172ae18b&biz_tag=Post', '想知道关于我们的故事吗？', '那就点击下方按钮吧~', '相信你一定会喜欢的！', '当然想！', '算了吧', '你好呀！', '我是小男', '这是我和我女朋友的小站', '记录我们在一起的点点滴滴', '希望你也能找到属于你的那个TA', '祝你幸福~', '好的', '那你知道这个网站有什么功能吗？', '让我来告诉你吧~', '有文章、留言板、相册、恋爱清单等功能', '快去体验一下吧！', '知道啦', '最后，感谢你的访问！', '希望你能喜欢这个网站', '再见啦~', '拜拜');

-- 表的结构 `article`
DROP TABLE IF EXISTS `article`;
CREATE TABLE `article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '文章标题',
  `content` text NOT NULL COMMENT '文章内容',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `update_time` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `article` (`id`, `title`, `content`, `create_time`, `update_time`) VALUES
(1, '我们的第一篇日记', '<p>今天是我们在一起的第一天，很开心能遇见你！</p><p>愿得一人心，白首不相离。</p>', '2024-01-01 00:00:00', '2024-01-01 00:00:00');

-- 表的结构 `diySet`
DROP TABLE IF EXISTS `diySet`;
CREATE TABLE `diySet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `diyhead` text COMMENT '自定义页头代码',
  `diyfooter` text COMMENT '自定义页脚代码',
  `diycss` text COMMENT '自定义CSS',
  `pjax` tinyint(1) DEFAULT 1 COMMENT 'Pjax开关',
  `blur` tinyint(1) DEFAULT 1 COMMENT '高斯模糊开关',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `diySet` (`id`, `diyhead`, `diyfooter`, `diycss`, `pjax`, `blur`) VALUES
(1, '', '', '', 1, 1);

-- 表的结构 `IPerror`
DROP TABLE IF EXISTS `IPerror`;
CREATE TABLE `IPerror` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL COMMENT 'IP地址',
  `reason` varchar(200) DEFAULT NULL COMMENT '封禁原因',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '添加时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 表的结构 `leavSet`
DROP TABLE IF EXISTS `leavSet`;
CREATE TABLE `leavSet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `forbidden` text COMMENT '违禁词（逗号分隔）',
  `max_length` int(11) DEFAULT 200 COMMENT '留言最大长度',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `leavSet` (`id`, `forbidden`, `max_length`) VALUES
(1, '垃圾,傻逼,操你妈', 200);

-- 表的结构 `leaving`
DROP TABLE IF EXISTS `leaving`;
CREATE TABLE `leaving` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qq` varchar(20) NOT NULL COMMENT 'QQ号',
  `nickname` varchar(50) NOT NULL COMMENT '昵称',
  `content` text NOT NULL COMMENT '留言内容',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '留言时间',
  `ip` varchar(50) DEFAULT NULL COMMENT 'IP地址',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `leaving` (`id`, `qq`, `nickname`, `content`, `create_time`, `ip`) VALUES
(1, '10000', '系统消息', '欢迎来到我们的小站，请文明留言哦~', '2024-01-01 00:00:00', '127.0.0.1');

-- 表的结构 `login`
DROP TABLE IF EXISTS `login`;
CREATE TABLE `login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码(MD5)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 默认账号：admin / love2025 (密码已MD5加密)
INSERT INTO `login` (`id`, `username`, `password`) VALUES
(1, 'admin', '4c56ff4ce4aaf9573aa5dff913df997a');

-- 表的结构 `loveImg`
DROP TABLE IF EXISTS `loveImg`;
CREATE TABLE `loveImg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL DEFAULT 1 COMMENT '所属相册ID',
  `imgUrl` varchar(500) NOT NULL COMMENT '图片URL',
  `thumbUrl` varchar(255) DEFAULT NULL COMMENT '缩略图URL',
  `imgtext` varchar(200) DEFAULT NULL COMMENT '图片描述',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '上传时间',
  PRIMARY KEY (`id`),
  KEY `idx_album_id` (`album_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `loveImg` (`id`, `album_id`, `imgUrl`, `thumbUrl`, `imgtext`, `create_time`) VALUES
(1, 1, 'https://picx.zhimg.com/70/v2-4d82dd1d1c1bbfcb6d52d7e4e2b7e48f_1440w.avis', NULL, '我们的第一张照片', '2024-01-01 00:00:00');

-- 表的结构 `lovelist`
DROP TABLE IF EXISTS `lovelist`;
CREATE TABLE `lovelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` varchar(200) NOT NULL COMMENT '事件内容',
  `is_completed` tinyint(1) DEFAULT 0 COMMENT '是否完成 0:未完成 1:已完成',
  `sort_order` int(11) DEFAULT 0 COMMENT '排序',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `lovelist` (`id`, `content`, `is_completed`, `sort_order`, `create_time`) VALUES
(1, '一起看日出', 0, 1, '2024-01-01 00:00:00'),
(2, '一起去旅行', 0, 2, '2024-01-01 00:00:00'),
(3, '一起过生日', 1, 3, '2024-01-01 00:00:00');

-- 表的结构 `text`
DROP TABLE IF EXISTS `text`;
CREATE TABLE `text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `boy_name` varchar(50) NOT NULL COMMENT '男主角昵称',
  `girl_name` varchar(50) NOT NULL COMMENT '女主角昵称',
  `start_time` datetime NOT NULL COMMENT '恋爱开始时间',
  `site_title` varchar(100) NOT NULL COMMENT '网站标题',
  `site_logo` varchar(500) DEFAULT NULL COMMENT '网站Logo',
  `bg_image` varchar(500) DEFAULT NULL COMMENT '背景图片',
  `boy_avatar` varchar(500) DEFAULT NULL COMMENT '男生头像',
  `girl_avatar` varchar(500) DEFAULT NULL COMMENT '女生头像',
  `copyright` varchar(200) DEFAULT NULL COMMENT '版权信息',
  `icp` varchar(100) DEFAULT NULL COMMENT '备案号',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `text` (`id`, `boy_name`, `girl_name`, `start_time`, `site_title`, `site_logo`, `bg_image`, `boy_avatar`, `girl_avatar`, `copyright`, `icp`) VALUES
(1, '小明', '小红', '2024-01-01 00:00:00', 'LikeGirl - 我们的小站', 'https://q1.qlogo.cn/g?b=qq&nk=10000&s=640', 'https://picx.zhimg.com/70/v2-4d82dd1d1c1bbfcb6d52d7e4e2b7e48f_1440w.avis', 'https://q1.qlogo.cn/g?b=qq&nk=10000&s=640', 'https://q1.qlogo.cn/g?b=qq&nk=10001&s=640', 'Copyright © 2024 LikeGirl', '');

-- 表的结构 `warning`
DROP TABLE IF EXISTS `warning`;
CREATE TABLE `warning` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL COMMENT 'IP地址',
  `type` varchar(50) NOT NULL COMMENT '警告类型',
  `content` text COMMENT '警告内容',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '记录时间',
  PRIMARY KEY (`id`),
  KEY `idx_ip` (`ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. 相册功能扩展（来自 love_album.sql）
-- ============================================

DROP TABLE IF EXISTS `love_album`;
CREATE TABLE `love_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_name` varchar(100) NOT NULL COMMENT '相册名称',
  `album_cover` varchar(500) DEFAULT NULL COMMENT '相册封面',
  `album_desc` varchar(200) DEFAULT NULL COMMENT '相册描述',
  `sort_order` int(11) DEFAULT 0 COMMENT '排序',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `love_album` (`id`, `album_name`, `album_desc`, `sort_order`, `create_time`) VALUES
(1, '默认相册', '未分类的照片', 0, NOW());

-- ============================================
-- 3. 音乐播放器功能（来自 migration_add_music.sql）
-- ============================================

DROP TABLE IF EXISTS `music`;
CREATE TABLE `music` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '歌曲名称',
  `artist` varchar(100) DEFAULT NULL COMMENT '艺术家',
  `platform` varchar(20) DEFAULT 'netease' COMMENT '平台',
  `song_id` varchar(50) DEFAULT NULL COMMENT '歌曲ID',
  `music_url` varchar(500) DEFAULT NULL COMMENT '音乐文件URL',
  `cover_url` varchar(500) DEFAULT NULL COMMENT '封面图URL',
  `duration` int(11) DEFAULT 0 COMMENT '时长(秒)',
  `is_enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用 1:启用 0:禁用',
  `sort_order` int(11) DEFAULT 0 COMMENT '排序顺序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_enabled_sort` (`is_enabled`, `sort_order`),
  KEY `idx_song_id` (`song_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='音乐播放列表';

-- ============================================
-- 4. 音乐API配置（来自 migration_music_api_config.sql）
-- ============================================

DROP TABLE IF EXISTS `music_api_config`;
CREATE TABLE `music_api_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'API名称',
  `api_url` varchar(500) NOT NULL COMMENT 'API地址',
  `api_type` varchar(20) NOT NULL COMMENT 'API类型: search/url',
  `is_enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用',
  `sort_order` int(11) DEFAULT 0 COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='音乐API配置';

-- 插入默认API配置
INSERT INTO `music_api_config` (`name`, `api_url`, `api_type`, `is_enabled`, `sort_order`) VALUES
('Meting API', 'https://api.injahow.cn/meting/', 'both', 1, 1);

-- ============================================
-- 5. 添加主键和索引
-- ============================================

ALTER TABLE `about`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `about`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- ============================================
-- 提交事务
-- ============================================

COMMIT;

-- ============================================
-- 安装完成验证
-- ============================================

SELECT '========================================' AS '';
SELECT '✅ LikeGirl v5.2.3-Stable 安装完成！' AS 'Installation Status';
SELECT '========================================' AS '';
SELECT '' AS '';
SELECT '📋 数据库表列表：' AS 'Database Tables';
SHOW TABLES;
SELECT '' AS '';
SELECT '========================================' AS '';
SELECT '🔑 默认登录信息：' AS 'Login Info';
SELECT 'URL: http://localhost/admin/' AS '';
SELECT '用户名: admin' AS '';
SELECT '密码: love2025' AS '';
SELECT '========================================' AS '';
SELECT '' AS '';
SELECT '⚙️  配置文件位置：' AS 'Config File';
SELECT 'admin/Config_DB.php' AS '';
SELECT '请确保数据库配置正确！' AS '';
SELECT '========================================' AS '';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
