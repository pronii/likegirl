<?php
// 图片压缩和缩略图生成函数

/**
 * 压缩图片并生成缩略图
 * @param string $source 源文件路径
 * @param string $destination 目标文件路径
 * @param int $quality 压缩质量 (1-100)
 * @param int $maxWidth 最大宽度
 * @param int $maxHeight 最大高度
 * @return bool
 */
function compressImage($source, $destination, $quality = 85, $maxWidth = 1920, $maxHeight = 1080) {
    $info = getimagesize($source);
    if (!$info) return false;

    list($width, $height, $type) = $info;

    // 创建源图片资源
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($source);
            break;
        case IMAGETYPE_WEBP:
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }

    // 计算新尺寸
    if ($width > $maxWidth || $height > $maxHeight) {
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }

    // 创建新图片
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // 保持透明度
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // 复制并缩放
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // 保存
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($newImage, $destination, $quality);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($newImage, $destination, round(9 * (100 - $quality) / 100));
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($newImage, $destination);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($newImage, $destination, $quality);
            break;
    }

    imagedestroy($image);
    imagedestroy($newImage);

    return $result;
}

/**
 * 生成缩略图
 * @param string $source 源文件路径
 * @param string $destination 目标文件路径
 * @param int $thumbWidth 缩略图宽度
 * @param int $thumbHeight 缩略图高度
 * @return bool
 */
function createThumbnail($source, $destination, $thumbWidth = 300, $thumbHeight = 300) {
    return compressImage($source, $destination, 80, $thumbWidth, $thumbHeight);
}
