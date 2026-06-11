CREATE TABLE IF NOT EXISTS `music_api_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'API名称',
  `api_url` varchar(500) NOT NULL COMMENT 'API地址',
  `api_type` varchar(20) NOT NULL COMMENT 'API类型: search/url',
  `is_enabled` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `music_api_config` (`name`, `api_url`, `api_type`, `is_enabled`, `sort_order`) VALUES
('网易云API1', 'https://netease-cloud-music-api-psi-six-62.vercel.app', 'both', 1, 1),
('网易云API2', 'https://music.eleuu.com', 'both', 1, 2),
('Meting API', 'https://api.injahow.cn/meting/', 'both', 1, 3);
