<?php
session_start();
$file = $_SERVER['PHP_SELF'];

include_once 'connect.php';
include_once 'imageProcessor.php';

if (!isset($_SESSION['loginadmin']) || $_SESSION['loginadmin'] == '') {
    die("<script>alert('非法操作，行为已记录');location.href = 'warning.php?route=$file';</script>");
}

if (!isset($_FILES['imageFile']) || $_FILES['imageFile']['error'] != 0) {
    die("<script>alert('请选择图片文件');history.back();</script>");
}

$album_id = intval($_POST['album_id']);
$imgText = htmlspecialchars(trim($_POST['imgText']), ENT_QUOTES);
$imgDatd = trim($_POST['imgDatd']);

$file = $_FILES['imageFile'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

if (!in_array($file['type'], $allowedTypes)) {
    die("<script>alert('只支持 JPG、PNG、GIF、WEBP 格式');history.back();</script>");
}

// 创建目录
$uploadDir = __DIR__ . '/../uploads/images/';
$thumbDir = __DIR__ . '/../uploads/thumbs/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

// 生成文件名
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = date('YmdHis') . '_' . uniqid() . '.' . $ext;
$imagePath = $uploadDir . $filename;
$thumbPath = $thumbDir . $filename;

// 移动原文件
if (!move_uploaded_file($file['tmp_name'], $imagePath)) {
    die("<script>alert('文件上传失败');history.back();</script>");
}

// 压缩原图
$compressed = $uploadDir . 'compressed_' . $filename;
compressImage($imagePath, $compressed, 85, 1920, 1080);
unlink($imagePath);
rename($compressed, $imagePath);

// 生成缩略图
createThumbnail($imagePath, $thumbPath, 300, 300);

// 保存到数据库
$imgUrl = '/uploads/images/' . $filename;
$thumbUrl = '/uploads/thumbs/' . $filename;

$stmt = mysqli_prepare($connect, "INSERT INTO loveImg (album_id, imgDatd, imgText, imgUrl, thumbUrl) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "issss", $album_id, $imgDatd, $imgText, $imgUrl, $thumbUrl);
$result = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($result) {
    echo "<script>alert('图片上传成功');location.href = 'loveImgSet.php';</script>";
} else {
    echo "<script>alert('图片添加失败');history.back();</script>";
}
