<?php
session_start();
$file = $_SERVER['PHP_SELF'];

include_once 'connect.php';

if (isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] <> '') {
    $album_id = intval($_POST['album_id']);
    $imgText = htmlspecialchars(trim($_POST['imgText']), ENT_QUOTES);
    $imgDatd = trim($_POST['imgDatd']);
    $imgUrl = htmlspecialchars(trim($_POST['imgUrl']), ENT_QUOTES);
    
    $charu = "INSERT INTO loveImg (album_id, imgDatd, imgText, imgUrl) VALUES ($album_id, '$imgDatd', '$imgText', '$imgUrl')";
    $result = mysqli_query($connect, $charu);
    if ($result) {
        echo "<script>alert('图片添加成功');location.href = 'loveImgSet.php';</script>";
    } else {
        echo "<script>alert('图片添加失败');history.back();</script>";
    }
} else {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>";
}
?>
