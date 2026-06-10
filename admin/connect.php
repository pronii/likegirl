<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
header("Content-Type:text/html; charset=utf8");
include_once __DIR__.'/Config_DB.php';
$connect = mysqli_connect($db_address,$db_username,$db_password,$db_name);
$LikeGirl_Code = $Like_Code;
if (!$connect) {
    die("<script>location.href = '../admin/connectDie.php';</script>");
}
$connect->set_charset("utf8mb4");  