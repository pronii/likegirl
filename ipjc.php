<?php

include_once 'admin/connect.php';

// 获取访问者IP
$ip = $_SERVER["REMOTE_ADDR"];

// 一次性查询是否在黑名单中
$ipchaxun = "SELECT COUNT(*) as count FROM IPerror WHERE State = ?";
$stmt = mysqli_prepare($connect, $ipchaxun);
mysqli_stmt_bind_param($stmt, "s", $ip);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $count);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// 如果IP在黑名单中，阻止访问
if ($count > 0) {
    die("<script>Swal.fire({icon:'error',title:'你的IP($ip)已封禁，禁止访问本页面',confirmButtonText:'好的'}).then(()=>{location.href='error.php';});</script>");
}

?>
