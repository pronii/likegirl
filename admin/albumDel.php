<?php
session_start();
$file = $_SERVER['PHP_SELF'];
include_once 'connect.php';

if (isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] <> '') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        // 获取默认相册ID
        $defaultAlbum = "SELECT id FROM love_album WHERE album_name = '默认相册' LIMIT 1";
        $defaultRes = mysqli_query($connect, $defaultAlbum);
        $defaultRow = mysqli_fetch_array($defaultRes);
        $defaultAlbumId = $defaultRow ? $defaultRow['id'] : 1;
        
        // 将该相册下的图片移动到默认相册
        $updateSql = "UPDATE loveImg SET album_id = $defaultAlbumId WHERE album_id = $id";
        mysqli_query($connect, $updateSql);
        
        // 删除相册
        $sql = "DELETE FROM love_album WHERE id = $id";
        $result = mysqli_query($connect, $sql);
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