<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

include_once 'Database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => '参数错误']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM music_api_config WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => '数据不存在']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '查询失败：' . $e->getMessage()]);
}

$conn->close();
?>
