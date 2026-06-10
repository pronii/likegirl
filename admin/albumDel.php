<?php
session_start();
$file = $_SERVER['PHP_SELF'];
include_once 'connect.php';

function back_with_alert($message) {
    echo "<script>alert('" . addslashes($message) . "');history.back();</script>";
    exit;
}

function go_album_list($message) {
    echo "<script>alert('" . addslashes($message) . "');location.href = 'albumSet.php';</script>";
    exit;
}

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] === '') {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=" . addslashes($file) . "';</script>";
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    back_with_alert('参数错误');
}

mysqli_begin_transaction($connect);

try {
    $targetStmt = mysqli_prepare($connect, "SELECT id FROM love_album WHERE id = ? LIMIT 1");
    if (!$targetStmt) {
        throw new Exception('准备相册校验语句失败: ' . mysqli_error($connect));
    }
    mysqli_stmt_bind_param($targetStmt, 'i', $id);
    mysqli_stmt_execute($targetStmt);
    $targetResult = mysqli_stmt_get_result($targetStmt);
    if (!$targetResult || mysqli_num_rows($targetResult) === 0) {
        mysqli_stmt_close($targetStmt);
        throw new Exception('相册不存在');
    }
    mysqli_stmt_close($targetStmt);

    $replacementAlbumId = 0;
    $replacementSql = "SELECT id FROM love_album
                       WHERE id <> ?
                       ORDER BY
                         CASE
                           WHEN album_name = 'Default Album' THEN 0
                           WHEN album_name = '默认相册' THEN 1
                           WHEN id = 1 THEN 2
                           ELSE 3
                         END,
                         sort_order ASC,
                         id ASC
                       LIMIT 1";
    $replacementStmt = mysqli_prepare($connect, $replacementSql);
    if (!$replacementStmt) {
        throw new Exception('准备替代相册查询失败: ' . mysqli_error($connect));
    }
    mysqli_stmt_bind_param($replacementStmt, 'i', $id);
    mysqli_stmt_execute($replacementStmt);
    $replacementResult = mysqli_stmt_get_result($replacementStmt);
    if ($replacementResult && ($replacementRow = mysqli_fetch_assoc($replacementResult))) {
        $replacementAlbumId = intval($replacementRow['id']);
    }
    mysqli_stmt_close($replacementStmt);

    if ($replacementAlbumId <= 0) {
        $createStmt = mysqli_prepare($connect, "INSERT INTO love_album (album_name, album_desc, sort_order, create_time) VALUES ('Default Album', 'All uncategorized photos', 0, NOW())");
        if (!$createStmt || !mysqli_stmt_execute($createStmt)) {
            throw new Exception('创建默认相册失败: ' . mysqli_error($connect));
        }
        $replacementAlbumId = mysqli_insert_id($connect);
        mysqli_stmt_close($createStmt);
    }

    $updateStmt = mysqli_prepare($connect, "UPDATE loveImg SET album_id = ? WHERE album_id = ?");
    if (!$updateStmt) {
        throw new Exception('准备照片迁移语句失败: ' . mysqli_error($connect));
    }
    mysqli_stmt_bind_param($updateStmt, 'ii', $replacementAlbumId, $id);
    if (!mysqli_stmt_execute($updateStmt)) {
        throw new Exception('迁移相册照片失败: ' . mysqli_stmt_error($updateStmt));
    }
    mysqli_stmt_close($updateStmt);

    $deleteStmt = mysqli_prepare($connect, "DELETE FROM love_album WHERE id = ?");
    if (!$deleteStmt) {
        throw new Exception('准备删除相册语句失败: ' . mysqli_error($connect));
    }
    mysqli_stmt_bind_param($deleteStmt, 'i', $id);
    if (!mysqli_stmt_execute($deleteStmt)) {
        throw new Exception('删除相册失败: ' . mysqli_stmt_error($deleteStmt));
    }
    $deletedRows = mysqli_stmt_affected_rows($deleteStmt);
    mysqli_stmt_close($deleteStmt);

    if ($deletedRows <= 0) {
        throw new Exception('相册删除失败');
    }

    mysqli_commit($connect);
    go_album_list('相册删除成功，原相册照片已转移');
} catch (Exception $e) {
    mysqli_rollback($connect);
    back_with_alert($e->getMessage());
}
?>
