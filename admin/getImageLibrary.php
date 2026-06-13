<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include_once 'connect.php';

// 权限检查
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    echo json_encode(['success' => false, 'message' => '未登录'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 分页参数
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 24;
$album_id = isset($_GET['album_id']) ? intval($_GET['album_id']) : -1;
$offset = ($page - 1) * $limit;

// 构建查询条件（album_id >= 0 时按相册筛选，否则查询全部）
$where = '';
if ($album_id >= 0) {
    $where = " WHERE album_id = " . $album_id;
}

// 查询总数
$countSql = "SELECT COUNT(*) as total FROM loveImg" . $where;
$countRes = mysqli_query($connect, $countSql);
$countRow = mysqli_fetch_array($countRes);
$total = intval($countRow['total']);

// 查询图片列表（优先用缩略图，没有则用原图）
$sql = "SELECT id, imgUrl, thumbUrl, imgText, imgDatd FROM loveImg" . $where . " ORDER BY id DESC LIMIT $offset, $limit";
$res = mysqli_query($connect, $sql);

$images = [];
while ($row = mysqli_fetch_array($res)) {
    $images[] = [
        'id' => intval($row['id']),
        'imgUrl' => $row['imgUrl'],
        'thumbUrl' => !empty($row['thumbUrl']) ? $row['thumbUrl'] : $row['imgUrl'],
        'imgText' => $row['imgText'],
        'imgDatd' => $row['imgDatd']
    ];
}

echo json_encode([
    'success' => true,
    'data' => $images,
    'total' => $total,
    'page' => $page,
    'limit' => $limit,
    'hasMore' => ($offset + count($images)) < $total
], JSON_UNESCAPED_UNICODE);
