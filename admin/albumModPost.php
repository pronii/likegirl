<?php
session_start();
$file = $_SERVER['PHP_SELF'];
include_once 'connect.php';

if (isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] <> '') {
    $id = intval($_POST['id']);
    $album_name = htmlspecialchars(trim($_POST['album_name']), ENT_QUOTES);
    $album_cover = htmlspecialchars(trim($_POST['album_cover']), ENT_QUOTES);
    $album_desc = htmlspecialchars(trim($_POST['album_desc']), ENT_QUOTES);
    $sort_order = intval($_POST['sort_order']);

    // 使用预处理语句防止SQL注入
    if (empty($album_cover)) {
        $sql = "UPDATE love_album SET album_name = ?, album_cover = NULL, album_desc = ?, sort_order = ? WHERE id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "ssii", $album_name, $album_desc, $sort_order, $id);
    } else {
        $sql = "UPDATE love_album SET album_name = ?, album_cover = ?, album_desc = ?, sort_order = ? WHERE id = ?";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, "sssii", $album_name, $album_cover, $album_desc, $sort_order, $id);
    }

    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
        echo "<script>alert('相册修改成功');location.href = 'albumSet.php';</script>";
    } else {
        echo "<script>alert('相册修改失败');history.back();</script>";
    }
} else {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>";
}
?>