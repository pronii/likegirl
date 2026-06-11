<?php
/*
 * @Page：免费音乐API - 随机获取无版权音乐
 * @Version：Like Girl 5.2.1-Stable
 */
header("Content-Type: application/json; charset=utf-8");

// 免费无版权音乐列表
$freeMusicList = [
    [
        'title' => 'Summer Day',
        'artist' => 'Bensound',
        'url' => 'https://cdn.pixabay.com/download/audio/2022/05/27/audio_1808fbf07a.mp3',
        'cover' => 'https://cdn.pixabay.com/photo/2022/05/24/17/47/sunset-7218709_960_720.jpg',
        'duration' => 217
    ],
    [
        'title' => 'Good Night',
        'artist' => 'FASSounds',
        'url' => 'https://cdn.pixabay.com/download/audio/2022/03/15/audio_d1718ab41b.mp3',
        'cover' => 'https://cdn.pixabay.com/photo/2016/11/29/09/16/architecture-1868667_960_720.jpg',
        'duration' => 163
    ],
    [
        'title' => 'Inspiring Cinematic',
        'artist' => 'Rafael Krux',
        'url' => 'https://cdn.pixabay.com/download/audio/2022/01/18/audio_4a456f9c35.mp3',
        'cover' => 'https://cdn.pixabay.com/photo/2016/11/29/11/10/lake-1869902_960_720.jpg',
        'duration' => 189
    ],
    [
        'title' => 'Lofi Study',
        'artist' => 'FASSounds',
        'url' => 'https://cdn.pixabay.com/download/audio/2021/11/23/audio_67aff18bef.mp3',
        'cover' => 'https://cdn.pixabay.com/photo/2017/08/30/01/05/milky-way-2695569_960_720.jpg',
        'duration' => 149
    ],
    [
        'title' => 'Sunny Day',
        'artist' => 'Coma-Media',
        'url' => 'https://cdn.pixabay.com/download/audio/2022/08/23/audio_0abf619399.mp3',
        'cover' => 'https://cdn.pixabay.com/photo/2015/12/01/20/28/road-1072823_960_720.jpg',
        'duration' => 141
    ]
];

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'import':
        // 批量导入到数据库
        include_once '../admin/Database.php';
        session_start();

        if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
            echo json_encode(['success' => false, 'message' => '未登录']);
            exit;
        }

        $imported = 0;
        foreach ($freeMusicList as $index => $music) {
            $stmt = $conn->prepare("INSERT INTO music (title, artist, music_url, cover_url, duration, sort_order, is_enabled) VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("ssssii", $music['title'], $music['artist'], $music['url'], $music['cover'], $music['duration'], $index);
            if ($stmt->execute()) {
                $imported++;
            }
            $stmt->close();
        }
        $conn->close();

        echo json_encode([
            'success' => true,
            'message' => "成功导入 {$imported} 首音乐",
            'count' => $imported
        ]);
        break;

    case 'list':
    default:
        echo json_encode([
            'success' => true,
            'data' => $freeMusicList,
            'count' => count($freeMusicList)
        ]);
        break;
}
?>
