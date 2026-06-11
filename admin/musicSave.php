<?php
/*
 * @Page：保存音乐信息
 * @Version：Like Girl 5.2.1-Stable
 */
session_start();
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

include_once 'Database.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$artist = isset($_POST['artist']) ? trim($_POST['artist']) : '';
$platform = isset($_POST['platform']) ? trim($_POST['platform']) : 'netease';
$song_id = isset($_POST['song_id']) ? trim($_POST['song_id']) : '';
$music_url = isset($_POST['music_url']) ? trim($_POST['music_url']) : '';
$cover_url = isset($_POST['cover_url']) ? trim($_POST['cover_url']) : '';
$duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0;
$sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
$is_enabled = isset($_POST['is_enabled']) ? intval($_POST['is_enabled']) : 1;

// 验证必填项
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => '歌曲名称不能为空']);
    exit;
}

// 如果没有URL但有song_id，允许保存（播放时会动态获取URL）
if (empty($music_url) && empty($song_id)) {
    echo json_encode(['success' => false, 'message' => '音乐URL或歌曲ID至少需要一个']);
    exit;
}

try {
    if ($id > 0) {
        // 更新
        $stmt = $conn->prepare("UPDATE music SET title=?, artist=?, platform=?, song_id=?, music_url=?, cover_url=?, duration=?, sort_order=?, is_enabled=? WHERE id=?");
        $stmt->bind_param("ssssssiiii", $title, $artist, $platform, $song_id, $music_url, $cover_url, $duration, $sort_order, $is_enabled, $id);
        $message = '更新成功';
    } else {
        // 新增
        $stmt = $conn->prepare("INSERT INTO music (title, artist, platform, song_id, music_url, cover_url, duration, sort_order, is_enabled) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssiis", $title, $artist, $platform, $song_id, $music_url, $cover_url, $duration, $sort_order, $is_enabled);
        $message = '添加成功';
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => $message,
            'id' => $id > 0 ? $id : $conn->insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '保存失败：' . $stmt->error
        ]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '操作失败：' . $e->getMessage()
    ]);
}

$conn->close();
?>
