<?php
/*
 * @Page：通用音乐搜索API - 使用后台配置的API
 * @Version：Like Girl 5.2.1-Stable
 */
session_start();
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

include_once '../admin/Database.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$debug = isset($_GET['debug']) ? true : false;

if (empty($keyword)) {
    echo json_encode(['success' => false, 'message' => '请输入搜索关键词']);
    exit;
}

// 获取启用的API配置
$sql = "SELECT * FROM music_api_config WHERE is_enabled = 1 AND (api_type = 'both' OR api_type = 'search') ORDER BY sort_order ASC";
$result = $conn->query($sql);

$all_results = [];
$debug_info = [];

if ($result && $result->num_rows > 0) {
    while ($api = $result->fetch_assoc()) {
        $api_url = $api['api_url'];

        // 构造搜索URL
        if (strpos($api_url, 'music.163.com') !== false) {
            // 网易云官方API
            $search_url = $api_url . '/search/get/web?s=' . urlencode($keyword) . '&type=1&limit=20';
        } else {
            // Meting格式
            $search_url = $api_url . '?server=netease&type=search&id=' . urlencode($keyword);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $search_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $debug_info[] = [
            'api_name' => $api['name'],
            'url' => $search_url,
            'http_code' => $http_code,
            'response_length' => strlen($response),
            'response_sample' => substr($response, 0, 200)
        ];

        $data = json_decode($response, true);

        // 解析网易云官方API返回
        if (isset($data['result']['songs'])) {
            foreach ($data['result']['songs'] as $song) {
                $artists = [];
                if (isset($song['artists'])) {
                    foreach ($song['artists'] as $ar) {
                        $artists[] = $ar['name'];
                    }
                }

                $all_results[] = [
                    'id' => $song['id'] ?? '',
                    'name' => $song['name'] ?? '',
                    'artist' => implode('/', $artists) ?: '未知',
                    'album' => $song['album']['name'] ?? '',
                    'cover' => ($song['album']['picUrl'] ?? '') . '?param=200y200',
                    'duration' => intval(($song['duration'] ?? 0) / 1000),
                    'platform' => 'netease',
                    'api_name' => $api['name']
                ];
            }
            break;
        }
        // 解析Meting API返回
        elseif ($data && is_array($data)) {
            foreach ($data as $song) {
                $artist = '未知';
                if (isset($song['artist'])) {
                    if (is_array($song['artist'])) {
                        $artist = implode('/', $song['artist']);
                    } else {
                        $artist = $song['artist'];
                    }
                }

                $all_results[] = [
                    'id' => $song['id'] ?? '',
                    'name' => $song['name'] ?? '',
                    'artist' => $artist,
                    'album' => $song['album'] ?? '',
                    'cover' => $song['pic'] ?? '',
                    'duration' => intval(($song['time'] ?? 0) / 1000),
                    'platform' => 'netease',
                    'api_name' => $api['name']
                ];
            }

            if (count($all_results) > 0) {
                break;
            }
        }
    }
}

if ($debug) {
    echo json_encode([
        'success' => true,
        'data' => array_slice($all_results, 0, 20),
        'count' => count($all_results),
        'debug' => $debug_info
    ]);
} else {
    if (count($all_results) > 0) {
        echo json_encode([
            'success' => true,
            'data' => array_slice($all_results, 0, 20),
            'count' => count($all_results)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => '未找到结果，请检查后台API配置',
            'debug' => $debug_info
        ]);
    }
}

$conn->close();
?>
