<?php
session_start();
include_once 'connect.php';
include_once 'Function.php';
header('Content-Type: application/json; charset=utf-8');

// 检查登录状态
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    http_response_code(403);
    echo json_encode([
        'code' => 403,
        'message' => '未登录或会话已过期',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 检查是否有文件上传
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode([
        'code' => 400,
        'message' => '文件上传失败：' . ($_FILES['file']['error'] ?? '未找到文件'),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 获取表单参数
$albumId = intval($_POST['album_id'] ?? 0);
$description = mysqli_real_escape_string($connect, trim($_POST['imgText'] ?? ''));
$date = mysqli_real_escape_string($connect, trim($_POST['imgDatd'] ?? date('Y-m-d H:i:s')));

if ($albumId <= 0) {
    http_response_code(400);
    echo json_encode([
        'code' => 400,
        'message' => '相册ID无效',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$file = $_FILES['file'];
$mimeType = $file['type'];
$tmpPath = $file['tmp_name'];
$originalName = $file['name'];

// 判断文件类型：视频还是图片
if (strpos($mimeType, 'video/') === 0) {
    // ==================== 视频上传处理 ====================

    // 验证视频文件
    if (!isValidVideoFile($originalName, $mimeType)) {
        http_response_code(400);
        echo json_encode([
            'code' => 400,
            'message' => '不支持的视频格式，仅支持 MP4, AVI, MOV, WMV, FLV, MKV',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 验证文件大小（100MB）
    $maxSize = 100 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode([
            'code' => 400,
            'message' => '视频文件过大，最大支持100MB',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 获取文件扩展名
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // 生成唯一文件名：时间戳_随机8位.扩展名
    $uniqueName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

    // 确保目录存在
    $videoDir = '../uploads/videos/';
    if (!is_dir($videoDir)) {
        mkdir($videoDir, 0755, true);
    }

    $videoPath = $videoDir . $uniqueName;

    // 移动上传的视频文件
    if (!move_uploaded_file($tmpPath, $videoPath)) {
        http_response_code(500);
        echo json_encode([
            'code' => 500,
            'message' => '视频文件保存失败',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 相对URL
    $videoUrl = 'uploads/videos/' . $uniqueName;
    $thumbnailUrl = null;

    // 处理缩略图（如果提供）
    if (!empty($_POST['thumbnail'])) {
        $thumbnailBase64 = $_POST['thumbnail'];

        // 去除data:image/png;base64,前缀
        if (strpos($thumbnailBase64, 'data:image') === 0) {
            $thumbnailBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $thumbnailBase64);
        }

        $thumbnailData = base64_decode($thumbnailBase64);

        if ($thumbnailData !== false) {
            // 确保缩略图目录存在
            $thumbDir = '../uploads/video_thumbs/';
            if (!is_dir($thumbDir)) {
                mkdir($thumbDir, 0755, true);
            }

            $thumbName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $thumbPath = $thumbDir . $thumbName;

            if (file_put_contents($thumbPath, $thumbnailData)) {
                $thumbnailUrl = 'uploads/video_thumbs/' . $thumbName;
            }
        }
    }

    // 获取视频时长（如果提供）
    $duration = null;
    if (!empty($_POST['duration']) && is_numeric($_POST['duration'])) {
        $duration = intval($_POST['duration']);
    }

    // 文件大小
    $fileSize = $file['size'];

    // 插入数据库
    $sql = "INSERT INTO loveImg (
        media_type, imgUrl, thumbnail_url, video_duration,
        file_size, imgText, imgDatd, album_id
    ) VALUES (
        'video',
        '$videoUrl',
        " . ($thumbnailUrl ? "'$thumbnailUrl'" : "NULL") . ",
        " . ($duration ? $duration : "NULL") . ",
        $fileSize,
        '$description',
        '$date',
        $albumId
    )";

    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo json_encode([
            'code' => 200,
            'message' => '视频上传成功',
            'data' => [
                'id' => mysqli_insert_id($connect),
                'media_type' => 'video',
                'url' => $videoUrl,
                'thumbnail_url' => $thumbnailUrl,
                'duration' => $duration,
                'file_size' => $fileSize,
                'file_size_formatted' => formatFileSize($fileSize)
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // 删除已上传的文件
        if (file_exists($videoPath)) {
            unlink($videoPath);
        }
        if ($thumbnailUrl && file_exists($thumbPath)) {
            unlink($thumbPath);
        }

        http_response_code(500);
        echo json_encode([
            'code' => 500,
            'message' => '数据库插入失败：' . mysqli_error($connect),
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
    }

} elseif (strpos($mimeType, 'image/') === 0) {
    // ==================== 图片上传处理 ====================

    // 验证图片格式
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode([
            'code' => 400,
            'message' => '不支持的图片格式，仅支持 JPEG, PNG, GIF, WebP',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 验证文件大小（10MB）
    $maxSize = 10 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode([
            'code' => 400,
            'message' => '图片文件过大，最大支持10MB',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 获取文件扩展名
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (empty($extension)) {
        // 根据MIME类型推断扩展名
        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp'
        ];
        $extension = $mimeMap[$mimeType] ?? 'jpg';
    }

    // 生成唯一文件名
    $uniqueName = date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

    // 确保目录存在
    $imageDir = '../uploads/images/';
    if (!is_dir($imageDir)) {
        mkdir($imageDir, 0755, true);
    }

    $imagePath = $imageDir . $uniqueName;

    // 移动上传的图片文件
    if (!move_uploaded_file($tmpPath, $imagePath)) {
        http_response_code(500);
        echo json_encode([
            'code' => 500,
            'message' => '图片文件保存失败',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 相对URL
    $imageUrl = 'uploads/images/' . $uniqueName;

    // 生成缩略图
    $thumbDir = '../uploads/thumbs/';
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0755, true);
    }

    $thumbPath = $thumbDir . $uniqueName;
    $thumbnailUrl = null;

    // 使用GD库生成缩略图
    $sourceImage = null;
    switch ($mimeType) {
        case 'image/jpeg':
        case 'image/jpg':
            $sourceImage = @imagecreatefromjpeg($imagePath);
            break;
        case 'image/png':
            $sourceImage = @imagecreatefrompng($imagePath);
            break;
        case 'image/gif':
            $sourceImage = @imagecreatefromgif($imagePath);
            break;
        case 'image/webp':
            $sourceImage = @imagecreatefromwebp($imagePath);
            break;
    }

    if ($sourceImage) {
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        // 计算缩略图尺寸（最大300x300，保持比例）
        $maxThumbSize = 300;
        $ratio = min($maxThumbSize / $sourceWidth, $maxThumbSize / $sourceHeight);
        $thumbWidth = (int)($sourceWidth * $ratio);
        $thumbHeight = (int)($sourceHeight * $ratio);

        // 创建缩略图
        $thumbImage = imagecreatetruecolor($thumbWidth, $thumbHeight);

        // 处理透明背景（PNG/GIF）
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 255, 255, 255, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }

        // 重采样
        imagecopyresampled($thumbImage, $sourceImage, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $sourceWidth, $sourceHeight);

        // 保存缩略图
        imagejpeg($thumbImage, $thumbPath, 85);
        imagedestroy($thumbImage);
        imagedestroy($sourceImage);

        $thumbnailUrl = 'uploads/thumbs/' . $uniqueName;
    }

    // 文件大小
    $fileSize = $file['size'];

    // 插入数据库
    $sql = "INSERT INTO loveImg (
        media_type, imgUrl, thumbnail_url, file_size,
        imgText, imgDatd, album_id
    ) VALUES (
        'image',
        '$imageUrl',
        " . ($thumbnailUrl ? "'$thumbnailUrl'" : "NULL") . ",
        $fileSize,
        '$description',
        '$date',
        $albumId
    )";

    $result = mysqli_query($connect, $sql);

    if ($result) {
        echo json_encode([
            'code' => 200,
            'message' => '图片上传成功',
            'data' => [
                'id' => mysqli_insert_id($connect),
                'media_type' => 'image',
                'url' => $imageUrl,
                'thumbnail_url' => $thumbnailUrl,
                'file_size' => $fileSize,
                'file_size_formatted' => formatFileSize($fileSize)
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        // 删除已上传的文件
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        if ($thumbnailUrl && file_exists($thumbPath)) {
            unlink($thumbPath);
        }

        http_response_code(500);
        echo json_encode([
            'code' => 500,
            'message' => '数据库插入失败：' . mysqli_error($connect),
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
    }

} else {
    // 不支持的文件类型
    http_response_code(400);
    echo json_encode([
        'code' => 400,
        'message' => '不支持的文件类型：' . $mimeType,
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}
?>
