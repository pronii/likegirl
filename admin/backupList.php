<?php
session_start();
include_once 'connect.php';

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die(json_encode(['success' => false, 'message' => '未授权']));
}

$backupDir = __DIR__ . '/../backups';

// 获取备份列表
$files = [];
if (is_dir($backupDir)) {
    $scannedFiles = scandir($backupDir);
    foreach ($scannedFiles as $file) {
        if (preg_match('/^backup_.*\.sql$/', $file)) {
            $filepath = $backupDir . '/' . $file;
            $files[] = [
                'name' => $file,
                'size' => filesize($filepath),
                'date' => date('Y-m-d H:i:s', filemtime($filepath))
            ];
        }
    }
    // 按时间倒序
    usort($files, function($a, $b) {
        return strcmp($b['name'], $a['name']);
    });
}

echo json_encode(['success' => true, 'files' => $files]);
