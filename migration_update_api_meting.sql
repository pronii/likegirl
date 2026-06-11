-- 更新API配置，添加Meting API
TRUNCATE TABLE `music_api_config`;

INSERT INTO `music_api_config` (`name`, `api_url`, `api_type`, `is_enabled`, `sort_order`) VALUES
('Meting API', 'https://api.injahow.cn/meting/', 'both', 1, 1);
