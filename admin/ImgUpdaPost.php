<?php
session_start();
$file = $_SERVER['PHP_SELF'];
include_once 'connect.php';

if (isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] <> '') {
    $id = intval($_POST['id']);
    $album_id = intval($_POST['album_id']);
    $imgText = htmlspecialchars(trim($_POST['imgText']), ENT_QUOTES);
    $imgDatd = trim($_POST['imgDatd']);
    $imgUrl = htmlspecialchars(trim($_POST['imgUrl']), ENT_QUOTES);
    
    $sql = "UPDATE loveImg SET album_id = $album_id, imgText = '$imgText', imgDatd = '$imgDatd', imgUrl = '$imgUrl' WHERE id = $id";
    $result = mysqli_query($connect, $sql);
    if ($result) {
        echo "<script>alert('修改成功');location.href = 'loveImgSet.php';</script>";
    } else {
        echo "<script>alert('修改失败');history.back();</script>";
    }
} else {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>";
}
?>
