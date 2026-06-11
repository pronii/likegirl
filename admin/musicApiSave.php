<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

include_once 'Database.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$api_url = isset($_POST['api_url']) ? trim($_POST['api_url']) : '';
$api_type = isset($_POST['api_type']) ? trim($_POST['api_type']) : 'both';
$sort_order = isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0;
$is_enabled = isset($_POST['is_enabled']) ? intval($_POST['is_enabled']) : 1;

if (empty($name) || empty($api_url)) {
    echo json_encode(['success' => false, 'message' => 'API名称和地址不能为空']);
    exit;
}

try {
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE music_api_config SET name=?, api_url=?, api_type=?, sort_order=?, is_enabled=? WHERE id=?");
        $stmt->bind_param("sssiii", $name, $api_url, $api_type, $sort_order, $is_enabled, $id);
        $message = '更新成功';
    } else {
        $stmt = $conn->prepare("INSERT INTO music_api_config (name, api_url, api_type, sort_order, is_enabled) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $name, $api_url, $api_type, $sort_order, $is_enabled);
        $message = '添加成功';
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => '保存失败']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => '操作失败：' . $e->getMessage()]);
}

$conn->close();
?>
