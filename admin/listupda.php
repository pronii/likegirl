<?php
session_start();

$name = htmlspecialchars(trim($_POST['eventname']),ENT_QUOTES);
$icon = $_POST['icon'];
$id = $_POST['id'];
$img = htmlspecialchars($_POST['imgurl'],ENT_QUOTES);
$file = $_SERVER['PHP_SELF'];
include_once 'connect.php';
if (!empty($img)) {
    $img = htmlspecialchars($_POST['imgurl'],ENT_QUOTES);
} else {
    $img = 0;
}
if (!$icon) {
    $icon = 0;
} else {
    $icon = $_POST['icon'];
}

if (isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] <> '') {
    $stmt = mysqli_prepare($connect, "UPDATE lovelist SET eventname = ?, icon = ?, imgurl = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "sssi", $name, $icon, $img, $id);
    $reslove = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if ($reslove) {
        echo "1";
    } else {
        echo "0";
    }
} else {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>";
}
