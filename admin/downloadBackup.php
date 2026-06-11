<?php
session_start();
include_once 'connect.php';

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die(json_encode(['success' => false, 'message' => '未授权']));
}

$filename = $_GET['filename'] ?? '';
if (empty($filename)) {
    die(json_encode(['success' => false, 'message' => '文件名不能为空']));
}

$backupDir = __DIR__ . '/../backups';
$filepath = $backupDir . '/' . $filename;

if (!file_exists($filepath) || !preg_match('/^backup_.*\.sql$/', $filename)) {
    die(json_encode(['success' => false, 'message' => '备份文件不存在']));
}

// 下载文件
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
