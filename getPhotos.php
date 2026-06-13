<?php
// ===== 缓存优化头 =====
header('Content-Type: application/json');
// 短期缓存（5分钟），因为相册内容可能更新
header('Cache-Control: public, max-age=300');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');

include_once 'admin/connect.php';
include_once 'admin/Function.php';

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 12;
$album_id = isset($_POST['album_id']) ? intval($_POST['album_id']) : 0;
$offset = ($page - 1) * $limit;

// 根据是否指定相册ID来构建查询
if ($album_id > 0) {
    // 查询指定相册的照片总数
    $totalRes = $connect->prepare("SELECT COUNT(*) as total FROM loveImg WHERE album_id = ?");
    $totalRes->bind_param("i", $album_id);
    $totalRes->execute();
    $total = $totalRes->get_result()->fetch_assoc()['total'];
    
    // 预处理分页查询指定相册的照片 - 增加 id
    $stmt = $connect->prepare("SELECT id, imgUrl, imgDatd, imgText FROM loveImg WHERE album_id = ? ORDER BY id DESC LIMIT ?, ?");
    $stmt->bind_param("iii", $album_id, $offset, $limit);
} else {
    // 查询所有照片总数
    $totalRes = $connect->query("SELECT COUNT(*) as total FROM loveImg");
    $total = $totalRes->fetch_assoc()['total'];
    
    // 预处理分页查询所有照片 - 增加 id
    $stmt = $connect->prepare("SELECT id, imgUrl, imgDatd, imgText FROM loveImg ORDER BY id DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id' => $row['id'],
        'img' => $row['imgUrl'],
        'date' => $row['imgDatd'],
        'text' => $row['imgText']
    ];
}

echo json_encode([
    'code' => 200,
    'data' => $data,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'album_id' => $album_id
]);
?>

