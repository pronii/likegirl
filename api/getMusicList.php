<?php
/*
 * @Page：前端获取音乐列表API
 * @Version：Like Girl 5.2.1-Stable
 */
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

include_once '../admin/Database.php';

try {
    $sql = "SELECT id, title, artist, music_url, cover_url, duration FROM music WHERE is_enabled = 1 ORDER BY sort_order ASC, id DESC";
    $result = $conn->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // 只返回有有效URL的音乐
            if (!empty($row['music_url'])) {
                $data[] = $row;
            }
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $data,
        'count' => count($data)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '查询失败',
        'data' => []
    ]);
}

$conn->close();
?>
