<?php
session_start();
include_once 'connect.php';

// 验证登录会话
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die(json_encode(['success' => false, 'message' => '未授权']));
}

// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 验证文件上传
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = '文件上传失败';
    if (isset($_FILES['file']['error'])) {
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = '文件大小超过限制';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = '未选择文件';
                break;
        }
    }
    die(json_encode(['success' => false, 'message' => $errorMsg]));
}

$uploadedFile = $_FILES['file'];
$originalName = $uploadedFile['name'];
$tmpPath = $uploadedFile['tmp_name'];
$fileSize = $uploadedFile['size'];

// 文件大小限制：50MB
$maxSize = 50 * 1024 * 1024;
if ($fileSize > $maxSize) {
    die(json_encode(['success' => false, 'message' => '文件大小超过限制（最大50MB）']));
}

// 验证文件扩展名
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if ($ext !== 'sql') {
    die(json_encode(['success' => false, 'message' => '文件格式不正确，仅支持 .sql 文件']));
}

// 验证 MIME 类型
$allowedMimes = ['text/plain', 'application/sql', 'application/x-sql', 'application/octet-stream'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $tmpPath);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimes)) {
    die(json_encode(['success' => false, 'message' => '文件类型不正确']));
}

// 读取文件前1KB，检查是否包含SQL关键字
$handle = fopen($tmpPath, 'r');
if ($handle === false) {
    die(json_encode(['success' => false, 'message' => '无法读取文件']));
}

$firstKB = fread($handle, 1024);
fclose($handle);

// 检查SQL关键字
$sqlKeywords = ['CREATE', 'INSERT', 'DROP', 'SET', 'TABLE', 'DATABASE', 'SELECT', 'UPDATE'];
$foundKeyword = false;
foreach ($sqlKeywords as $keyword) {
    if (stripos($firstKB, $keyword) !== false) {
        $foundKeyword = true;
        break;
    }
}

if (!$foundKeyword) {
    die(json_encode(['success' => false, 'message' => '文件内容不是有效的SQL文件']));
}

// 生成标准格式文件名
$newFilename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';

// 备份目录
$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$backupDirReal = realpath($backupDir);
if ($backupDirReal === false) {
    die(json_encode(['success' => false, 'message' => '备份目录创建失败']));
}

// 目标路径
$targetPath = $backupDir . '/' . $newFilename;

// 移动上传文件
if (!move_uploaded_file($tmpPath, $targetPath)) {
    die(json_encode(['success' => false, 'message' => '文件保存失败']));
}

// 路径规范化验证：确保文件在备份目录内
$targetPathReal = realpath($targetPath);
if ($targetPathReal === false || strpos($targetPathReal, $backupDirReal) !== 0) {
    // 安全问题，删除文件
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
    die(json_encode(['success' => false, 'message' => '路径验证失败']));
}

// 记录操作日志
$logContent = '管理员上传备份文件: ' . $newFilename . ' (' . round($fileSize / 1024 / 1024, 2) . ' MB)';
$logTime = date('Y-m-d H:i:s');
$logStmt = $connect->prepare("INSERT INTO warning (Warr_content, Warr_time) VALUES (?, ?)");
if ($logStmt) {
    $logStmt->bind_param("ss", $logContent, $logTime);
    if (!$logStmt->execute()) {
        error_log("Failed to log backup upload: " . $logStmt->error);
    }
    $logStmt->close();
} else {
    error_log("Failed to prepare log statement: " . $connect->error);
}

// 返回成功结果
echo json_encode([
    'success' => true,
    'message' => '上传成功',
    'filename' => $newFilename,
    'size' => $fileSize
]);
?>
