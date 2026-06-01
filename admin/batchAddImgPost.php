<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

// 引入同目录下的数据库连接文件
include_once __DIR__ . '/connect.php';

// 增加判断：如果引入失败或者$connect未定义，直接提示并退出
if (!isset($connect)) {
    exit("数据库连接失败！请检查 connect.php 是否存在及配置是否正确。");
}

// 检查是否为POST请求
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "<script>alert('非法请求'); window.location.href='loveImgSet.php';</script>";
    exit;
}

// 获取公共参数
$album_id = isset($_POST['album_id']) ? intval($_POST['album_id']) : 0;
$imgDatd = isset($_POST['imgDatd']) ? trim($_POST['imgDatd']) : '';

// 验证公共参数
if (empty($imgDatd)) {
    echo "<script>alert('参数不完整：请选择日期'); window.history.back();</script>";
    exit;
}

// 如果选择了相册，验证相册是否存在
if ($album_id > 0) {
    $safe_album_id = mysqli_real_escape_string($connect, $album_id);
    $checkAlbum = "SELECT id FROM love_album WHERE id = '$safe_album_id'";
    $albumResult = mysqli_query($connect, $checkAlbum);
    if (!$albumResult || mysqli_num_rows($albumResult) == 0) {
        echo "<script>alert('所选相册不存在'); window.history.back();</script>";
        exit;
    }
}

// 收集所有图片数据
$images = array();
foreach ($_POST as $key => $value) {
    if (strpos($key, 'imgUrl_') === 0) {
        $index = substr($key, 7);
        $images[$index]['imgUrl'] = trim($value);
    } elseif (strpos($key, 'imgText_') === 0) {
        $index = substr($key, 8);
        $images[$index]['imgText'] = trim($value);
    }
}

// 验证至少有一张图片
if (empty($images)) {
    echo "<script>alert('请至少添加一张图片'); window.history.back();</script>";
    exit;
}

// === 使用兼容低版本PHP的方式开启事务 ===
mysqli_autocommit($connect, false);

$successCount = 0;
$failCount = 0;
$errors = array();

$realIndex = 0;

// 批量插入图片
foreach ($images as $index => $img) {
    $imgUrl = isset($img['imgUrl']) ? trim($img['imgUrl']) : '';
    $imgText = isset($img['imgText']) ? trim($img['imgText']) : '';

    // URL和描述都为空 → 直接跳过，不计入失败（前端自动补的空项）
    if (empty($imgUrl) && empty($imgText)) {
        continue;
    }

    $realIndex++;

    // 部分填写 报错
    if (empty($imgUrl)) {
        $failCount++;
        $errors[] = "第 " . $realIndex . " 张图片：URL不能为空";
        continue;
    }

    // 转义后插入数据库
    $safe_album = mysqli_real_escape_string($connect, $album_id);
    $safe_url   = mysqli_real_escape_string($connect, $imgUrl);
    $safe_text  = mysqli_real_escape_string($connect, $imgText);
    $safe_date  = mysqli_real_escape_string($connect, $imgDatd);
    $sql = "INSERT INTO loveImg (album_id, imgUrl, imgText, imgDatd)
            VALUES ('$safe_album', '$safe_url', '$safe_text', '$safe_date')";

    $result = mysqli_query($connect, $sql);

    if ($result) {
        $successCount++;
    } else {
        $failCount++;
        $errors[] = "第 " . $realIndex . " 张图片：" . mysqli_error($connect);
    }
}

// === 事务提交/回滚逻辑 ===
if ($successCount > 0 && $failCount == 0) {
    // 全部成功 → 提交
    mysqli_commit($connect);
    $msg = "批量添加完成！\\n\\n成功：" . $successCount . " 张";
    echo "<script>alert('" . $msg . "'); window.location.href='loveImgSet.php';</script>";

} elseif ($successCount > 0 && $failCount > 0) {
    // 部分成功 → 提交成功的
    mysqli_commit($connect);
    $msg = "批量添加完成！\\n\\n成功：" . $successCount . " 张\\n失败：" . $failCount . " 张\\n\\n详情：\\n" . implode("\\n", $errors);
    echo "<script>alert('" . addslashes($msg) . "'); window.location.href='loveImgSet.php';</script>";

} else {
    // 全部失败 → 回滚
    mysqli_rollback($connect);
    $msg = "添加失败！\\n\\n" . implode("\\n", $errors);
    echo "<script>alert('" . addslashes($msg) . "'); window.history.back();</script>";
}

// 恢复自动提交并关闭连接
mysqli_autocommit($connect, true);
mysqli_close($connect);