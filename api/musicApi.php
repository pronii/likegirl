<?php
/*
 * @Page：音乐API代理 - 网易云音乐
 * @Version：Like Girl 5.2.1-Stable
 */
header("Content-Type: application/json; charset=utf-8");

class NeteaseMusicAPI {
    private $baseUrl = 'https://music.163.com/api/';

    // 搜索音乐
    public function search($keyword, $limit = 30) {
        $url = $this->baseUrl . 'search/get/web';
        $params = [
            's' => $keyword,
            'type' => 1,
            'limit' => $limit,
            'offset' => 0
        ];

        $result = $this->curl($url, $params);
        if ($result && isset($result['result']['songs'])) {
            return array_map(function($song) {
                return [
                    'id' => $song['id'],
                    'name' => $song['name'],
                    'artist' => implode('/', array_map(function($ar) {
                        return $ar['name'];
                    }, $song['artists'])),
                    'album' => $song['album']['name'],
                    'cover' => $song['album']['picUrl'] ?? '',
                    'duration' => intval($song['duration'] / 1000)
                ];
            }, $result['result']['songs']);
        }
        return [];
    }

    // 获取音乐播放URL
    public function getMusicUrl($id) {
        $url = 'https://music.163.com/song/media/outer/url?id=' . $id . '.mp3';
        return $url;
    }

    // 获取音乐详情
    public function getDetail($id) {
        $url = $this->baseUrl . 'song/detail';
        $params = ['id' => $id, 'ids' => "[$id]"];

        $result = $this->curl($url, $params);
        if ($result && isset($result['songs'][0])) {
            $song = $result['songs'][0];
            return [
                'id' => $song['id'],
                'name' => $song['name'],
                'artist' => implode('/', array_map(function($ar) {
                    return $ar['name'];
                }, $song['artists'])),
                'album' => $song['album']['name'],
                'cover' => $song['album']['picUrl'] ?? '',
                'duration' => intval($song['duration'] / 1000)
            ];
        }
        return null;
    }

    private function curl($url, $params = []) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

// 路由处理
$action = $_GET['action'] ?? '';
$api = new NeteaseMusicAPI();

switch ($action) {
    case 'search':
        $keyword = $_GET['keyword'] ?? '';
        if (empty($keyword)) {
            echo json_encode(['success' => false, 'message' => '请输入搜索关键词']);
            exit;
        }
        $result = $api->search($keyword);
        echo json_encode(['success' => true, 'data' => $result]);
        break;

    case 'url':
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => '缺少音乐ID']);
            exit;
        }
        $url = $api->getMusicUrl($id);
        echo json_encode(['success' => true, 'url' => $url]);
        break;

    case 'detail':
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => '缺少音乐ID']);
            exit;
        }
        $detail = $api->getDetail($id);
        if ($detail) {
            echo json_encode(['success' => true, 'data' => $detail]);
        } else {
            echo json_encode(['success' => false, 'message' => '获取失败']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => '无效的操作']);
        break;
}
?>
