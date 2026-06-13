#!/bin/bash

echo "========================================"
echo "   图片加载优化 - 效果验证脚本"
echo "========================================"
echo ""

# 测试图片缓存头
echo "[1/4] 测试静态图片缓存..."
curl -I http://localhost/Style/img/Loading2.gif 2>/dev/null | grep -E "Cache-Control|Expires" || echo "❌ 缓存头未生效"
echo ""

# 测试 Gzip 压缩
echo "[2/4] 测试 Gzip 压缩..."
curl -I -H "Accept-Encoding: gzip" http://localhost/ 2>/dev/null | grep "Content-Encoding: gzip" && echo "✅ Gzip 已启用" || echo "❌ Gzip 未启用"
echo ""

# 测试 API 缓存
echo "[3/4] 测试 API 接口缓存..."
curl -I http://localhost/getPhotos.php 2>/dev/null | grep -E "Cache-Control" && echo "✅ API 缓存已设置" || echo "❌ API 缓存未设置"
echo ""

# 测试懒加载脚本
echo "[4/4] 检查懒加载脚本..."
if [ -f "Style/js/loveAlbum/lazyload.js" ]; then
    echo "✅ 懒加载脚本存在"
else
    echo "❌ 懒加载脚本缺失"
fi
echo ""

echo "========================================"
echo "  测试完成！查看上方结果"
echo "========================================"
echo ""
echo "详细文档：IMAGE_OPTIMIZATION_GUIDE.md"
