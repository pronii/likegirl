<?php
session_start();
include_once 'connect.php';

if (isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] <> '') {
    $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['code' => 400, 'message' => '无效的ID']);
        exit;
    }

    // 查询媒体信息
    $query = "SELECT * FROM loveImg WHERE id = $id";
    $result = mysqli_query($connect, $query);
    $media = mysqli_fetch_assoc($result);

    if (!$media) {
        echo json_encode(['code' => 404, 'message' => '记录不存在']);
        exit;
    }

    $mediaType = $media['media_type'] ?? 'image';

    // 删除文件
    if ($mediaType === 'video') {
        // 删除视频文件
        $videoPath = $_SERVER['DOCUMENT_ROOT'] . $media['imgUrl'];
        if (file_exists($videoPath)) {
            @unlink($videoPath);
        }

        // 删除缩略图
        if (!empty($media['thumbnail_url'])) {
            $thumbPath = $_SERVER['DOCUMENT_ROOT'] . $media['thumbnail_url'];
            if (file_exists($thumbPath)) {
                @unlink($thumbPath);
            }
        }
    } else {
        // 删除图片（现有逻辑）
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $media['imgUrl'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }

        // 删除缩略图
        if (!empty($media['thumbUrl'])) {
            $thumbPath = $_SERVER['DOCUMENT_ROOT'] . $media['thumbUrl'];
            if (file_exists($thumbPath)) {
                @unlink($thumbPath);
            }
        }
    }

    // 删除数据库记录
    $stmt = mysqli_prepare($connect, "DELETE FROM loveImg WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $deleteResult = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($deleteResult) {
        // 检查是否为AJAX请求
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['code' => 200, 'message' => '删除成功']);
        } else {
            echo "<script>alert('删除相册成功');location.href = 'loveImgSet.php';</script>";
        }
    } else {
        // 检查是否为AJAX请求
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            echo json_encode(['code' => 500, 'message' => '删除失败: ' . mysqli_error($connect)]);
        } else {
            echo "<script>alert('删除相册失败');history.back();</script>";
        }
    }

} else {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>";
}
?>
