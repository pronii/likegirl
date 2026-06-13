<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$file = $_SERVER['PHP_SELF'];
include_once 'connect.php';
include_once 'imageProcessor.php';

// 返回JSON响应
function jsonResponse($success, $message, $data = []) {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data), JSON_UNESCAPED_UNICODE);
    exit;
}

// 权限检查
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    jsonResponse(false, '未登录或会话已过期');
}

// 文件检查
if (!isset($_FILES['imageFile'])) {
    jsonResponse(false, '未接收到文件');
}

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
