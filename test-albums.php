<?php
header('Content-Type: text/html; charset=utf-8');
include_once 'admin/connect.php';

echo "<h2>数据库连接测试</h2>";
echo "连接状态: " . ($connect ? "成功" : "失败") . "<br>";

echo "<h2>相册表结构</h2>";
$tables = mysqli_query($connect, "SHOW TABLES LIKE 'love_album'");
if (mysqli_num_rows($tables) > 0) {
    echo "✅ love_album 表存在<br>";

    echo "<h3>表结构:</h3>";
    $structure = mysqli_query($connect, "DESCRIBE love_album");
    echo "<pre>";
    while ($row = mysqli_fetch_assoc($structure)) {
        print_r($row);
    }
    echo "</pre>";

    echo "<h3>相册数据:</h3>";
    $albums = mysqli_query($connect, "SELECT * FROM love_album ORDER BY id");
    if (mysqli_num_rows($albums) > 0) {
        echo "<pre>";
        while ($row = mysqli_fetch_assoc($albums)) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "❌ 没有相册数据<br>";
    }

    echo "<h3>照片数据:</h3>";
    $photos = mysqli_query($connect, "SELECT id, album_id, text FROM loveImg LIMIT 10");
    if (mysqli_num_rows($photos) > 0) {
        echo "<pre>";
        while ($row = mysqli_fetch_assoc($photos)) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "❌ 没有照片数据<br>";
    }

} else {
    echo "❌ love_album 表不存在<br>";
    echo "<h3>现有表:</h3>";
    $allTables = mysqli_query($connect, "SHOW TABLES");
    echo "<pre>";
    while ($row = mysqli_fetch_array($allTables)) {
        echo $row[0] . "\n";
    }
    echo "</pre>";
}

echo "<h2>API测试</h2>";
echo "<a href='getAlbums.php' target='_blank'>测试 getAlbums.php</a><br>";
?>
