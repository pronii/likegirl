ALTER TABLE `music`
ADD COLUMN `platform` varchar(20) DEFAULT 'netease' AFTER `artist`,
ADD COLUMN `song_id` varchar(50) DEFAULT NULL AFTER `platform`,
MODIFY COLUMN `music_url` varchar(500) DEFAULT NULL,
ADD INDEX `idx_song_id` (`song_id`);

TRUNCATE TABLE `music`;
