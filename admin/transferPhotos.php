<?php
session_start();

ob_start();
include_once 'connect.php';
ob_end_clean();

header('Content-Type: application/json; charset=utf-8');

function json_response($code, $message, $extra = []) {
    echo json_encode(array_merge([
        'code' => $code,
        'message' => $message
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] === '') {
    json_response(401, '未登录或登录已过期');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, '请求方法不支持');
}

$ids = isset($_POST['ids']) ? $_POST['ids'] : [];
$targetAlbumId = isset($_POST['album_id']) ? intval($_POST['album_id']) : 0;

if (empty($ids) || (!is_array($ids) && !is_string($ids))) {
    json_response(400, '请选择需要转移的照片');
}

if ($targetAlbumId <= 0) {
    json_response(400, '目标相册ID无效');
}

if (is_string($ids)) {
    $decoded = json_decode($ids, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $ids = $decoded;
    } else {
        $ids = array_filter(array_map('trim', explode(',', $ids)));
    }
}

$validIds = [];
foreach ($ids as $id) {
    $id = intval($id);
    if ($id > 0) {
        $validIds[] = $id;
    }
}
$validIds = array_values(array_unique($validIds));

if (empty($validIds)) {
    json_response(400, '无效的照片ID');
}

$albumStmt = mysqli_prepare($connect, "SELECT id, album_name FROM love_album WHERE id = ?");
if (!$albumStmt) {
    json_response(500, '验证目标相册失败: ' . mysqli_error($connect));
}
mysqli_stmt_bind_param($albumStmt, 'i', $targetAlbumId);
mysqli_stmt_execute($albumStmt);
$albumResult = mysqli_stmt_get_result($albumStmt);
if (!$albumResult || mysqli_num_rows($albumResult) === 0) {
    mysqli_stmt_close($albumStmt);
    json_response(400, '目标相册不存在');
}
$targetAlbum = mysqli_fetch_assoc($albumResult);
mysqli_stmt_close($albumStmt);

mysqli_begin_transaction($connect);

try {
    $placeholders = implode(',', array_fill(0, count($validIds), '?'));

    $countStmt = mysqli_prepare($connect, "SELECT COUNT(*) AS total FROM loveImg WHERE id IN ($placeholders)");
    if (!$countStmt) {
        throw new Exception('准备照片校验语句失败: ' . mysqli_error($connect));
    }
    $countTypes = str_repeat('i', count($validIds));
    $countParams = [$countStmt, $countTypes];
    foreach ($validIds as $key => $value) {
        $countParams[] = &$validIds[$key];
    }
    call_user_func_array('mysqli_stmt_bind_param', $countParams);
    if (!mysqli_stmt_execute($countStmt)) {
        throw new Exception('校验照片失败: ' . mysqli_stmt_error($countStmt));
    }
    $countResult = mysqli_stmt_get_result($countStmt);
    $countRow = mysqli_fetch_assoc($countResult);
    $matchedRows = intval($countRow['total']);
    mysqli_stmt_close($countStmt);

    if ($matchedRows === 0) {
        throw new Exception('没有找到可转移的照片，请检查照片ID是否正确');
    }

    $updateStmt = mysqli_prepare($connect, "UPDATE loveImg SET album_id = ? WHERE id IN ($placeholders)");
    if (!$updateStmt) {
        throw new Exception('准备转移语句失败: ' . mysqli_error($connect));
    }
    $types = str_repeat('i', count($validIds) + 1);
    $bindValues = array_merge([$targetAlbumId], $validIds);
    $params = [$updateStmt, $types];
    foreach ($bindValues as $key => $value) {
        $params[] = &$bindValues[$key];
    }
    call_user_func_array('mysqli_stmt_bind_param', $params);
    if (!mysqli_stmt_execute($updateStmt)) {
        throw new Exception('执行转移失败: ' . mysqli_stmt_error($updateStmt));
    }
    $affectedRows = mysqli_stmt_affected_rows($updateStmt);
    mysqli_stmt_close($updateStmt);

    mysqli_commit($connect);

    json_response(200, $affectedRows > 0 ? "成功转移 {$affectedRows} 张照片" : '所选照片已在目标相册中，无需转移', [
        'affected_count' => $affectedRows,
        'matched_count' => $matchedRows,
        'album_id' => $targetAlbumId,
        'album_name' => $targetAlbum['album_name']
    ]);
} catch (Exception $e) {
    mysqli_rollback($connect);
    json_response(500, '转移失败: ' . $e->getMessage());
}
?>
