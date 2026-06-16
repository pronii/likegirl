<?php
// 开启错误报告（调试用）
error_reporting(E_ALL);
ini_set('display_errors', 0); // 不直接显示，避免破坏JSON
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json; charset=utf-8');

$file = $_SERVER['PHP_SELF'];
include_once 'connect.php';
include_once 'imageProcessor.php';
include_once 'Function.php';

// 返回JSON响应
function jsonResponse($success, $message, $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// 权限检查
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    jsonResponse(false, '未登录或会话已过期');
}

// 检查是否为批量上传（多文件）还是单文件上传
$isBatchUpload = isset($_FILES['files']) && is_array($_FILES['files']['name']);
$isSingleUpload = isset($_FILES['imageFile']);

if (!$isBatchUpload && !$isSingleUpload) {
    jsonResponse(false, '未接收到文件');
}

// ==================== 批量上传模式 ====================
if ($isBatchUpload) {
    $album_id = intval($_POST['album_id'] ?? 0);
    $imgDatd = trim($_POST['imgDatd'] ?? '');

    // 日期验证
    if (empty($imgDatd)) {
        jsonResponse(false, '日期不能为空');
    }

    $uploadedFiles = [];
    $errors = [];
    $totalFiles = count($_FILES['files']['name']);

    // 创建必要的目录
    $uploadDirs = [
        'image' => __DIR__ . '/../uploads/images/',
        'thumb' => __DIR__ . '/../uploads/thumbs/',
        'video' => __DIR__ . '/../uploads/videos/',
        'video_thumb' => __DIR__ . '/../uploads/video_thumbs/'
    ];

    foreach ($uploadDirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    // 批量处理每个文件
    for ($index = 0; $index < $totalFiles; $index++) {
        // 检查上传错误
        if ($_FILES['files']['error'][$index] != UPLOAD_ERR_OK) {
            $errors[] = "文件 {$_FILES['files']['name'][$index]}: 上传失败";
            continue;
        }

        $fileName = $_FILES['files']['name'][$index];
        $tmpPath = $_FILES['files']['tmp_name'][$index];
        $fileSize = $_FILES['files']['size'][$index];
        $fileType = $_FILES['files']['type'][$index];

        // 获取描述（如果提供）- 使用htmlspecialchars防止XSS
        $imgText = isset($_POST["imgText_{$index}"]) ? htmlspecialchars(trim($_POST["imgText_{$index}"]), ENT_QUOTES, 'UTF-8') : '';

        // 判断是视频还是图片
        if (strpos($fileType, 'video/') === 0) {
            // ==================== 视频处理 ====================

            // 服务器端二次验证MIME类型（防止伪造）
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMimeType = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);

            if (strpos($realMimeType, 'video/') !== 0) {
                $errors[] = "文件 {$fileName}: 文件类型不匹配（伪造的视频文件）";
                continue;
            }

            // 验证视频格式
            if (!isValidVideoFile($fileName, $fileType)) {
                $errors[] = "文件 {$fileName}: 不支持的视频格式";
                continue;
            }

            // 验证文件大小（100MB）
            $maxSize = 100 * 1024 * 1024;
            if ($fileSize > $maxSize) {
                $errors[] = "文件 {$fileName}: 视频文件过大（最大100MB）";
                continue;
            }

            // 生成唯一文件名
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $timestamp = date('YmdHis') . '_' . $index;
            $randomStr = substr(md5(uniqid()), 0, 8);
            $newFileName = $timestamp . '_' . $randomStr . '.' . $extension;

            $videoPath = $uploadDirs['video'] . $newFileName;

            // 保存视频文件
            if (!move_uploaded_file($tmpPath, $videoPath)) {
                $errorMsg = error_get_last();
                $errors[] = "文件 {$fileName}: 视频保存失败 (源: $tmpPath, 目标: $videoPath, 错误: " . ($errorMsg['message'] ?? 'unknown') . ")";
                continue;
            }

            $videoUrl = '/uploads/videos/' . $newFileName;
            $thumbnailUrl = null;

            // 处理缩略图（如果提供）
            if (!empty($_POST["thumbnail_{$index}"])) {
                $thumbnailBase64 = $_POST["thumbnail_{$index}"];

                // 去除data:image前缀
                if (strpos($thumbnailBase64, 'data:image') === 0) {
                    $thumbnailBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $thumbnailBase64);
                }

                $thumbnailData = base64_decode($thumbnailBase64);

                if ($thumbnailData !== false) {
                    // 验证大小（限制5MB）
                    if (strlen($thumbnailData) > 5 * 1024 * 1024) {
                        $errors[] = "文件 {$fileName}: 缩略图过大";
                        // 删除已上传的视频
                        if (file_exists($videoPath)) {
                            unlink($videoPath);
                        }
                        continue;
                    }

                    $thumbName = $timestamp . '_' . $randomStr . '.jpg';
                    $thumbPath = $uploadDirs['video_thumb'] . $thumbName;
                    $tempPath = $thumbPath . '.tmp';

                    // 临时保存并验证
                    file_put_contents($tempPath, $thumbnailData);

                    // 验证是否为真实图片
                    $imageInfo = @getimagesize($tempPath);
                    if ($imageInfo === false) {
                        unlink($tempPath);
                        $errors[] = "文件 {$fileName}: 无效的缩略图";
                        // 删除已上传的视频
                        if (file_exists($videoPath)) {
                            unlink($videoPath);
                        }
                        continue;
                    }

                    // 重命名为最终文件
                    rename($tempPath, $thumbPath);
                    $thumbnailUrl = '/uploads/video_thumbs/' . $thumbName;
                }
            }

            // 获取视频时长
            $duration = null;
            if (!empty($_POST["duration_{$index}"]) && is_numeric($_POST["duration_{$index}"])) {
                $duration = intval($_POST["duration_{$index}"]);
            }

            // 插入数据库 - 使用预处理语句防止SQL注入
            $stmt = mysqli_prepare($connect, "INSERT INTO loveImg (media_type, imgUrl, thumbnail_url, video_duration, file_size, imgText, imgDatd, album_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                // 删除已上传的文件
                if (file_exists($videoPath)) {
                    unlink($videoPath);
                }
                if ($thumbnailUrl && file_exists($uploadDirs['video_thumb'] . basename($thumbnailUrl))) {
                    unlink($uploadDirs['video_thumb'] . basename($thumbnailUrl));
                }
                $errors[] = "文件 {$fileName}: 数据库准备失败";
                continue;
            }

            $media_type = 'video';
            mysqli_stmt_bind_param($stmt, "ssssissi", $media_type, $videoUrl, $thumbnailUrl, $duration, $fileSize, $imgText, $imgDatd, $album_id);
            $result = mysqli_stmt_execute($stmt);
            $insertId = $result ? mysqli_insert_id($connect) : 0;
            mysqli_stmt_close($stmt);

            if ($result) {
                $uploadedFiles[] = [
                    'id' => $insertId,
                    'name' => $fileName,
                    'type' => 'video',
                    'url' => $videoUrl,
                    'thumbnail_url' => $thumbnailUrl,
                    'duration' => $duration,
                    'file_size' => formatFileSize($fileSize)
                ];
            } else {
                // 删除已上传的文件
                if (file_exists($videoPath)) {
                    unlink($videoPath);
                }
                if ($thumbnailUrl && file_exists($uploadDirs['video_thumb'] . basename($thumbnailUrl))) {
                    unlink($uploadDirs['video_thumb'] . basename($thumbnailUrl));
                }
                $errors[] = "文件 {$fileName}: 数据库插入失败";
            }

        } elseif (strpos($fileType, 'image/') === 0) {
            // ==================== 图片处理 ====================

            // 验证图片格式
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($fileType, $allowedTypes)) {
                $errors[] = "文件 {$fileName}: 不支持的图片格式";
                continue;
            }

            // 验证文件大小（10MB）
            $maxSize = 10 * 1024 * 1024;
            if ($fileSize > $maxSize) {
                $errors[] = "文件 {$fileName}: 图片文件过大（最大10MB）";
                continue;
            }

            // 验证是否为真实图片
            $imageInfo = @getimagesize($tmpPath);
            if ($imageInfo === false) {
                $errors[] = "文件 {$fileName}: 不是有效的图片";
                continue;
            }

            // 生成唯一文件名
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (empty($ext)) {
                $mimeMap = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp'
                ];
                $ext = $mimeMap[$fileType] ?? 'jpg';
            }

            $timestamp = date('YmdHis') . '_' . $index;
            $randomStr = substr(md5(uniqid()), 0, 8);
            $newFileName = $timestamp . '_' . $randomStr . '.' . $ext;

            $imagePath = $uploadDirs['image'] . $newFileName;
            $thumbPath = $uploadDirs['thumb'] . $newFileName;

            // 移动文件
            if (!move_uploaded_file($tmpPath, $imagePath)) {
                $errorMsg = error_get_last();
                $errors[] = "文件 {$fileName}: 图片保存失败 (源: $tmpPath, 目标: $imagePath, 错误: " . ($errorMsg['message'] ?? 'unknown') . ")";
                continue;
            }

            // 压缩原图
            $compressed = $uploadDirs['image'] . 'compressed_' . $newFileName;
            if (!compressImage($imagePath, $compressed, 85, 1920, 1080)) {
                @unlink($imagePath);
                $errors[] = "文件 {$fileName}: 图片压缩失败";
                continue;
            }
            @unlink($imagePath);
            rename($compressed, $imagePath);

            // 生成缩略图
            if (!createThumbnail($imagePath, $thumbPath, 300, 300)) {
                @unlink($imagePath);
                $errors[] = "文件 {$fileName}: 缩略图生成失败";
                continue;
            }

            // 保存到数据库 - 使用预处理语句防止SQL注入
            $imgUrl = '/uploads/images/' . $newFileName;
            $thumbUrl = '/uploads/thumbs/' . $newFileName;

            $stmt = mysqli_prepare($connect, "INSERT INTO loveImg (media_type, imgUrl, thumbnail_url, file_size, imgText, imgDatd, album_id) VALUES (?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                // 删除已上传的文件
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
                $errors[] = "文件 {$fileName}: 数据库准备失败";
                continue;
            }

            $media_type = 'image';
            mysqli_stmt_bind_param($stmt, "ssssssi", $media_type, $imgUrl, $thumbUrl, $fileSize, $imgText, $imgDatd, $album_id);
            $result = mysqli_stmt_execute($stmt);
            $insertId = $result ? mysqli_insert_id($connect) : 0;
            mysqli_stmt_close($stmt);

            if ($result) {
                $uploadedFiles[] = [
                    'id' => $insertId,
                    'name' => $fileName,
                    'type' => 'image',
                    'url' => $imgUrl,
                    'thumbnail_url' => $thumbUrl,
                    'file_size' => formatFileSize($fileSize)
                ];
            } else {
                // 删除已上传的文件
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
                $errors[] = "文件 {$fileName}: 数据库保存失败";
            }

        } else {
            $errors[] = "文件 {$fileName}: 不支持的文件类型";
        }
    }

    // 返回批量上传结果（统一使用 success 格式）
    $hasUploaded = count($uploadedFiles) > 0;
    echo json_encode([
        'success' => $hasUploaded,
        'message' => $hasUploaded ? '批量上传完成' : '上传失败',
        'data' => [
            'uploaded' => count($uploadedFiles),
            'failed' => count($errors),
            'files' => $uploadedFiles,
            'errors' => $errors
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ==================== 单文件上传模式（保持向后兼容） ====================
if ($isSingleUpload) {
    if ($_FILES['imageFile']['error'] != 0) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => '文件大小超过 php.ini 限制',
            UPLOAD_ERR_FORM_SIZE => '文件大小超过表单限制',
            UPLOAD_ERR_PARTIAL => '文件只上传了一部分',
            UPLOAD_ERR_NO_FILE => '没有文件被上传',
            UPLOAD_ERR_NO_TMP_DIR => '临时目录不存在',
            UPLOAD_ERR_CANT_WRITE => '文件写入失败',
            UPLOAD_ERR_EXTENSION => 'PHP扩展阻止了文件上传',
        ];
        $error = $_FILES['imageFile']['error'];
        $message = isset($errorMessages[$error]) ? $errorMessages[$error] : '文件上传失败';
        jsonResponse(false, $message);
    }

    $file = $_FILES['imageFile'];
    $album_id = intval($_POST['album_id']);
    $imgText = isset($_POST['imgText']) ? htmlspecialchars(trim($_POST['imgText']), ENT_QUOTES) : '';
    $imgDatd = trim($_POST['imgDatd']);

    // 日期验证
    if (empty($imgDatd)) {
        jsonResponse(false, '日期不能为空');
    }

    // 类型验证
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($file['type'], $allowedTypes)) {
        jsonResponse(false, '不支持的文件格式，仅支持 JPG、PNG、GIF、WEBP');
    }

    // 额外检查文件扩展名
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts)) {
        jsonResponse(false, '不支持的文件扩展名');
    }

    // 大小验证（10MB）
    $maxSize = 10 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        $sizeMB = round($file['size'] / 1024 / 1024, 2);
        jsonResponse(false, "文件过大（{$sizeMB} MB），最大支持 10 MB");
    }

    // 验证是否为真实图片文件
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        jsonResponse(false, '文件不是有效的图片');
    }

    // 创建目录
    $uploadDir = __DIR__ . '/../uploads/images/';
    $thumbDir = __DIR__ . '/../uploads/thumbs/';

    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            jsonResponse(false, '无法创建上传目录');
        }
    }

    if (!is_dir($thumbDir)) {
        if (!mkdir($thumbDir, 0755, true)) {
            jsonResponse(false, '无法创建缩略图目录');
        }
    }

    // 生成唯一文件名
    $filename = date('YmdHis') . '_' . uniqid() . '.' . $ext;
    $imagePath = $uploadDir . $filename;
    $thumbPath = $thumbDir . $filename;

    // 移动文件
    if (!move_uploaded_file($file['tmp_name'], $imagePath)) {
        jsonResponse(false, '文件保存失败');
    }

    // 压缩原图
    $compressed = $uploadDir . 'compressed_' . $filename;
    if (!compressImage($imagePath, $compressed, 85, 1920, 1080)) {
        @unlink($imagePath);
        jsonResponse(false, '图片压缩失败');
    }
    @unlink($imagePath);
    rename($compressed, $imagePath);

    // 生成缩略图
    if (!createThumbnail($imagePath, $thumbPath, 300, 300)) {
        @unlink($imagePath);
        jsonResponse(false, '缩略图生成失败');
    }

    // 保存到数据库
    $imgUrl = '/uploads/images/' . $filename;
    $thumbUrl = '/uploads/thumbs/' . $filename;

    $stmt = mysqli_prepare($connect, "INSERT INTO loveImg (album_id, imgDatd, imgText, imgUrl, thumbUrl) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        @unlink($imagePath);
        @unlink($thumbPath);
        jsonResponse(false, '数据库错误：' . mysqli_error($connect));
    }

    mysqli_stmt_bind_param($stmt, "issss", $album_id, $imgDatd, $imgText, $imgUrl, $thumbUrl);
    $result = mysqli_stmt_execute($stmt);
    $insertId = mysqli_insert_id($connect);
    mysqli_stmt_close($stmt);

    if ($result) {
        // 记录成功日志（可选）
        error_log("Batch upload success: {$file['name']} by " . $_SESSION['loginadmin']);

        jsonResponse(true, '上传成功', [
            'imgUrl' => $imgUrl,
            'thumbUrl' => $thumbUrl,
            'id' => $insertId,
            'filename' => $filename
        ]);
    } else {
        @unlink($imagePath);
        @unlink($thumbPath);
        jsonResponse(false, '数据库保存失败');
    }
}
?>
