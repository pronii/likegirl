<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include_once 'admin/connect.php';

// 检查登录状态
if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    echo json_encode(['code' => 401, 'message' => '未登录或登录已过期']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    $target_album_id = isset($_POST['album_id']) ? intval($_POST['album_id']) : 0;

    if (empty($ids) || !is_array($ids)) {
        echo json_encode(['code' => 400, 'message' => '请选择需要转移的照片']);
        exit;
    }

    if ($target_album_id <= 0) {
        echo json_encode(['code' => 400, 'message' => '目标相册ID无效']);
        exit;
    }

    // 验证目标相册是否存在
    $checkAlbumSql = "SELECT id FROM love_album WHERE id = ?";
    $checkStmt = mysqli_prepare($connect, $checkAlbumSql);
    if ($checkStmt) {
        mysqli_stmt_bind_param($checkStmt, "i", $target_album_id);
        mysqli_stmt_execute($checkStmt);
        $checkResult = mysqli_stmt_get_result($checkStmt);
        if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
            echo json_encode(['code' => 400, 'message' => '目标相册不存在']);
            exit;
        }
        mysqli_stmt_close($checkStmt);
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
        // 准备 ID 列表
        $idList = implode(',', $validIds);
        
        $sql = "UPDATE loveImg SET album_id = ? WHERE id IN ($idList)";
        $stmt = mysqli_prepare($connect, $sql);
        
        if (!$stmt) {
            throw new Exception('准备SQL语句失败: ' . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $target_album_id);
        $result = mysqli_stmt_execute($stmt);
        
        if (!$result) {
            throw new Exception('执行SQL失败: ' . mysqli_stmt_error($stmt));
        }
        
        $affectedRows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        if ($affectedRows == 0) {
            throw new Exception('没有照片被更新，请检查照片ID是否正确');
        }
        
        // 提交事务
        mysqli_commit($connect);
        
        echo json_encode([
            'code' => 200, 
            'message' => "成功转移 {$affectedRows} 张照片",
            'affected_count' => $affectedRows
        ]);
        
    } catch (Exception $e) {
        // 回滚事务
        mysqli_rollback($connect);
        echo json_encode([
            'code' => 500, 
            'message' => '转移失败: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['code' => 405, 'message' => '请求方法不支持']);
}
?>
