-- ========================================
-- 删除默认相册脚本
-- ========================================
-- 警告：执行前请备份数据库！
-- 使用方法：在 phpMyAdmin 的 SQL 标签页中粘贴执行
-- ========================================

-- 1. 查看当前所有相册
SELECT * FROM love_album ORDER BY id;

-- 2. 将默认相册的照片移动到未分类（album_id = 0）
--    如果你想移动到其他相册，请修改下面的 0 为目标相册 ID
UPDATE loveImg
SET album_id = 0
WHERE album_id IN (
    SELECT id FROM (
        SELECT id FROM love_album
        WHERE album_name IN ('默认相册', 'Default Album')
    ) AS temp
);

-- 3. 删除默认相册
DELETE FROM love_album
WHERE album_name IN ('默认相册', 'Default Album');

-- 4. 验证删除结果
SELECT '删除后的相册列表：' AS info;
SELECT * FROM love_album ORDER BY id;

SELECT '未分类照片数量：' AS info;
SELECT COUNT(*) as count FROM loveImg WHERE album_id = 0;

-- ========================================
-- 如果想将未分类照片移动到指定相册，执行下面的语句
-- 将 <目标相册ID> 替换为实际的相册 ID
-- ========================================
-- UPDATE loveImg SET album_id = <目标相册ID> WHERE album_id = 0;
