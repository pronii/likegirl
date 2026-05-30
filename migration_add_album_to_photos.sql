-- Migration: Add album_id column to loveImg table
-- This script adds the foreign key relationship between photos and albums

-- Step 1: Add album_id column to loveImg table
ALTER TABLE `loveImg` 
ADD COLUMN `album_id` int(11) NOT NULL DEFAULT '1' AFTER `id`;

-- Step 2: Add foreign key constraint (optional, can be removed if you prefer no constraints)
-- ALTER TABLE `loveImg` 
-- ADD CONSTRAINT `fk_loveimg_album` 
-- FOREIGN KEY (`album_id`) REFERENCES `love_album` (`id`) 
-- ON DELETE CASCADE 
-- ON UPDATE CASCADE;

-- Step 3: Update existing photos to belong to the default album
-- The 'Default Album' should have been created by love_album.sql
-- Force all existing photos to be in the first album (id=1 or the 'Default Album')
UPDATE `loveImg` 
SET `album_id` = (SELECT id FROM `love_album` LIMIT 1)
WHERE `album_id` = 0;

-- Step 4: Add index for better query performance on album_id
CREATE INDEX `idx_album_id` ON `loveImg` (`album_id`);

-- Verification query (uncomment to check):
-- SELECT li.*, la.album_name 
-- FROM loveImg li 
-- LEFT JOIN love_album la ON li.album_id = la.id 
-- LIMIT 10;