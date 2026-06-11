<?php
session_start();
include_once 'connect.php';
include_once 'Config_DB.php';

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die(json_encode(['success' => false, 'message' => '未授权']));
}

$filename = $_POST['filename'] ?? '';
if (empty($filename)) {
    die(json_encode(['success' => false, 'message' => '文件名不能为空']));
}

$backupDir = __DIR__ . '/../backups';
$filepath = $backupDir . '/' . $filename;

if (!file_exists($filepath) || !preg_match('/^backup_.*\.sql$/', $filename)) {
    die(json_encode(['success' => false, 'message' => '备份文件不存在']));
}

// 读取SQL文件
$sql = file_get_contents($filepath);
if ($sql === false) {
    die(json_encode(['success' => false, 'message' => '读取备份文件失败']));
}

// 使用 mysqli_multi_query 执行多条SQL
if (mysqli_multi_query($connect, $sql)) {
    // 清空所有结果集
    do {
        if ($result = mysqli_store_result($connect)) {
            mysqli_free_result($result);
        }
    } while (mysqli_next_result($connect));

    echo json_encode(['success' => true, 'message' => '恢复成功']);
} else {
    echo json_encode(['success' => false, 'message' => '恢复失败: ' . mysqli_error($connect)]);
}

