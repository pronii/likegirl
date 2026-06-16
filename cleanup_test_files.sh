#!/bin/bash

# 清理测试文件脚本
# 用途：删除项目中的测试和诊断文件
# 作者：Claude Code
# 日期：2026-06-16

echo "================================"
echo "清理测试文件"
echo "================================"
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 计数器
deleted_count=0
failed_count=0

# 删除文件的函数
delete_file() {
    local file=$1
    if [ -f "$file" ]; then
        rm "$file"
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✓${NC} 已删除: $file"
            ((deleted_count++))
        else
            echo -e "${RED}✗${NC} 删除失败: $file"
            ((failed_count++))
        fi
    else
        echo -e "${YELLOW}⊘${NC} 文件不存在: $file"
    fi
}

echo "开始清理根目录测试文件..."
echo ""

# 根目录测试文件
delete_file "test_video_controls.html"
delete_file "test_video_controls_comparison.html"
delete_file "test_videoplayer_simple.html"
delete_file "test_inline_player.html"
delete_file "test_inline.html"
delete_file "diagnose_mediaplayer.html"
delete_file "diagnose_failed_video.html"
delete_file "test_video_debug.html"
delete_file "test_video_api.html"
delete_file "test_video_thumbnail.html"
delete_file "test_video_upload_simple.html"
delete_file "upload_test.html"
delete_file "test_actual_upload.php"
delete_file "test_batch_post.php"
delete_file "test_upload_error.php"
delete_file "test_video_validation.php"
delete_file "simple_test.php"
delete_file "check_config.php"
delete_file "debug_500_error.php"
delete_file "test-albums.php"
delete_file "test-music-url.html"

echo ""
echo "清理旧版文档..."
echo ""

# 旧版修复文档
delete_file "VIDEO_CONTROLS_FIX.md"
delete_file "MEDIAPLAYER_CACHE_FIX.md"
delete_file "MEDIAPLAYER_FIX.md"
delete_file "MEDIAPLAYER_LOADING_FIX.md"
delete_file "VIDEO_UPLOAD_DIAGNOSIS.md"
delete_file "VIDEO_UPLOAD_FAILURE_ANALYSIS.md"
delete_file "VIDEO_UPLOAD_FIX_SUMMARY.md"
delete_file "QUICK_FIX_VIDEO_UPLOAD.md"

echo ""
echo "清理 admin 目录测试文件..."
echo ""

# admin 目录测试文件
delete_file "admin/test_batch_upload.php"
delete_file "admin/test_direct_upload.php"
delete_file "admin/test_upload_debug.php"
delete_file "admin/test_upload_diagnosis.php"
delete_file "admin/test_upload_full.php"
delete_file "admin/test_upload_simple.php"
delete_file "admin/test_backup_features.php"
delete_file "admin/integration_test.php"
delete_file "admin/uploadBackup_debug.php"
delete_file "admin/diagnose_video_upload.php"
delete_file "admin/check_upload_error.php"
delete_file "admin/check_upload.bat"
delete_file "admin/check_upload.sh"
delete_file "admin/UPLOAD_TROUBLESHOOTING.md"

echo ""
echo "================================"
echo "清理完成"
echo "================================"
echo -e "${GREEN}成功删除: $deleted_count 个文件${NC}"
if [ $failed_count -gt 0 ]; then
    echo -e "${RED}删除失败: $failed_count 个文件${NC}"
fi
echo ""
echo "保留的重要文档："
echo "  - README.md"
echo "  - VIDEO_PLAYER_V2_CHANGELOG.md (新增)"
echo "  - MUSIC_PLAYER_README.md"
echo "  - MUSIC_API_README.md"
echo "  - 其他项目文档"
echo ""
