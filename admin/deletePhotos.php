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
    // 先查询要删除的照片数据，用于返回和撤销功能
    $placeholders = implode(',', array_fill(0, count($validIds), '?'));
    $selectSql = "SELECT id, img, text, date, album_id FROM loveImg WHERE id IN ($placeholders)";
    $selectStmt = mysqli_prepare($connect, $selectSql);

    if (!$selectStmt) {
        throw new Exception('准备查询语句失败: ' . mysqli_error($connect));
    }

    // 绑定查询参数
    $types = str_repeat('i', count($validIds));
    $params = [$selectStmt, $types];
    foreach ($validIds as $key => $value) {
        $params[] = &$validIds[$key];
    }
    call_user_func_array('mysqli_stmt_bind_param', $params);

    $result = mysqli_stmt_execute($selectStmt);
    if (!$result) {
        throw new Exception('查询照片数据失败: ' . mysqli_stmt_error($selectStmt));
    }

    // 获取查询结果
    $selectResult = mysqli_stmt_get_result($selectStmt);
    $deletedPhotos = [];
    while ($row = mysqli_fetch_assoc($selectResult)) {
        $deletedPhotos[] = [
            'id' => $row['id'],
            'img' => $row['img'],
            'text' => $row['text'],
            'date' => $row['date'],
            'album_id' => $row['album_id']
        ];
    }
    mysqli_stmt_close($selectStmt);

    // 使用预处理语句构建删除SQL，防止SQL注入
    $deleteSql = "DELETE FROM loveImg WHERE id IN ($placeholders)";
    $deleteStmt = mysqli_prepare($connect, $deleteSql);

    if (!$deleteStmt) {
        throw new Exception('准备删除语句失败: ' . mysqli_error($connect));
    }

    // 动态绑定删除参数
    $params = [$deleteStmt, $types];
    foreach ($validIds as $key => $value) {
        $params[] = &$validIds[$key];
    }
    call_user_func_array('mysqli_stmt_bind_param', $params);

    $result = mysqli_stmt_execute($deleteStmt);
    if (!$result) {
        throw new Exception('执行删除失败: ' . mysqli_stmt_error($deleteStmt));
    }

    $deletedCount = mysqli_stmt_affected_rows($deleteStmt);
    mysqli_stmt_close($deleteStmt);

    // 提交事务
    mysqli_commit($connect);

    echo json_encode([
        'code' => 200,
        'message' => "成功删除 {$deletedCount} 张照片",
        'deleted_count' => $deletedCount,
        'deleted_photos' => $deletedPhotos
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