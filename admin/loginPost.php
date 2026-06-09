<?php
session_start();
$user = isset($_POST['adminName']) ? $_POST['adminName'] : '';
$pw = isset($_POST['pw']) ? $_POST['pw'] : '';
include_once "Database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 清理输入
    $USER = trim($user);
    $PW = md5($pw);

    // 预处理查询
    $sql = "select * from login where user = ? limit 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $USER);
    $stmt->bind_result($id, $Login_user, $Login_pw);
    $result = $stmt->execute();

    if (!$result) {
        die("<script>Swal.fire({icon:'error',title:'系统错误',confirmButtonText:'好的'}).then(() => {history.back();});</script>");
    }

    $stmt->fetch();
    $stmt->close();

    // 验证登录
    if ($USER == $Login_user && $PW == $Login_pw) {
        $_SESSION['loginadmin'] = $USER;
        echo "<script>alert('登录成功 欢迎进入小站后台管理页面！');location.href = '../admin/index.php';</script>";
    } else {
        die("<script>Swal.fire({icon:'error',title:'登录失败，用户名或密码错误！！！',confirmButtonText:'好的'}).then(() => {history.back();});</script>");
    }
} else {
    die("<script>Swal.fire({icon:'error',title:'请使用POST方式访问',confirmButtonText:'好的'}).then(() => {history.back();});</script>");
}

