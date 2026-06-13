<?php
/**
 * 图片缓存优化处理
 * 用于动态图片接口的缓存头设置
 */

header('Content-Type: application/json; charset=utf-8');

// 设置强缓存（1年）
header('Cache-Control: public, max-age=31536000, immutable');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// 启用 ETag 协商缓存
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$etag = md5($requestUri . filemtime(__FILE__));
header("ETag: \"$etag\"");

// 检查客户端缓存
if (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
    trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag) {
    http_response_code(304);
    exit;
}

// 响应数据
echo json_encode([
    'code' => 200,
    'message' => '缓存配置成功',
    'cache_enabled' => true
]);
