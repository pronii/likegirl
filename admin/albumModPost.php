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
    
    // 如果封面URL为空，设置为NULL
    if (empty($album_cover)) {
        $album_cover_sql = "album_cover = NULL";
    } else {
        $album_cover_sql = "album_cover = '$album_cover'";
    }
    
    $sql = "UPDATE love_album SET album_name = '$album_name', $album_cover_sql, album_desc = '$album_desc', sort_order = $sort_order WHERE id = $id";
    $result = mysqli_query($connect, $sql);
    if ($result) {
        echo "<script>alert('相册修改成功');location.href = 'albumSet.php';</script>";
    } else {
        echo "<script>alert('相册修改失败');history.back();</script>";
    }
} else {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>";
}
?>