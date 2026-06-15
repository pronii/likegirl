<?php
session_start();
include_once 'connect.php';

// 验证登录会话
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die(json_encode(['success' => false, 'message' => '未授权']));
}

// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 获取请求数据
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 验证参数
if (!isset($data['filenames']) || !is_array($data['filenames']) || empty($data['filenames'])) {
    die(json_encode(['success' => false, 'message' => '参数错误']));
}

$filenames = $data['filenames'];

// 限制批量删除数量
if (count($filenames) > 100) {
    die(json_encode(['success' => false, 'message' => '单次最多删除100个文件']));
}

// 备份目录
$backupDir = __DIR__ . '/../backups';
$backupDirReal = realpath($backupDir);

if ($backupDirReal === false) {
    die(json_encode(['success' => false, 'message' => '备份目录不存在']));
}

$deleted = 0;
$failed = 0;
$deletedFiles = [];

// 遍历删除文件
foreach ($filenames as $filename) {
    // 文件名白名单验证：只允许 backup_YYYY-MM-DD_HH-ii-ss.sql 格式
    if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
        $failed++;
        continue;
    }

    // 构建文件路径
    $filepath = $backupDir . '/' . $filename;

    // 路径规范化验证：确保文件在备份目录内
    $filepathReal = realpath($filepath);
    if ($filepathReal === false || strpos($filepathReal, $backupDirReal) !== 0) {
        $failed++;
        continue;
    }

    // 检查文件是否存在
    if (!file_exists($filepathReal)) {
        $failed++;
        continue;
    }

    // 删除文件
    if (unlink($filepathReal)) {
        $deleted++;
        $deletedFiles[] = $filename;
    } else {
        $failed++;
    }
}

// 记录操作日志到 warning 表
if ($deleted > 0) {
    $logContent = '';
    if ($deleted == 1 && count($filenames) == 1) {
        $logContent = '管理员删除备份文件: ' . $deletedFiles[0];
    } else {
        $logContent = '管理员批量删除备份文件: ' . $deleted . '个文件';
    }

    $logTime = date('Y-m-d H:i:s');
    $logIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    // 使用warning表的实际字段: ip, gsd, time, file
    $logStmt = $connect->prepare("INSERT INTO warning (ip, gsd, time, file) VALUES (?, ?, ?, ?)");
    if ($logStmt) {
        $fileInfo = ($deleted == 1 && count($filenames) == 1) ? $deletedFiles[0] : "{$deleted}个文件";
        $logStmt->bind_param("ssss", $logIp, $logContent, $logTime, $fileInfo);
        if (!$logStmt->execute()) {
            error_log("Failed to log backup deletion: " . $logStmt->error);
        }
        $logStmt->close();
    } else {
        error_log("Failed to prepare log statement: " . $connect->error);
    }
}

// 返回结果
if ($failed == 0) {
    echo json_encode([
        'success' => true,
        'message' => '成功删除 ' . $deleted . ' 个备份文件',
        'deleted' => $deleted
    ]);
} else if ($deleted > 0) {
    echo json_encode([
        'success' => true,
        'message' => '部分文件删除成功',
        'deleted' => $deleted,
        'failed' => $failed
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => '删除失败',
        'failed' => $failed
    ]);
}
?>
