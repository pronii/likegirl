<?php
/*
 * @Page：音乐列表API
 * @Version：Like Girl 5.2.1-Stable
 */
session_start();
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

include_once 'Database.php';

try {
    $sql = "SELECT * FROM music ORDER BY sort_order ASC, id DESC";
    $result = $conn->query($sql);

    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
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
        'message' => '查询失败：' . $e->getMessage()
    ]);
}

$conn->close();
?>
