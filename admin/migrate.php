<?php
session_start();
include_once 'connect.php';

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die('未授权');
}

// 添加缩略图字段
$sql = "ALTER TABLE loveImg ADD COLUMN thumbUrl VARCHAR(255) DEFAULT NULL COMMENT '缩略图URL' AFTER imgUrl";
$result = mysqli_query($connect, $sql);

if ($result) {
    echo "✅ 数据库迁移成功！thumbUrl 字段已添加。";
} else {
    $error = mysqli_error($connect);
    if (strpos($error, 'Duplicate column name') !== false) {
        echo "✅ thumbUrl 字段已存在，无需重复添加。";
    } else {
        echo "❌ 迁移失败：" . $error;
    }
}
