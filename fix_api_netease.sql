-- 更新为网易云官方API（无需第三方）
UPDATE `music_api_config` SET
  `name` = '网易云官方API',
  `api_url` = 'https://music.163.com/api'
WHERE id = 1;
