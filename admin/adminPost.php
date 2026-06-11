<?php
session_start();
$file = $_SERVER['PHP_SELF'];
include_once 'connect.php';

if (isset($_SESSION['loginadmin']) && $_SESSION['loginadmin'] <> '') {
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES);
    $logo = htmlspecialchars(trim($_POST['logo']), ENT_QUOTES);
    $writing = htmlspecialchars(trim($_POST['writing']), ENT_QUOTES);
    $WebPjax = trim($_POST['WebPjax']);
    $WebBlur = trim($_POST['WebBlur']);

    $stmt = mysqli_prepare($connect, "UPDATE text SET title = ?, logo = ?, writing = ? WHERE id = 1");
    mysqli_stmt_bind_param($stmt, "sss", $title, $logo, $writing);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    if ($result) {
        echo "1";
    } else {
        echo "0";
    }

    $stmt2 = mysqli_prepare($connect, "UPDATE diySet SET Pjaxkg = ?, Blurkg = ? WHERE id = 1");
    mysqli_stmt_bind_param($stmt2, "ss", $WebPjax, $WebBlur);
    $diyresult = mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);
    if ($diyresult) {
        echo "3";
    } else {
        echo "4";
    }
} else {
    echo "<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>";
}
