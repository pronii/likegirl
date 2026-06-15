-- 添加视频相关字段
ALTER TABLE `loveImg`
ADD COLUMN `media_type` ENUM('image', 'video') DEFAULT 'image'
    COMMENT '媒体类型' AFTER `id`,
ADD COLUMN `thumbnail_url` VARCHAR(500) DEFAULT NULL
    COMMENT '视频缩略图URL' AFTER `imgUrl`,
ADD COLUMN `video_duration` INT DEFAULT NULL
    COMMENT '视频时长(秒)' AFTER `thumbnail_url`,
ADD COLUMN `file_size` BIGINT DEFAULT NULL
    COMMENT '文件大小(字节)' AFTER `video_duration`;

-- 将现有数据标记为image类型
UPDATE `loveImg`
SET `media_type` = 'image'
WHERE `media_type` IS NULL OR `media_type` = '';

-- 添加索引优化查询
ALTER TABLE `loveImg` ADD INDEX `idx_media_type` (`media_type`);
