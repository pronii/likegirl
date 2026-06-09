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

    // 使用预处理语句防止SQL注入
    $sql = "UPDATE loveImg SET album_id = ?, imgText = ?, imgDatd = ?, imgUrl = ? WHERE id = ?";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, "isssi", $album_id, $imgText, $imgDatd, $imgUrl, $id);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        echo "<script>Swal.fire({icon:'success',title:'修改成功',confirmButtonText:'好的'}).then(()=>{location.href='loveImgSet.php'});</script>";
    } else {
        echo "<script>Swal.fire({icon:'error',title:'修改失败',confirmButtonText:'好的'}).then(()=>{history.back()});</script>";
    }
    mysqli_stmt_close($stmt);
} else {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>";
}
?>

