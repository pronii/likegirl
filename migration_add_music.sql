-- 音乐播放器功能 - 数据库迁移
-- 创建音乐表

CREATE TABLE IF NOT EXISTS `music` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '歌曲名称',
  `artist` varchar(100) DEFAULT NULL COMMENT '艺术家',
  `music_url` varchar(500) NOT NULL COMMENT '音乐文件URL',
  `cover_url` varchar(500) DEFAULT NULL COMMENT '封面图URL',
  `duration` int(11) DEFAULT 0 COMMENT '时长(秒)',
  `is_enabled` tinyint(1) DEFAULT 1 COMMENT '是否启用 1:启用 0:禁用',
  `sort_order` int(11) DEFAULT 0 COMMENT '排序顺序',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_enabled_sort` (`is_enabled`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='音乐播放列表';

-- 插入示例数据
INSERT INTO `music` (`title`, `artist`, `music_url`, `cover_url`, `duration`, `is_enabled`, `sort_order`) VALUES
('示例音乐1', '示例歌手', 'https://example.com/music1.mp3', 'https://example.com/cover1.jpg', 180, 1, 1),
('示例音乐2', '示例歌手', 'https://example.com/music2.mp3', 'https://example.com/cover2.jpg', 240, 1, 2);
