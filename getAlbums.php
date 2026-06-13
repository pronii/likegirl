<?php
// ===== 缓存优化头 =====
header('Content-Type: application/json');
// 相册列表缓存 5 分钟
header('Cache-Control: public, max-age=300');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');

include_once 'admin/connect.php';
include_once 'admin/Function.php';

try {
    // 获取所有相册及照片数量
    $sql = "SELECT a.*, COUNT(l.id) as photo_count 
            FROM love_album a 
            LEFT JOIN loveImg l ON a.id = l.album_id 
            GROUP BY a.id 
            ORDER BY a.sort_order ASC, a.create_time DESC";
    
    $result = mysqli_query($connect, $sql);
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'album_name' => $row['album_name'],
            'album_cover' => $row['album_cover'],
            'album_desc' => $row['album_desc'],
            'photo_count' => $row['photo_count'],
            'create_time' => $row['create_time']
        ];
    }
    
    echo json_encode([
        'code' => 200,
        'data' => $data
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'code' => 500,
        'message' => $e->getMessage()
    ]);
}
?>