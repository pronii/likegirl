@echo off
chcp 65001 >nul
REM 清理测试文件脚本 - Windows 版本
REM 用途：删除项目中的测试和诊断文件
REM 作者：Claude Code
REM 日期：2026-06-16

echo ================================
echo 清理测试文件
echo ================================
echo.

set deleted_count=0
set failed_count=0

echo 开始清理根目录测试文件...
echo.

REM 根目录测试文件
call :delete_file "test_video_controls.html"
call :delete_file "test_video_controls_comparison.html"
call :delete_file "test_videoplayer_simple.html"
call :delete_file "test_inline_player.html"
call :delete_file "test_inline.html"
call :delete_file "diagnose_mediaplayer.html"
call :delete_file "diagnose_failed_video.html"
call :delete_file "test_video_debug.html"
call :delete_file "test_video_api.html"
call :delete_file "test_video_thumbnail.html"
call :delete_file "test_video_upload_simple.html"
call :delete_file "upload_test.html"
call :delete_file "test_actual_upload.php"
call :delete_file "test_batch_post.php"
call :delete_file "test_upload_error.php"
call :delete_file "test_video_validation.php"
call :delete_file "simple_test.php"
call :delete_file "check_config.php"
call :delete_file "debug_500_error.php"
call :delete_file "test-albums.php"
call :delete_file "test-music-url.html"

echo.
echo 清理旧版文档...
echo.

REM 旧版修复文档
call :delete_file "VIDEO_CONTROLS_FIX.md"
call :delete_file "MEDIAPLAYER_CACHE_FIX.md"
call :delete_file "MEDIAPLAYER_FIX.md"
call :delete_file "MEDIAPLAYER_LOADING_FIX.md"
call :delete_file "VIDEO_UPLOAD_DIAGNOSIS.md"
call :delete_file "VIDEO_UPLOAD_FAILURE_ANALYSIS.md"
call :delete_file "VIDEO_UPLOAD_FIX_SUMMARY.md"
call :delete_file "QUICK_FIX_VIDEO_UPLOAD.md"

echo.
echo 清理 admin 目录测试文件...
echo.

REM admin 目录测试文件
call :delete_file "admin\test_batch_upload.php"
call :delete_file "admin\test_direct_upload.php"
call :delete_file "admin\test_upload_debug.php"
call :delete_file "admin\test_upload_diagnosis.php"
call :delete_file "admin\test_upload_full.php"
call :delete_file "admin\test_upload_simple.php"
call :delete_file "admin\test_backup_features.php"
call :delete_file "admin\integration_test.php"
call :delete_file "admin\uploadBackup_debug.php"
call :delete_file "admin\diagnose_video_upload.php"
call :delete_file "admin\check_upload_error.php"
call :delete_file "admin\check_upload.bat"
call :delete_file "admin\check_upload.sh"
call :delete_file "admin\UPLOAD_TROUBLESHOOTING.md"

echo.
echo ================================
echo 清理完成
echo ================================
echo 成功删除: %deleted_count% 个文件
if %failed_count% GTR 0 (
    echo 删除失败: %failed_count% 个文件
)
echo.
echo 保留的重要文档：
echo   - README.md
echo   - VIDEO_PLAYER_V2_CHANGELOG.md (新增)
echo   - MUSIC_PLAYER_README.md
echo   - MUSIC_API_README.md
echo   - 其他项目文档
echo.
pause
goto :eof

:delete_file
set "file=%~1"
if exist "%file%" (
    del /f /q "%file%" >nul 2>&1
    if errorlevel 1 (
        echo [×] 删除失败: %file%
        set /a failed_count+=1
    ) else (
        echo [√] 已删除: %file%
        set /a deleted_count+=1
    )
) else (
    echo [○] 文件不存在: %file%
)
goto :eof
