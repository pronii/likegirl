<?php

/*
 * @Page：自定义函数方法
 * @Version：Like Girl 5.2.1-Stable
 * @Author: Ki.
 * @Date: 2025-09-03 00:00:00
 * @LastEditTime: 2025-09-03
 * @Description: 愿得一心人 白头不相离
 * @Document：https://blog.kikiw.cn/index.php/archives/52/
 * @Copyright (c) 2023 - 2025 by Ki All Rights Reserved. 
 * @Warning：禁止以任何方式出售本项目 如有发现一切后果自行负责
 * @Warning：禁止以任何方式出售本项目 如有发现一切后果自行负责
 * @Warning：禁止以任何方式出售本项目 如有发现一切后果自行负责
 * @Message：开发不易 版权信息请保留 （删除/更改版权的无耻之人请勿使用 查到一个挂一个）
 * @Message：开发不易 版权信息请保留 （删除/更改版权的无耻之人请勿使用 查到一个挂一个）
 * @Message：开发不易 版权信息请保留 （删除/更改版权的无耻之人请勿使用 查到一个挂一个）
 */
 
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $_SERVER['REMOTE_ADDR'] = $list[0];
    $Filter_IP = $_SERVER['REMOTE_ADDR'];
} else {
    $Filter_IP = $_SERVER['REMOTE_ADDR'];
}

function checkQQ($qq)
{
    if (preg_match("/^[1-9][0-9]{4,}$/", $qq)) {
        return true;
    } else {
        return false;
    }
}

function replaceSpecialChar($str)
{
    $filter = "/[\\'\"\\\`;]/"; 
    return preg_replace($filter, '', $str);
}


function escapeXSS($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function time_tran($time)
{
    $text = '';
    if (!$time) {
        return $text;
    }
    $current = time();
    $t = $current - $time;
    $retArr = array('刚刚', '秒前', '分钟前', '小时前', '天前', '月前', '年前');
    switch ($t) {
        case $t < 0://时间大于当前时间，返回格式化时间
            $text = date('Y-m-d', $time);
            break;
        case $t == 0://刚刚
            $text = $retArr[0];
            break;
        case $t < 60:// 几秒前
            $text = $t . $retArr[1];
            break;
        case $t < 3600://几分钟前
            $text = floor($t / 60) . $retArr[2];
            break;
        case $t < 86400://几小时前
            $text = floor($t / 3600) . $retArr[3];
            break;
        case $t < 2592000: //几天前
            $text = floor($t / 86400) . $retArr[4];
            break;
        case $t < 31536000: //几个月前
            $text = floor($t / 2592000) . $retArr[5];
            break;
        default: //几年前
            $text = floor($t / 31536000) . $retArr[6];
    }
    return $text;
}




function get_ip_city_New($ip)
{
    $ch = curl_init();
    $url = 'https://www.inte.net/tool/ip/api.ashx?ip='.$ip.'&datatype=json';
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $location = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($location, true);
    return $data['data'][0];
}

/**
 * 格式化视频时长
 * @param int|null $seconds 秒数
 * @return string 格式化后的时长字符串 (HH:MM:SS 或 MM:SS)
 */
function formatDuration($seconds) {
    if ($seconds === null || $seconds === '') {
        return '--';
    }

    $seconds = intval($seconds);
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}

/**
 * 格式化文件大小
 * @param int|null $bytes 字节数
 * @return string 格式化后的文件大小 (B/KB/MB/GB)
 */
function formatFileSize($bytes) {
    if ($bytes === null || $bytes === '') {
        return '--';
    }

    $bytes = floatval($bytes);
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;

    while ($bytes >= 1024 && $i < 3) {
        $bytes /= 1024;
        $i++;
    }

    return round($bytes, 2) . ' ' . $units[$i];
}

/**
 * 验证视频文件类型
 * @param string $filename 文件名
 * @param string $mimeType MIME类型
 * @return bool 是否为允许的视频类型
 */
function isValidVideoFile($filename, $mimeType) {
    $allowedExtensions = ['mp4', 'avi', 'mov', 'webm', 'mkv', 'm4v', 'flv', 'wmv'];

    // 扩展 MIME 类型支持（不同浏览器和系统可能返回不同的 MIME）
    $allowedMimeTypes = [
        'video/mp4',
        'video/x-m4v',          // MP4 变体
        'video/x-msvideo',      // AVI
        'video/avi',            // AVI 变体
        'video/msvideo',        // AVI 另一种形式
        'video/quicktime',      // MOV
        'video/webm',           // WebM
        'video/x-matroska',     // MKV
        'video/mkv',            // MKV 变体
        'video/x-flv',          // FLV
        'video/flv',            // FLV 变体
        'video/x-ms-wmv'        // WMV
    ];

    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // 检查扩展名和 MIME 类型
    return in_array($extension, $allowedExtensions) &&
           in_array($mimeType, $allowedMimeTypes);
}
