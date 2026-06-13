-- ============================================
-- LikeGirl 数据库增量更新脚本
-- 版本：v5.2.3 增量更新
-- 执行前请先备份数据库！
-- ============================================

-- 开始事务（安全起见）
START TRANSACTION;

-- ============================================
-- 1. 创建音乐播放器表
-- ============================================
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

-- ============================================
-- 2. 相册功能：添加 album_id 字段
-- ============================================
-- 检查字段是否存在，不存在才添加
SET @column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'loveImg'
    AND column_name = 'album_id'
);

SET @sql = IF(@column_exists = 0,
  'ALTER TABLE `loveImg` ADD COLUMN `album_id` int(11) NOT NULL DEFAULT 1 AFTER `id`',
  'SELECT "album_id already exists" AS status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 创建索引（如果不存在）
SET @index_exists = (
  SELECT COUNT(*) FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'loveImg'
    AND index_name = 'idx_album_id'
);

SET @sql = IF(@index_exists = 0,
  'CREATE INDEX `idx_album_id` ON `loveImg` (`album_id`)',
  'SELECT "idx_album_id already exists" AS status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 更新现有照片到默认相册
UPDATE `loveImg`
SET `album_id` = 1
WHERE `album_id` = 0 OR `album_id` IS NULL;

-- ============================================
-- 3. 缩略图支持：添加 thumbUrl 字段
-- ============================================
SET @column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'loveImg'
    AND column_name = 'thumbUrl'
);

SET @sql = IF(@column_exists = 0,
  'ALTER TABLE `loveImg` ADD COLUMN `thumbUrl` VARCHAR(255) DEFAULT NULL COMMENT "缩略图URL" AFTER `imgUrl`',
  'SELECT "thumbUrl already exists" AS status'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- 提交事务
-- ============================================
COMMIT;

-- ============================================
-- 验证结果
-- ============================================
SELECT '✅ 数据库更新完成！' AS status;

-- 检查新表
SELECT
  CASE
    WHEN COUNT(*) > 0 THEN '✅ music 表已创建'
    ELSE '❌ music 表创建失败'
  END AS music_table_status
FROM information_schema.tables
WHERE table_schema = DATABASE() AND table_name = 'music';

-- 检查新字段
SELECT
  CASE
    WHEN COUNT(*) > 0 THEN '✅ album_id 字段已添加'
    ELSE '⚠️ album_id 字段不存在'
  END AS album_id_status
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'loveImg'
  AND column_name = 'album_id';

SELECT
  CASE
    WHEN COUNT(*) > 0 THEN '✅ thumbUrl 字段已添加'
    ELSE '⚠️ thumbUrl 字段不存在'
  END AS thumbUrl_status
FROM information_schema.columns
WHERE table_schema = DATABASE()
  AND table_name = 'loveImg'
  AND column_name = 'thumbUrl';

-- 显示更新后的表结构
SELECT '📋 loveImg 表当前结构：' AS info;
SHOW COLUMNS FROM loveImg;

SELECT '📋 music 表当前结构：' AS info;
SHOW COLUMNS FROM music;
