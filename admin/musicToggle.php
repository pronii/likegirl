<?php
/*
 * @Page：切换音乐状态
 * @Version：Like Girl 5.2.1-Stable
 */
session_start();
header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    echo json_encode(['success' => false, 'message' => '未登录']);
    exit;
}

include_once 'Database.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$is_enabled = isset($_POST['is_enabled']) ? intval($_POST['is_enabled']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => '参数错误']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE music SET is_enabled = ? WHERE id = ?");
    $stmt->bind_param("ii", $is_enabled, $id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => $is_enabled == 1 ? '已启用' : '已禁用'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '操作失败']);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '操作失败：' . $e->getMessage()
    ]);
}

$conn->close();
?>
