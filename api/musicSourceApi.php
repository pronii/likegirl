<?php
/*
 * @Page：获取音乐多音源API - 基于Meting
 * @Version：Like Girl 5.2.1-Stable
 */
header("Content-Type: application/json; charset=utf-8");
include_once '../admin/Database.php';

$song_id = isset($_GET['song_id']) ? trim($_GET['song_id']) : '';
$platform = isset($_GET['platform']) ? trim($_GET['platform']) : 'netease';

if (empty($song_id)) {
    echo json_encode(['success' => false, 'message' => '缺少歌曲ID']);
    exit;
}

// 获取启用的API配置
$sql = "SELECT * FROM music_api_config WHERE is_enabled = 1 ORDER BY sort_order ASC";
$result = $conn->query($sql);

$sources = [];

if ($result && $result->num_rows > 0) {
    while ($api = $result->fetch_assoc()) {
        // 使用Meting API获取音乐URL
        $music_url = getMusicUrlFromMeting($api['api_url'], $platform, $song_id);

        if ($music_url) {
            $sources[] = [
                'name' => $api['name'],
                'url' => $music_url
            ];
        }
    }
}

echo json_encode([
    'success' => true,
    'sources' => $sources,
    'count' => count($sources)
]);

$conn->close();

// 从Meting API获取真实播放地址
function getMusicUrlFromMeting($api_base, $platform, $song_id) {
    $url = $api_base . '?server=' . $platform . '&type=url&id=' . $song_id;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    // 检查是否是有效的音乐URL
    if ($final_url && strpos($final_url, '.mp3') !== false || strpos($final_url, '.m4a') !== false) {
        return $final_url;
    }

    return $url;
}
?>
