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

// 获取操作类型
$operationType = isset($_POST['operation_type']) ? trim($_POST['operation_type']) : '';

if (empty($operationType)) {
    echo json_encode(['code' => 400, 'message' => '缺少操作类型参数']);
    exit;
}

// 根据操作类型进行不同的恢复处理
switch ($operationType) {
    case 'delete':
        restoreDeletedPhotos($connect);
        break;
    case 'transfer':
        restoreTransferredPhotos($connect);
        break;
    default:
        echo json_encode(['code' => 400, 'message' => '不支持的操作类型']);
        exit;
}

/**
 * 恢复已删除的照片
 * 需要提供照片的完整数据用于重新插入
 */
function restoreDeletedPhotos($connect) {
    // 获取恢复数据（JSON格式的照片数据数组）
    $restoreData = isset($_POST['restore_data']) ? $_POST['restore_data'] : '';

    if (empty($restoreData)) {
        echo json_encode(['code' => 400, 'message' => '缺少恢复数据']);
        exit;
    }

    // 解析JSON数据
    $photos = json_decode($restoreData, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($photos) || empty($photos)) {
        echo json_encode(['code' => 400, 'message' => '恢复数据格式错误或为空']);
        exit;
    }

    // 开始事务处理
    mysqli_begin_transaction($connect);

    try {
        $restoredCount = 0;
        $stmt = mysqli_prepare($connect, "INSERT INTO loveImg (id, img, text, album_id, created_at) VALUES (?, ?, ?, ?, ?)");

        if (!$stmt) {
            throw new Exception('准备SQL语句失败: ' . mysqli_error($connect));
        }

        foreach ($photos as $photo) {
            // 验证必需字段
            if (!isset($photo['id']) || !isset($photo['img'])) {
                continue;
            }

            $id = intval($photo['id']);
            $img = trim($photo['img']);
            $text = isset($photo['text']) ? trim($photo['text']) : '';
            $album_id = isset($photo['album_id']) ? intval($photo['album_id']) : 1;
            $created_at = isset($photo['created_at']) ? trim($photo['created_at']) : date('Y-m-d H:i:s');

            // 检查照片是否已经存在（可能已经恢复过）
            $checkStmt = mysqli_prepare($connect, "SELECT id FROM loveImg WHERE id = ?");
            mysqli_stmt_bind_param($checkStmt, "i", $id);
            mysqli_stmt_execute($checkStmt);
            $checkResult = mysqli_stmt_get_result($checkStmt);

            if (mysqli_num_rows($checkResult) > 0) {
                mysqli_stmt_close($checkStmt);
                continue; // 跳过已存在的照片
            }
            mysqli_stmt_close($checkStmt);

            // 插入照片数据
            mysqli_stmt_bind_param($stmt, "isssi", $id, $img, $text, $album_id, $created_at);
            $result = mysqli_stmt_execute($stmt);

            if (!$result) {
                throw new Exception('插入照片数据失败: ' . mysqli_stmt_error($stmt));
            }

            $restoredCount++;
        }

        mysqli_stmt_close($stmt);

        if ($restoredCount === 0) {
            throw new Exception('没有照片被恢复，可能照片已存在或数据无效');
        }

        // 提交事务
        mysqli_commit($connect);

        echo json_encode([
            'code' => 200,
            'message' => "成功恢复 {$restoredCount} 张照片",
            'restored_count' => $restoredCount
        ]);

    } catch (Exception $e) {
        // 回滚事务
        mysqli_rollback($connect);
        echo json_encode([
            'code' => 500,
            'message' => '恢复失败: ' . $e->getMessage()
        ]);
    }
}

/**
 * 恢复已转移的照片到原相册
 * 需要提供照片ID和原相册ID
 */
function restoreTransferredPhotos($connect) {
    // 获取照片ID数组
    $ids = isset($_POST['ids']) ? $_POST['ids'] : [];
    // 获取恢复数据（包含每张照片的原始相册ID）
    $restoreData = isset($_POST['restore_data']) ? $_POST['restore_data'] : '';

    if (empty($ids) || (!is_array($ids) && !is_string($ids))) {
        echo json_encode(['code' => 400, 'message' => '缺少照片ID参数']);
        exit;
    }

    if (empty($restoreData)) {
        echo json_encode(['code' => 400, 'message' => '缺少恢复数据']);
        exit;
    }

    // 解析恢复数据
    $albumMapping = json_decode($restoreData, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($albumMapping)) {
        echo json_encode(['code' => 400, 'message' => '恢复数据格式错误']);
        exit;
    }

    // 处理ID数组
    $validIds = [];
    if (is_string($ids)) {
        $decoded = json_decode($ids, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $ids = $decoded;
        } else {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }
    }

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
        $restoredCount = 0;
        $stmt = mysqli_prepare($connect, "UPDATE loveImg SET album_id = ? WHERE id = ?");

        if (!$stmt) {
            throw new Exception('准备SQL语句失败: ' . mysqli_error($connect));
        }

        foreach ($validIds as $photoId) {
            // 获取该照片的原始相册ID
            if (!isset($albumMapping[$photoId])) {
                continue; // 如果没有映射数据，跳过
            }

            $originalAlbumId = intval($albumMapping[$photoId]);

            // 验证原始相册是否存在
            $checkAlbumStmt = mysqli_prepare($connect, "SELECT id FROM love_album WHERE id = ?");
            if ($checkAlbumStmt) {
                mysqli_stmt_bind_param($checkAlbumStmt, "i", $originalAlbumId);
                mysqli_stmt_execute($checkAlbumStmt);
                $checkResult = mysqli_stmt_get_result($checkAlbumStmt);

                if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
                    mysqli_stmt_close($checkAlbumStmt);
                    continue; // 原相册不存在，跳过
                }
                mysqli_stmt_close($checkAlbumStmt);
            }

            // 恢复照片到原相册
            mysqli_stmt_bind_param($stmt, "ii", $originalAlbumId, $photoId);
            $result = mysqli_stmt_execute($stmt);

            if (!$result) {
                throw new Exception('更新照片相册失败: ' . mysqli_stmt_error($stmt));
            }

            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $restoredCount++;
            }
        }

        mysqli_stmt_close($stmt);

        if ($restoredCount === 0) {
            throw new Exception('没有照片被恢复，请检查照片ID和相册数据');
        }

        // 提交事务
        mysqli_commit($connect);

        echo json_encode([
            'code' => 200,
            'message' => "成功恢复 {$restoredCount} 张照片到原相册",
            'restored_count' => $restoredCount
        ]);

    } catch (Exception $e) {
        // 回滚事务
        mysqli_rollback($connect);
        echo json_encode([
            'code' => 500,
            'message' => '恢复失败: ' . $e->getMessage()
        ]);
    }
}
?>
