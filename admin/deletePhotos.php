<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include_once 'connect.php';

// 检查登录状态
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    echo json_encode(['code' => 401, 'message' => '未登录或登录已过期']);
    exit;
}

// 检查请求方法
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['code' => 405, 'message' => '请求方法错误']);
    exit;
}

// 获取要删除的照片ID数组
$ids = isset($_POST['ids']) ? $_POST['ids'] : [];

if (empty($ids) || !is_array($ids)) {
    echo json_encode(['code' => 400, 'message' => '参数错误，缺少照片ID']);
    exit;
}

// 过滤和验证ID数组
$validIds = [];
foreach ($ids as $id) {
    $id = intval($id);
    if ($id > 0) {
        $validIds[] = $id;
    }
}

if (empty($validIds)) {
    echo json_encode(['code' => 400, 'message' => '无效的照片ID']);
    exit;
}

// 开始事务处理
mysqli_begin_transaction($connect);

try {
    // 构建删除SQL
    $idsString = implode(',', $validIds);
    
    // 删除照片记录
    $sql = "DELETE FROM loveImg WHERE id IN ($idsString)";
    $result = mysqli_query($connect, $sql);
    
    if (!$result) {
        throw new Exception('删除照片失败');
    }
    
    $deletedCount = mysqli_affected_rows($connect);
    
    // 提交事务
    mysqli_commit($connect);
    
    echo json_encode([
        'code' => 200, 
        'message' => "成功删除 {$deletedCount} 张照片",
        'deleted_count' => $deletedCount
    ]);
    
} catch (Exception $e) {
    // 回滚事务
    mysqli_rollback($connect);
    echo json_encode([
        'code' => 500, 
        'message' => '删除失败: ' . $e->getMessage()
    ]);
}
?>