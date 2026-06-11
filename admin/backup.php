<?php
session_start();
include_once 'connect.php';
include_once 'Config_DB.php';

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die(json_encode(['success' => false, 'message' => '未授权']));
}

// 创建备份目录
$backupDir = __DIR__ . '/../backups';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// 生成备份文件名
$filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
$filepath = $backupDir . '/' . $filename;

// 获取所有表
$tables = [];
$result = mysqli_query($connect, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// 生成SQL
$sql = "-- LikeGirl Database Backup\n";
$sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n\n";
$sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

foreach ($tables as $table) {
    // 表结构
    $sql .= "-- Table: $table\n";
    $sql .= "DROP TABLE IF EXISTS `$table`;\n";
    $createTable = mysqli_query($connect, "SHOW CREATE TABLE `$table`");
    $row = mysqli_fetch_row($createTable);
    $sql .= $row[1] . ";\n\n";

    // 表数据
    $rows = mysqli_query($connect, "SELECT * FROM `$table`");
    $numFields = mysqli_num_fields($rows);

    while ($row = mysqli_fetch_row($rows)) {
        $sql .= "INSERT INTO `$table` VALUES(";
        for ($i = 0; $i < $numFields; $i++) {
            $row[$i] = str_replace("\n", "\\n", addslashes($row[$i]));
            $sql .= isset($row[$i]) ? "'".$row[$i]."'" : "NULL";
            if ($i < ($numFields - 1)) {
                $sql .= ',';
            }
        }
        $sql .= ");\n";
    }
    $sql .= "\n";
}

$sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

// 保存文件
if (file_put_contents($filepath, $sql)) {
    echo json_encode([
        'success' => true,
        'message' => '备份成功',
        'filename' => $filename,
        'size' => filesize($filepath)
    ]);
} else {
    echo json_encode(['success' => false, 'message' => '备份失败']);
}
