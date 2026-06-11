<?php
/*
 * @Page：删除音乐
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

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => '参数错误']);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM music WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => '删除成功'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => '删除失败']);
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
