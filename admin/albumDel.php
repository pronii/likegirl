<?php
session_start();
$file = $_SERVER['PHP_SELF'];
include_once 'connect.php';

if (isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] <> '') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        // 获取默认相册ID（使用预处理语句）
        $defaultAlbum = "SELECT id FROM love_album WHERE album_name = ? LIMIT 1";
        $stmt = mysqli_prepare($connect, $defaultAlbum);
        $defaultAlbumName = '默认相册';
        mysqli_stmt_bind_param($stmt, "s", $defaultAlbumName);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $defaultAlbumId);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        if (!$defaultAlbumId) {
            $defaultAlbumId = 1; // 如果找不到默认相册，使用ID=1
        }

        // 将该相册下的图片移动到默认相册（使用预处理语句）
        $updateSql = "UPDATE loveImg SET album_id = ? WHERE album_id = ?";
        $stmt = mysqli_prepare($connect, $updateSql);
        mysqli_stmt_bind_param($stmt, "ii", $defaultAlbumId, $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // 删除相册（使用预处理语句）
        $sql = "DELETE FROM love_album WHERE id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if ($result) {
            echo "<script>alert('相册删除成功');location.href = 'albumSet.php';</script>";
        } else {
            echo "<script>alert('相册删除失败');history.back();</script>";
        }
    } else {
        echo "<script>alert('参数错误');history.back();</script>";
    }
} else {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>";
}
?>